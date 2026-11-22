<?php

namespace Leantime\Domain\Tickets\Tools;

use Illuminate\Support\Str;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Domain\Tickets\Exceptions\AssigneeCannotSeeProjectException;

/**
 * The error text a task tool returns to the AI.
 *
 * Most errors are the client message as is. When the assignee or a collaborator isn't on the
 * project team, the AI also gets the way out: ask the user, add them to the team, try again.
 * That whole text goes through Str::sanitizeForLLM(): user and project names are in it.
 */
class ToolErrorMessage
{
    /** The tool that adds a user to a project team (core #4100); not on every server yet. */
    public const ADD_USER_TO_PROJECT_TOOL = 'Leantime\\Domain\\Projects\\Tools\\AddUserToProjectTool';

    /**
     * @param  bool|null  $canAddToTeam  Whether addUserToProject exists; null looks it up.
     */
    public static function for(LeantimeExceptionInterface $e, ?bool $canAddToTeam = null): string
    {
        if (! $e instanceof AssigneeCannotSeeProjectException) {
            return $e->getClientMessage();
        }

        $who = "{$e->userName} (user {$e->userId})";
        $where = "{$e->projectName} (project {$e->projectId})";

        if ($canAddToTeam ?? class_exists(self::ADD_USER_TO_PROJECT_TOOL)) {
            $next = "Ask the user whether to add {$who} to the {$where} team. Don't add them without asking. "
                ."If the user says yes, call addUserToProject with userId {$e->userId} and projectId {$e->projectId}, then try this again. "
                .'If not, pick someone from getUsersAssignedToProject instead.';
        } else {
            $next = "Tell the user that {$who} needs to be added to the {$where} team in Leantime first (project settings, Team). "
                .'Or pick someone from getUsersAssignedToProject instead.';
        }

        return Str::sanitizeForLLM($e->getClientMessage().' '.$next);
    }
}
