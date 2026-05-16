<?php

namespace Leantime\Domain\Notepad\Services;

use Leantime\Domain\Notepad\Repositories\Notepad as NotepadRepository;

/**
 * Personal notepad service. Thin wrapper over the repository that enforces the
 * "session user only" rule for every operation — never accept a userId argument
 * from outside this class.
 */
class Notepad
{
    public function __construct(private NotepadRepository $repo) {}

    /**
     * Get last 7 days of tasks for the logged-in user, grouped by date desc.
     * Returns only dates that have at least one task.
     */
    public function getMyRecentTasks(int $days = 7): array
    {
        return $this->repo->getRecentTasksByDay($this->currentUserId(), $days);
    }

    public function addTask(string $taskDate, string $content): int
    {
        $content = trim($content);
        if ($content === '') {
            $content = ' ';
        }

        return $this->repo->create($this->currentUserId(), $taskDate, $content);
    }

    public function updateTask(int $id, string $content): bool
    {
        return $this->repo->updateContent($id, $this->currentUserId(), trim($content));
    }

    public function toggleTask(int $id, bool $done): bool
    {
        return $this->repo->toggleDone($id, $this->currentUserId(), $done);
    }

    public function deleteTask(int $id): bool
    {
        return $this->repo->delete($id, $this->currentUserId());
    }

    public function getTask(int $id): ?array
    {
        return $this->repo->find($id, $this->currentUserId());
    }

    private function currentUserId(): int
    {
        return (int) session('userdata.id');
    }
}
