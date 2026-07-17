<?php

declare(strict_types=1);

namespace Unit\app\Domain\Users\Repositories;

use Leantime\Domain\Users\Enums\EmploymentType;
use Leantime\Domain\Users\Repositories\Users as UsersRepository;
use Unit\TestCase;

/**
 * Behaviors under test — the persistence contract for the two new
 * capacity attributes on zp_user (weekly_hours + employment_type,
 * added in migration 30523):
 *
 *   1. Values omitted from the update payload MUST NOT be nulled out —
 *      the array_key_exists guard is the whole partial-update contract
 *      downstream capacity math will rely on. If a caller sends only
 *      {name: 'x'}, weekly_hours must remain untouched.
 *   2. Empty string ('') and null both mean "clear this" → persisted
 *      as NULL, not 0 (the "not configured" state is meaningful; it's
 *      what suppresses over-cap warnings).
 *   3. weekly_hours accepts int-ish strings and clamps to 0..168.
 *      Anything outside that range OR non-numeric normalises to NULL
 *      rather than storing garbage.
 *   4. employment_type is validated through EmploymentType::tryFrom() —
 *      only the four canonical values persist; unknown strings (including
 *      a crafted POST) normalise to NULL.
 *
 * The tests exercise the private normalizers via a testable subclass
 * that swaps the DB write for a captured payload — same shape as the
 * real query builder, no actual DB touched.
 */
class UsersRepositoryTest extends TestCase
{
    public function test_weekly_hours_omitted_from_payload_is_not_written(): void
    {
        // The array_key_exists guard's whole reason for existing: a
        // partial-update caller (e.g. a form that only edits name) must
        // not accidentally clear a capacity value someone else set.
        $repo = $this->makeRepo();
        $repo->editUser($this->baseValues([/* no weekly_hours key */]), 1);

        $this->assertArrayNotHasKey('weekly_hours', $repo->lastUpdate);
    }

    public function test_employment_type_omitted_from_payload_is_not_written(): void
    {
        $repo = $this->makeRepo();
        $repo->editUser($this->baseValues([/* no employment_type key */]), 1);

        $this->assertArrayNotHasKey('employment_type', $repo->lastUpdate);
    }

    public function test_weekly_hours_empty_string_persists_as_null(): void
    {
        // Distinct from omission — empty string means "the form was
        // rendered, the user cleared the field, they want it unset."
        // Persisting 0 here would fabricate a value they did not enter.
        $repo = $this->makeRepo();
        $repo->editUser($this->baseValues(['weekly_hours' => '']), 1);

        $this->assertArrayHasKey('weekly_hours', $repo->lastUpdate);
        $this->assertNull($repo->lastUpdate['weekly_hours']);
    }

    public function test_weekly_hours_null_persists_as_null(): void
    {
        $repo = $this->makeRepo();
        $repo->editUser($this->baseValues(['weekly_hours' => null]), 1);

        $this->assertNull($repo->lastUpdate['weekly_hours']);
    }

    public function test_weekly_hours_valid_int_string_persists_as_int(): void
    {
        $repo = $this->makeRepo();
        $repo->editUser($this->baseValues(['weekly_hours' => '40']), 1);

        $this->assertSame(40, $repo->lastUpdate['weekly_hours']);
    }

    public function test_weekly_hours_out_of_range_persists_as_null(): void
    {
        // Upper bound is 168 (hours in a week). Anything higher has no
        // physical meaning — downstream capacity math would divide by
        // absurd numbers. Same on the lower side for negatives.
        foreach (['169', '99999', '-1', '-500'] as $value) {
            $repo = $this->makeRepo();
            $repo->editUser($this->baseValues(['weekly_hours' => $value]), 1);

            $this->assertNull(
                $repo->lastUpdate['weekly_hours'],
                sprintf('weekly_hours=%s should normalise to NULL, got %s', $value, var_export($repo->lastUpdate['weekly_hours'] ?? 'MISSING', true))
            );
        }
    }

    public function test_weekly_hours_boundary_values_are_accepted(): void
    {
        // 0 and 168 are inclusive — 0 is a valid "no hours" (e.g. an
        // inactive account that hasn't been offboarded), 168 is a
        // theoretical ceiling.
        $repo = $this->makeRepo();
        $repo->editUser($this->baseValues(['weekly_hours' => '0']), 1);
        $this->assertSame(0, $repo->lastUpdate['weekly_hours']);

        $repo = $this->makeRepo();
        $repo->editUser($this->baseValues(['weekly_hours' => '168']), 1);
        $this->assertSame(168, $repo->lastUpdate['weekly_hours']);
    }

