<?php

namespace Unit\app\Domain\Help\Services;

use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Help\Services\FirstTaskStep;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use RuntimeException;
use Unit\TestCase;

/**
 * Regression tests for the first-login onboarding dead-end in GH #3683.
 *
 * Readonly users have no TicketsPermissions::CREATE, so quickAddTicket() throws an
 * AuthorizationException. That used to happen before the firstLoginCompleted flag was
 * written, leaving the onboarding modal re-rendering forever with no way out. The flag
 * write is the invariant here; the first task is only a convenience.
 */
class FirstTaskStepTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** Captures every saveSetting() call as [key, value]. */
    private array $savedSettings = [];

    /** Captures every headline quickAddTicket() was called with. */
    private array $createdHeadlines = [];

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata.id' => 1]);

        $this->savedSettings = [];
        $this->createdHeadlines = [];
    }

    /**
     * Builds the step with a spying Setting repo and a Tickets service that either
     * records the headline or throws the given exception.
     */
    private function makeStep(?\Throwable $ticketFailure = null): FirstTaskStep
    {
        $settingsRepo = $this->make(SettingRepository::class, [
            'saveSetting' => function ($key, $value) {
                $this->savedSettings[] = [$key, $value];

                return true;
            },
        ]);

        $ticketService = $this->make(TicketService::class, [
            'quickAddTicket' => function ($params) use ($ticketFailure) {
                if ($ticketFailure !== null) {
                    throw $ticketFailure;
                }

                $this->createdHeadlines[] = $params['headline'];

                return 1;
            },
        ]);

        return new FirstTaskStep($settingsRepo, $ticketService);
    }

    /** The flag write, asserted as "written exactly once with true". */
    private function assertOnboardingCompleted(): void
    {
        $this->assertSame(
            [['user.1.firstLoginCompleted', true]],
            $this->savedSettings,
            'firstLoginCompleted must be persisted exactly once, regardless of ticket creation'
        );
    }

    public function test_readonly_user_completes_onboarding_when_ticket_creation_is_denied(): void
    {
        $result = $this->makeStep(new AuthorizationException)->handle(['headline' => 'Water the plants']);

        $this->assertTrue($result, 'handle() must report success even when the user cannot create tickets');
        $this->assertOnboardingCompleted();
        $this->assertSame([], $this->createdHeadlines);
    }

    public function test_unexpected_ticket_failure_still_completes_onboarding(): void
    {
        $result = $this->makeStep(new RuntimeException('database went away'))->handle(['headline' => 'Water the plants']);

        $this->assertTrue($result);
        $this->assertOnboardingCompleted();
    }

    public function test_permitted_user_creates_the_first_task_and_completes_onboarding(): void
    {
        $result = $this->makeStep()->handle(['headline' => 'Water the plants']);

        $this->assertTrue($result);
        $this->assertSame(['Water the plants'], $this->createdHeadlines);
        $this->assertOnboardingCompleted();
    }

    public function test_headline_is_trimmed_before_the_task_is_created(): void
    {
        $this->makeStep()->handle(['headline' => '  Water the plants  ']);

        $this->assertSame(['Water the plants'], $this->createdHeadlines);
    }

    /**
     * A blank, whitespace-only, missing or non-string headline must not create an empty
     * task — but must still complete onboarding.
     *
     * @dataProvider blankHeadlineProvider
     */
    public function test_blank_headline_skips_task_creation_but_completes_onboarding(array $params): void
    {
        $result = $this->makeStep()->handle($params);

        $this->assertTrue($result);
        $this->assertSame([], $this->createdHeadlines, 'No task should be created for a blank headline');
        $this->assertOnboardingCompleted();
    }

    public static function blankHeadlineProvider(): array
    {
        return [
            'empty string' => [['headline' => '']],
            'whitespace only' => [['headline' => "  \t "]],
            'missing key' => [[]],
            'null' => [['headline' => null]],
            'array (malformed POST)' => [['headline' => ['nope']]],
        ];
    }
}
