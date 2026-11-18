<?php

namespace Unit\app\Domain\Goalcanvas\Controllers;

use Carbon\CarbonImmutable;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\UI\Template;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Goalcanvas\Controllers\EditCanvasItem;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvasRepository;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Unit\TestCase;

/**
 * Goal start/end dates are entered in the user's timezone and must be stored in UTC, because
 * the dialog reads them back as UTC (#4046). The start date used to be stored as local midnight,
 * so a user behind UTC saw the previous day after saving, and every further save moved it back
 * another day.
 */
class EditCanvasItemDatesTest extends TestCase
{
    /** @var array<string, mixed>|null */
    private ?array $savedItem = null;

    private function useUserTimezone(string $timezone): void
    {
        session([
            'usersettings.timezone' => $timezone,
            'usersettings.language' => 'en-US',
            'usersettings.date_format' => 'm/d/Y',
            'usersettings.time_format' => 'h:i A',
            'userdata.id' => 1,
            'userdata.name' => 'Test User',
            'currentProject' => 7,
        ]);

        CarbonImmutable::mixin(new CarbonMacros($timezone, 'en-US', 'm/d/Y', 'h:i A'));
    }

    private function controller(): EditCanvasItem
    {
        $goalService = $this->createMock(GoalcanvasService::class);
        $goalService->method('getGoalItem')->willReturn(['id' => 5, 'projectId' => 7]);
        $goalService->method('updateGoalItem')->willReturnCallback(function (array $values): void {
            $this->savedItem = $values;
        });
        $goalService->method('createGoalItem')->willReturnCallback(function (array $values): string {
            $this->savedItem = $values;

            return '5';
        });
        app()->instance(GoalcanvasService::class, $goalService);

        $canvasRepo = $this->createMock(GoalcanvasRepository::class);
        $canvasRepo->method('getCanvasTypes')->willReturn(['goal' => ['title' => 'Goal']]);
        app()->instance(GoalcanvasRepository::class, $canvasRepo);

        app()->instance(CommentRepository::class, $this->createMock(CommentRepository::class));
        app()->instance(TicketService::class, $this->createMock(TicketService::class));
        app()->instance(ProjectService::class, $this->createMock(ProjectService::class));

        return new EditCanvasItem(
            $this->createMock(IncomingRequest::class),
            $this->createMock(Template::class),
            $this->createMock(Language::class),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function dialogPost(string $itemId): array
    {
        return [
            'changeItem' => 1,
            'itemId' => $itemId,
            'canvasId' => 3,
            'box' => 'goal',
            'title' => 'Grow revenue',
            'startDate' => '11/02/2026',
            'endDate' => '03/31/2027',
        ];
    }

    /**
     * Saves the dialog, then reads the stored dates back the way canvasDialog.blade.php shows them.
     *
     * @return array{0: string, 1: string}
     */
    private function saveAndReloadDates(array $post): array
    {
        $this->controller()->post($post);

        $this->assertNotNull($this->savedItem, 'The goal was not saved.');

        return [
            format($this->savedItem['startDate'])->date(),
            format($this->savedItem['endDate'])->date(),
        ];
    }

    public function test_updating_a_goal_stores_dates_in_utc_for_a_user_behind_utc(): void
    {
        $this->useUserTimezone('America/New_York');

        $this->controller()->post($this->dialogPost('5'));

        // Midnight / end of day in New York (EST, UTC-5), expressed in UTC.
        $this->assertSame('2026-11-02 05:00:00', $this->savedItem['startDate']);
        $this->assertSame('2027-04-01 03:59:59', $this->savedItem['endDate']);
    }

    public function test_updated_goal_keeps_its_calendar_dates_after_reload_for_a_user_behind_utc(): void
    {
        $this->useUserTimezone('America/New_York');

        $this->assertSame(['11/02/2026', '03/31/2027'], $this->saveAndReloadDates($this->dialogPost('5')));
    }

    public function test_saving_the_reloaded_dates_again_does_not_drift(): void
    {
        $this->useUserTimezone('America/New_York');

        [$startShown, $endShown] = $this->saveAndReloadDates($this->dialogPost('5'));

        // Save the dialog again with exactly what it showed.
        $secondPost = array_merge($this->dialogPost('5'), ['startDate' => $startShown, 'endDate' => $endShown]);

        $this->assertSame(['11/02/2026', '03/31/2027'], $this->saveAndReloadDates($secondPost));
    }

    public function test_new_goal_keeps_its_calendar_dates_after_reload_for_a_user_behind_utc(): void
    {
        $this->useUserTimezone('America/New_York');
        $_POST['title'] = 'Grow revenue';

        try {
            $this->assertSame(['11/02/2026', '03/31/2027'], $this->saveAndReloadDates($this->dialogPost('')));
        } finally {
            unset($_POST['title']);
        }
    }

    public function test_goal_keeps_its_calendar_dates_after_reload_for_a_user_ahead_of_utc(): void
    {
        $this->useUserTimezone('Asia/Tokyo');

        $this->assertSame(['11/02/2026', '03/31/2027'], $this->saveAndReloadDates($this->dialogPost('5')));
    }
}
