<?php

namespace Unit\app\Domain\Setting\Services;

use Leantime\Core\Exceptions\ValidationException;
use Leantime\Core\Files\Contracts\FileManagerInterface;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Notifications\Services\PushRelay;
use Leantime\Domain\Reports\Services\Reports as ReportService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * Unit tests for the Setting service helpers extracted during the
 * thin-controller refactor (getProjectLabel).
 */
class SettingServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Setting service, allowing each dependency to be
     * overridden with a stub so we can observe the label resolution.
     */
    private function makeService(
        ?SettingRepository $settingsRepo = null,
        ?TicketRepository $ticketsRepo = null,
        ?IdeaRepository $ideaRepo = null,
    ): SettingService {
        return new SettingService(
            $settingsRepo ?? $this->make(SettingRepository::class),
            $this->makeEmpty(FileManagerInterface::class),
            $ticketsRepo ?? $this->make(TicketRepository::class),
            $ideaRepo ?? $this->make(IdeaRepository::class),
        );
    }

    public function test_get_project_label_reads_ticket_state_label_name(): void
    {
        $ticketsRepo = $this->make(TicketRepository::class, [
            'getStateLabels' => fn () => [
                3 => ['name' => 'In Progress'],
            ],
        ]);

        $label = $this->makeService(ticketsRepo: $ticketsRepo)->getProjectLabel('ticketlabels', 3, 1);

        $this->assertSame('In Progress', $label);
    }

    public function test_get_project_label_returns_empty_for_missing_ticket_label(): void
    {
        $ticketsRepo = $this->make(TicketRepository::class, [
            'getStateLabels' => fn () => [
                3 => ['name' => 'In Progress'],
            ],
        ]);

        $label = $this->makeService(ticketsRepo: $ticketsRepo)->getProjectLabel('ticketlabels', 99, 1);

        $this->assertSame('', $label);
    }

    public function test_get_project_label_reads_idea_label_name(): void
    {
        $ideaRepo = $this->make(IdeaRepository::class, [
            'getCanvasLabels' => fn () => [
                1 => ['name' => 'Backlog', 'class' => 'label-default'],
            ],
        ]);

        $label = $this->makeService(ideaRepo: $ideaRepo)->getProjectLabel('idealabels', 1, 1);

        $this->assertSame('Backlog', $label);
    }

    public function test_get_project_label_returns_empty_for_unknown_module(): void
    {
        $label = $this->makeService()->getProjectLabel('doesnotexist', 1, 1);

        $this->assertSame('', $label);
    }

    /**
     * Runs saveCompanySettings() with only the telemetry toggle (no other settings), with the
     * stored opt-out flag set to $storedOptOut, and records which ReportService calls it made.
     *
     * @param  array<string, mixed>  $params
     * @return array{saved: bool, calls: array<int, string>}
     */
    private function saveTelemetryToggle(array $params, string|false $storedOptOut): array
    {
        $calls = [];
        $reports = $this->make(ReportService::class, [
            'optOutTelemetry' => function () use (&$calls) {
                $calls[] = 'optOut';
            },
            'optInTelemetry' => function () use (&$calls) {
                $calls[] = 'optIn';
            },
        ]);
        app()->instance(ReportService::class, $reports);

        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn ($key) => $key === 'companysettings.telemetry.optOut' ? $storedOptOut : false,
        ]);

        try {
            $saved = $this->makeService(settingsRepo: $settingsRepo)->saveCompanySettings($params);
        } finally {
            app()->forgetInstance(ReportService::class);
        }

        return ['saved' => $saved, 'calls' => $calls];
    }

    public function test_unchecking_the_toggle_opts_out_once(): void
    {
        $result = $this->saveTelemetryToggle(['telemetryActive' => false], false);

        $this->assertSame(['optOut'], $result['calls']);
        $this->assertTrue($result['saved'], 'an opt-out counts as a save even without other settings');
    }

    public function test_unchecked_toggle_when_already_opted_out_sends_no_second_notice(): void
    {
        $result = $this->saveTelemetryToggle(['telemetryActive' => false], 'true');

        $this->assertSame([], $result['calls']);
    }

    public function test_checking_the_toggle_after_an_opt_out_opts_back_in(): void
    {
        $result = $this->saveTelemetryToggle(['telemetryActive' => true], 'true');

        $this->assertSame(['optIn'], $result['calls']);
        $this->assertTrue($result['saved']);
    }

    public function test_checked_toggle_when_already_opted_in_changes_nothing(): void
    {
        $result = $this->saveTelemetryToggle(['telemetryActive' => true], 'false');

        $this->assertSame([], $result['calls']);
    }

    public function test_callers_without_the_toggle_leave_telemetry_untouched(): void
    {
        $result = $this->saveTelemetryToggle(['messageFrequency' => 300], false);

        $this->assertSame([], $result['calls']);
        $this->assertFalse($result['saved']);
    }

    // ---------------------------------------------------------------------
    // Mobile push relay settings, saved with the main company details.
    // ---------------------------------------------------------------------

    /**
     * Runs saveCompanySettings() with the required main details plus $extra and returns every
     * setting it wrote, keyed by setting name.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function saveMainDetails(array $extra): array
    {
        $saved = [];
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => false,
            'saveSetting' => function ($key, $value) use (&$saved) {
                $saved[$key] = $value;

                return true;
            },
        ]);

        $this->makeService(settingsRepo: $settingsRepo)->saveCompanySettings($extra + ['name' => 'Acme', 'language' => 'en-US', 'messageFrequency' => 3600]);

        return $saved;
    }

    /**
     * The relay switch is read as a boolean: an RPC caller sending false or '0' must switch it
     * off, not on. A missing key (unchecked box) is off.
     *
     * @dataProvider relaySwitchProvider
     */
    public function test_the_relay_switch_is_read_as_a_boolean(array $params, string $stored): void
    {
        $this->assertSame($stored, $this->saveMainDetails($params)[PushRelay::SETTING_ENABLED]);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function relaySwitchProvider(): array
    {
        return [
            'true' => [['pushRelayEnabled' => true], '1'],
            'checkbox' => [['pushRelayEnabled' => 'on'], '1'],
            '"1"' => [['pushRelayEnabled' => '1'], '1'],
            'false' => [['pushRelayEnabled' => false], '0'],
            '"0"' => [['pushRelayEnabled' => '0'], '0'],
            '"false"' => [['pushRelayEnabled' => 'false'], '0'],
            'missing' => [[], '0'],
        ];
    }

    /**
     * The content level is only written when sent: a save that doesn't carry it keeps whatever
     * was stored, so it can't silently flip a content-free relay back to full content.
     */
    public function test_a_save_without_the_content_level_leaves_it_alone(): void
    {
        $saved = $this->saveMainDetails(['pushRelayEnabled' => true]);

        $this->assertArrayNotHasKey(PushRelay::SETTING_CONTENT, $saved);
    }

    public function test_the_content_level_accepts_minimal_and_falls_back_to_full(): void
    {
        $this->assertSame(PushRelay::CONTENT_MINIMAL, $this->saveMainDetails(['pushRelayContent' => 'minimal'])[PushRelay::SETTING_CONTENT]);
        $this->assertSame(PushRelay::CONTENT_FULL, $this->saveMainDetails(['pushRelayContent' => 'full'])[PushRelay::SETTING_CONTENT]);
        $this->assertSame(PushRelay::CONTENT_NAMES, $this->saveMainDetails(['pushRelayContent' => 'names'])[PushRelay::SETTING_CONTENT]);
        $this->assertSame(PushRelay::CONTENT_FULL, $this->saveMainDetails(['pushRelayContent' => 'everything'])[PushRelay::SETTING_CONTENT]);
    }

    // ---------------------------------------------------------------------
    // Emoji suggestions in the text editor, a company-wide switch.
    // ---------------------------------------------------------------------

    /**
     * The form posts '0' (hidden input) or '1' (checked box), and RPC callers may send booleans.
     * The switch is saved on its own, without the main company details.
     *
     * @dataProvider emojiSwitchProvider
     */
    public function test_the_emoji_switch_is_saved_on_its_own_as_a_boolean(mixed $sent, string $stored): void
    {
        $written = [];
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => false,
            'saveSetting' => function ($key, $value) use (&$written) {
                $written[$key] = $value;

                return true;
            },
        ]);

        $saved = $this->makeService(settingsRepo: $settingsRepo)->saveCompanySettings(['emojiSuggestions' => $sent]);

        $this->assertTrue($saved);
        $this->assertSame([SettingService::SETTING_EMOJI_SUGGESTIONS => $stored], $written);
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function emojiSwitchProvider(): array
    {
        return [
            '"1"' => ['1', '1'],
            'true' => [true, '1'],
            '"0"' => ['0', '0'],
            'false' => [false, '0'],
            '"false"' => ['false', '0'],
        ];
    }

    /**
     * A value that is not a boolean is rejected before anything is saved: it must neither switch
     * the suggestions off nor leave the rest of the form half saved.
     *
     * @dataProvider invalidEmojiSwitchProvider
     */
    public function test_an_invalid_emoji_switch_is_rejected_before_anything_is_saved(mixed $sent): void
    {
        $written = [];
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => false,
            'saveSetting' => function ($key, $value) use (&$written) {
                $written[$key] = $value;

                return true;
            },
        ]);

        try {
            $this->makeService(settingsRepo: $settingsRepo)->saveCompanySettings(
                ['emojiSuggestions' => $sent, 'name' => 'Acme', 'language' => 'en-US', 'messageFrequency' => 3600]
            );
            $this->fail('An invalid value must throw a ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertSame([], $written);
        }
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidEmojiSwitchProvider(): array
    {
        return [
            'a word' => ['enabled'],
            'a sentence' => ['yes please'],
            'an array' => [['1']],
            'null' => [null],
        ];
    }

    /**
     * The switch is on by default, so a save that doesn't carry it (API, other forms) must not
     * switch it off.
     */
    public function test_a_save_without_the_emoji_switch_leaves_it_alone(): void
    {
        $this->assertArrayNotHasKey(SettingService::SETTING_EMOJI_SUGGESTIONS, $this->saveMainDetails([]));
    }

    /**
     * @dataProvider emojiSuggestionsStoredProvider
     */
    public function test_emoji_suggestions_are_on_unless_switched_off(string|false $stored, bool $expected): void
    {
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn ($key) => $key === SettingService::SETTING_EMOJI_SUGGESTIONS ? $stored : false,
        ]);

        $view = $this->makeService(settingsRepo: $settingsRepo)->getCompanySettings('');

        $this->assertSame($expected, $view['companySettings']['emojiSuggestions']);
    }

    /**
     * @return array<string, array{string|false, bool}>
     */
    public static function emojiSuggestionsStoredProvider(): array
    {
        return [
            'never saved' => [false, true],
            'switched on' => ['1', true],
            'switched off' => ['0', false],
        ];
    }
}
