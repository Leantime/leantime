<?php

namespace Leantime\Domain\Notepad\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Notepad\Services\Notepad as NotepadService;

/**
 * Personal notepad HTMX endpoints.
 *
 * POST  /hx/notepad/tasks/add        Body: { taskDate, content }     → re-renders the day section
 * POST  /hx/notepad/tasks/save       Body: { id, content }           → silent autosave (empty response)
 * POST  /hx/notepad/tasks/toggle     Body: { id, done }              → silent toggle  (empty response)
 * POST  /hx/notepad/tasks/delete     Body: { id, taskDate }          → re-renders the day section
 */
class Tasks extends HtmxController
{
    protected static string $view = 'notepad::partials.daySection';

    private NotepadService $service;

    public function init(NotepadService $service): void
    {
        $this->service = $service;
    }

    private function denyClientPortal(): bool
    {
        if (session('userdata.role') === Roles::$commenter) {
            $this->tpl->setNotification('errors.error403', 'error');

            return true;
        }

        return false;
    }

    /**
     * Initial load — returns the full notepad body (all 7 days) for the popup.
     */
    public function load(): void
    {
        if ($this->denyClientPortal()) {
            return;
        }

        $tasksByDay = $this->service->getMyRecentTasks(7);

        $this->tpl->assign('tasksByDay', $tasksByDay);
        $this->tpl->assign('today', date('Y-m-d'));
        static::$view = 'notepad::partials.body';
    }

    public function add(): void
    {
        if ($this->denyClientPortal()) {
            return;
        }

        $taskDate = (string) ($this->incomingRequest->request->get('taskDate') ?? date('Y-m-d'));
        $content = (string) ($this->incomingRequest->request->get('content') ?? '');

        $this->service->addTask($taskDate, $content);

        $this->renderDaySection($taskDate);
    }

    public function save(): void
    {
        if ($this->denyClientPortal()) {
            return;
        }

        $id = (int) ($this->incomingRequest->request->get('id') ?? 0);
        $content = (string) ($this->incomingRequest->request->get('content') ?? '');

        $this->service->updateTask($id, $content);

        // Silent autosave — no view re-render
        static::$view = 'notepad::partials.empty';
    }

    public function toggle(): void
    {
        if ($this->denyClientPortal()) {
            return;
        }

        $id = (int) ($this->incomingRequest->request->get('id') ?? 0);
        $done = (bool) ($this->incomingRequest->request->get('done') ?? false);

        $this->service->toggleTask($id, $done);

        static::$view = 'notepad::partials.empty';
    }

    public function delete(): void
    {
        if ($this->denyClientPortal()) {
            return;
        }

        $id = (int) ($this->incomingRequest->request->get('id') ?? 0);
        $taskDate = (string) ($this->incomingRequest->request->get('taskDate') ?? '');

        $this->service->deleteTask($id);

        if ($taskDate === '') {
            $taskDate = date('Y-m-d');
        }

        $this->renderDaySection($taskDate);
    }

    /**
     * Re-render a single day's task list (with the wrapping section).
     */
    private function renderDaySection(string $taskDate): void
    {
        $tasksByDay = $this->service->getMyRecentTasks(7);
        $tasks = $tasksByDay[$taskDate] ?? [];

        $this->tpl->assign('taskDate', $taskDate);
        $this->tpl->assign('tasks', $tasks);
    }
}
