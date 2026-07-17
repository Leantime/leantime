<?php

declare(strict_types=1);

namespace Unit\app\Domain\Users\Contracts;

use Leantime\Domain\Users\Contracts\EmploymentType;
use Unit\TestCase;

/**
 * Behaviors under test — the semantics that downstream capacity math
 * will trust:
 *
 *   1. Volunteer is the only case excluded from capacity accounting
 *      (countsAgainstCapacity()=false). Everyone else counts.
 *   2. Over-cap warning tone maps cleanly per type — FTE = warn (target
 *      exceeded, a burnout signal but not a violation), PT/Contractor =
 *      danger (violates an explicit ceiling or billable cap), Volunteer
 *      = none (best-effort work has no cap to violate).
 *   3. tryFrom() rejects arbitrary strings — the enum is the write-path
 *      validation surface, so an unrecognised value must return null
 *      rather than throw or coerce.
 *   4. Every case has both a human label AND an i18n key so admin UIs
 *      can render translated selects without special-casing.
 */
class EmploymentTypeTest extends TestCase
{
    public function test_volunteer_is_the_only_case_excluded_from_capacity(): void
    {
        // The whole point of the Volunteer type — best-effort work is
        // additive to team throughput but shouldn't fire over-cap
        // warnings that would flag someone for helping too much.
        $this->assertFalse(EmploymentType::Volunteer->countsAgainstCapacity());

        $this->assertTrue(EmploymentType::FTE->countsAgainstCapacity());
        $this->assertTrue(EmploymentType::PartTime->countsAgainstCapacity());
        $this->assertTrue(EmploymentType::Contractor->countsAgainstCapacity());
    }

    public function test_overcap_tone_reflects_target_vs_ceiling_semantics(): void
    {
        // FTE going over is a burnout signal — amber, not red. PT + Contractor
        // ceilings are explicit commitments (part-time hours the user set,
        // billable caps the org set) — over = red. Volunteer never fires.
        $this->assertSame('warn', EmploymentType::FTE->overCapTone());
        $this->assertSame('danger', EmploymentType::PartTime->overCapTone());
        $this->assertSame('danger', EmploymentType::Contractor->overCapTone());
        $this->assertSame('none', EmploymentType::Volunteer->overCapTone());
    }

    public function test_tryFrom_rejects_arbitrary_strings(): void
    {
        // This is the exact surface the write path relies on. If tryFrom
        // ever starts coercing garbage into a case, the repo guard opens
        // a store-arbitrary-string escape hatch.
        $this->assertNull(EmploymentType::tryFrom('ATTACKER'));
        $this->assertNull(EmploymentType::tryFrom(''));
        $this->assertNull(EmploymentType::tryFrom('FTE'));   // wrong case — enum values are lowercase
        $this->assertNull(EmploymentType::tryFrom('full-time'));
    }

    public function test_tryFrom_accepts_the_four_canonical_values(): void
    {
        $this->assertSame(EmploymentType::FTE, EmploymentType::tryFrom('fte'));
        $this->assertSame(EmploymentType::PartTime, EmploymentType::tryFrom('pt'));
        $this->assertSame(EmploymentType::Contractor, EmploymentType::tryFrom('contractor'));
        $this->assertSame(EmploymentType::Volunteer, EmploymentType::tryFrom('volunteer'));
    }

    public function test_every_case_has_a_label_and_langKey(): void
    {
        foreach (EmploymentType::cases() as $type) {
            $this->assertNotSame('', $type->label(), sprintf('%s has empty label', $type->name));
            $this->assertStringStartsWith('users.employment_type.', $type->langKey());
            // The i18n suffix must be the enum's value — templates read
            // the value and look up the string, so a mismatch means the
            // admin select renders as a raw key.
            $this->assertStringEndsWith('.'.$type->value, $type->langKey());
        }
    }
}
