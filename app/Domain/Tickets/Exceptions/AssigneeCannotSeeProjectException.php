<?php

namespace Leantime\Domain\Tickets\Exceptions;

use Leantime\Core\Exceptions\ValidationException;

/**
 * Thrown when a task write would assign someone (or add a collaborator) who can't see the
 * task's project. They would never see the task, and nobody would tell them about it.
 *
 * Carries the user and project so callers can offer a way out: the MCP tools tell the AI to
 * ask whether to add the user to the project team, then try again.
 */
class AssigneeCannotSeeProjectException extends ValidationException
{
    /**
     * @param  string  $field  The field that named the user: 'editorId' or 'collaborators'.
     */
    public function __construct(
        public readonly string $field,
        public readonly int $userId,
        public readonly string $userName,
        public readonly int $projectId,
        public readonly string $projectName,
        string $message,
    ) {
        parent::__construct([$field => [$message]], $message);
    }
}
