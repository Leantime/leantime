<?php

declare(strict_types=1);

namespace Leantime\Domain\Tickets\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Support\WorkStart;

/**
 * Plans a to-do for a day: the day someone chose to work on it, which they can move any time.
 *
 * A plan is a date-only Work Start ({@see WorkStart}): local midnight of the day, with no Work
 * End. Planning never changes the due date, so a plan never becomes a deadline and a missed plan
 * is never overdue. It also never moves a time box: a to-do whose Work Start has a real time is
 * left alone, and changed from the to-do or the calendar instead.
 *
 * Days are YYYY-MM-DD in the signed-in person's own timezone.
 *
 * Permissions: entity-scoped like the other per-to-do calls, so a plan doesn't depend on the
 * project currently selected in the session. The write goes through {@see Tickets::patch()},
 * which requires tickets.edit in the to-do's own project.
 */
class Planning extends BaseService
{
    public function __construct(
        private Tickets $tickets,
    ) {}

    /**
     * Plans a to-do for a day, replacing an earlier date-only plan.
     *
     * @param  int  $taskId  The to-do.
     * @param  string  $date  The day, as YYYY-MM-DD in your own timezone.
     * @return array{taskId: int, date: string}
     *
     * @throws ValidationException When the date isn't a real day, the to-do doesn't exist or you can't see it, it's a milestone, or it has a set time.
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::EDIT, entityScoped: true)]
    public function planTaskForDay(int $taskId, string $date): array
    {
        $timezone = self::timezone();

        $start = WorkStart::startOfLocalDay($date, $timezone);
        if ($start === null) {
            throw new ValidationException(['date' => ['Use a real day, written as YYYY-MM-DD.']]);
        }

        $task = $this->plannableTask($taskId);

        if (WorkStart::isTimeBoxed(self::stored($task->editFrom), self::stored($task->editTo), $timezone)) {
            throw new ValidationException(['taskId' => ['This to-do has a set time. Change it on the to-do or in the calendar.']]);
        }

        $this->write($taskId, ['editFrom' => $start, 'editTo' => null]);

        return ['taskId' => $taskId, 'date' => $date];
    }

    /**
     * Clears a to-do's plan. A to-do with no plan is fine; one with a set time is left alone.
     *
     * @param  int  $taskId  The to-do.
     * @return bool True once the to-do has no date-only plan.
     *
     * @throws ValidationException When the to-do doesn't exist or you can't see it, it's a milestone, or it has a set time.
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::EDIT, entityScoped: true)]
    public function clearTaskPlan(int $taskId): bool
    {
        $timezone = self::timezone();
        $task = $this->plannableTask($taskId);

        if (WorkStart::plannedDays(self::stored($task->editFrom), self::stored($task->editTo), $timezone) === []) {
            return true;
        }

        if (WorkStart::isTimeBoxed(self::stored($task->editFrom), self::stored($task->editTo), $timezone)) {
            throw new ValidationException(['taskId' => ['This to-do has a set time. Change it on the to-do or in the calendar.']]);
        }

        $this->write($taskId, ['editFrom' => null, 'editTo' => null]);

        return true;
    }

    /**
     * The days a to-do is planned for, in your own timezone. Empty when it has no Work Start.
     *
     * @param  int  $taskId  The to-do.
     * @return array{taskId: int, days: list<string>, timeBoxed: bool}
     *
     * @throws ValidationException When the to-do doesn't exist or you can't see it.
     *
     * @api
     */
    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function getTaskPlan(int $taskId): array
    {
        $timezone = self::timezone();
        $task = $this->visibleTask($taskId);

        return [
            'taskId' => $taskId,
            'days' => WorkStart::plannedDays(self::stored($task->editFrom), self::stored($task->editTo), $timezone),
            'timeBoxed' => WorkStart::isTimeBoxed(self::stored($task->editFrom), self::stored($task->editTo), $timezone),
        ];
    }

    /**
     * A stored Work Start value as a string, or null (the model types it loosely).
     */
    private static function stored(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    /**
     * The signed-in person's timezone name.
     */
    private static function timezone(): string
    {
        return dtHelper()->userNow()->getTimezone()->getName();
    }

    /**
     * A to-do you can see, or a field error. One message for "doesn't exist" and "can't see it",
     * so the reply never reveals whether a hidden to-do exists.
     *
     * getTicket() checks project access only, so tickets.view in the to-do's own project is
     * checked here too. Every endpoint goes through this before any other check, so a person who
     * can't view the to-do can't tell a milestone or a time box apart from a missing to-do.
     *
     * @throws ValidationException
     */
    private function visibleTask(int $taskId): TicketModel
    {
        $task = $taskId > 0 ? $this->tickets->getTicket($taskId) : false;
        if (! $task instanceof TicketModel || ! $this->can(TicketsPermissions::VIEW, (int) $task->projectId)) {
            throw new ValidationException(['taskId' => ["That to-do doesn't exist, or you can't see it."]]);
        }

        return $task;
    }

    /**
     * A to-do you can see that can carry a plan (not a milestone).
     *
     * @throws ValidationException
     */
    private function plannableTask(int $taskId): TicketModel
    {
        $task = $this->visibleTask($taskId);
        if (($task->type ?? '') === 'milestone') {
            throw new ValidationException(['taskId' => ["Milestones can't be planned. Plan the to-dos in it instead."]]);
        }

        return $task;
    }

    /**
     * Writes the Work Start through patch(), which checks tickets.edit in the to-do's project.
     *
     * @param  array<string, mixed>  $values
     *
     * @throws ValidationException When the write didn't happen.
     */
    private function write(int $taskId, array $values): void
    {
        if (! $this->tickets->patch($taskId, $values)) {
            throw new ValidationException(['taskId' => ["That to-do couldn't be updated. Try again."]]);
        }
    }
}