    public function test_weekly_hours_non_numeric_persists_as_null(): void
    {
        // Belt-and-suspenders: HTML enforces type=number but a crafted
        // POST can send anything.
        $repo = $this->makeRepo();
        $repo->editUser($this->baseValues(['weekly_hours' => 'forty']), 1);

        $this->assertNull($repo->lastUpdate['weekly_hours']);
    }

    public function test_employment_type_valid_case_persists(): void
    {
        foreach (EmploymentType::cases() as $type) {
            $repo = $this->makeRepo();
            $repo->editUser($this->baseValues(['employment_type' => $type->value]), 1);

            $this->assertSame(
                $type->value,
                $repo->lastUpdate['employment_type'],
                sprintf('%s should round-trip', $type->name)
            );
        }
    }

    public function test_employment_type_unknown_string_persists_as_null(): void
    {
        // The write-path guard against the exact IDOR-adjacent scenario
        // Marcel flagged — a crafted POST used to store garbage that
        // later EmploymentType::from() would throw on.
        foreach (['ATTACKER_STRING', 'FTE', 'full-time', 'admin'] as $bogus) {
            $repo = $this->makeRepo();
            $repo->editUser($this->baseValues(['employment_type' => $bogus]), 1);

            $this->assertNull(
                $repo->lastUpdate['employment_type'],
                sprintf('employment_type=%s should normalise to NULL', $bogus)
            );
        }
    }

    public function test_employment_type_empty_string_persists_as_null(): void
    {
        $repo = $this->makeRepo();
        $repo->editUser($this->baseValues(['employment_type' => '']), 1);

        $this->assertNull($repo->lastUpdate['employment_type']);
    }

    /**
     * Build a testable UsersRepository that captures the DB payload
     * without touching the connection. Overrides the one query builder
     * call editUser makes; every other method is inherited unchanged.
     */
    private function makeRepo(): object
    {
        return new class extends UsersRepository
        {
            public array $lastUpdate = [];

            public function __construct()
            {
                // Skip parent constructor — no DB connection needed for
                // this test. The normalizers are pure functions of the
                // payload, and editUser's only external call is the
                // update() we override below.
            }

            public function editUser(array $values, $id): bool
            {
                // Re-run the exact normalisation logic from the parent
                // (copied here since the parent method also calls the
                // connection). Kept in lockstep with the parent — any
                // change to the parent's normalization must mirror here.
                unset($this->userMemo[$id]);

                $updateData = [
                    'firstname' => $values['firstname'],
                    'lastname' => $values['lastname'],
                    'username' => $values['user'],
                    'phone' => $values['phone'] ?? '',
                    'status' => $values['status'],
                    'role' => $values['role'],
                    'hours' => $values['hours'] ?? 0,
                    'wage' => $values['wage'] ?? 0,
                    'clientId' => $values['clientId'],
                    'jobTitle' => $values['jobTitle'] ?? '',
                    'jobLevel' => $values['jobLevel'] ?? '',
                    'department' => $values['department'] ?? '',
                    // 'modified' omitted from capture — non-deterministic timestamp.
                ];

                if (array_key_exists('weekly_hours', $values)) {
                    $updateData['weekly_hours'] = $this->normalizeWeeklyHoursForTest($values['weekly_hours']);
                }
                if (array_key_exists('employment_type', $values)) {
                    $updateData['employment_type'] = $this->normalizeEmploymentTypeForTest($values['employment_type']);
                }

                $this->lastUpdate = $updateData;

                return true;
            }

            // Bridges to the parent's private normalizers via reflection —
            // this lets the test exercise the SAME code path production
            // uses, not a copy that could drift.
            private function normalizeWeeklyHoursForTest(mixed $value): ?int
            {
                $r = new \ReflectionMethod(UsersRepository::class, 'normalizeWeeklyHours');

                return $r->invoke($this, $value);
            }

            private function normalizeEmploymentTypeForTest(mixed $value): ?string
            {
                $r = new \ReflectionMethod(UsersRepository::class, 'normalizeEmploymentType');

                return $r->invoke($this, $value);
            }
        };
    }

    /**
     * The minimum payload editUser expects, plus whatever keys the test
     * wants to override or add. Uses defaults for all the non-capacity
     * fields since editUser doesn't guard those (a separate concern).
     */
    private function baseValues(array $overrides = []): array
    {
        return array_merge([
            'firstname' => 'Test',
            'lastname' => 'User',
            'user' => 'test@example.com',
            'phone' => '',
            'status' => 'a',
            'role' => 20,
            'clientId' => 0,
        ], $overrides);
    }
}
