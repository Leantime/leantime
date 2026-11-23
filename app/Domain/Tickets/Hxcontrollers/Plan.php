<?php

declare(strict_types=1);

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\ValidationException;
use Leantime\Domain\Tickets\Services\Planning;
use Leantime\Domain\Tickets\Support\WorkStart;
use Symfony\Component\HttpFoundation\Response;

/**
 * The to-do ⋮ menu's "Plan" items: plan a to-do for today, tomorrow or a picked day, or clear
 * the plan. Each action answers with a notification only; the write dispatches TicketUpdated, so
 * lists showing the to-do refresh themselves.
 */
class Plan extends HtmxController
{
    protected static string $view = 'tickets::partials.planMenu';

    private Planning $planning;

    /**
     * Controller constructor.
     */
    public function init(Planning $planning): void
    {
        $this->planning = $planning;
    }

    /**
     * Plans a to-do for a day. POST `id` and `day`: "today", "tomorrow" or YYYY-MM-DD.
     */
    public function set(): Response
    {
        $params = $this->incomingRequest->request->all();
        $taskId = is_numeric($params['id'] ?? null) ? (int) $params['id'] : 0;
        $date = $this->resolveDay(is_string($params['day'] ?? null) ? $params['day'] : '');

        try {
            $this->planning->planTaskForDay($taskId, $date);
            $this->tpl->setNotification($this->plannedMessage($date), 'success');
        } catch (ValidationException $e) {
            $this->tpl->setNotification($this->firstError($e), 'error');
        } catch (AuthorizationException) {
            $this->tpl->setNotification($this->language->__('short_notifications.plan_error'), 'error');
        }

        return $this->tpl->emptyResponse();
    }

    /**
     * Clears a to-do's plan. POST `id`.
     */
    public function clear(): Response
    {
        $params = $this->incomingRequest->request->all();
        $taskId = is_numeric($params['id'] ?? null) ? (int) $params['id'] : 0;

        try {
            $this->planning->clearTaskPlan($taskId);
            $this->tpl->setNotification($this->language->__('short_notifications.plan_cleared'), 'success');
        } catch (ValidationException $e) {
            $this->tpl->setNotification($this->firstError($e), 'error');
        } catch (AuthorizationException) {
            $this->tpl->setNotification($this->language->__('short_notifications.plan_error'), 'error');
        }

        return $this->tpl->emptyResponse();
    }

    /**
     * "today" and "tomorrow" in the signed-in person's timezone; anything else is passed on as given.
     */
    private function resolveDay(string $day): string
    {
        $now = dtHelper()->userNow();

        return match ($day) {
            'today' => $now->format('Y-m-d'),
            'tomorrow' => $now->addDay()->format('Y-m-d'),
            default => $day,
        };
    }

    /**
     * "Planned for today", "Planned for tomorrow", or "Planned for <date>".
     */
    private function plannedMessage(string $date): string
    {
        $now = dtHelper()->userNow();

        if ($date === $now->format('Y-m-d')) {
            return $this->language->__('label.planned_today');
        }

        if ($date === $now->addDay()->format('Y-m-d')) {
            return $this->language->__('label.planned_tomorrow');
        }

        $day = WorkStart::startOfLocalDay($date, $now->getTimezone()->getName());

        return sprintf(
            $this->language->__('short_notifications.planned_for'),
            $day !== null ? $day->formatDateForUser() : $date
        );
    }

    /**
     * The first field error, for a notification. Notifications render as HTML, so it's escaped.
     */
    private function firstError(ValidationException $e): string
    {
        foreach ($e->getErrorData() as $messages) {
            if (is_array($messages) && isset($messages[0]) && is_string($messages[0])) {
                return e($messages[0]);
            }
        }

        return $this->language->__('short_notifications.plan_error');
    }
}
