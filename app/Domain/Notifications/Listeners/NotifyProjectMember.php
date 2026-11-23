<?php

namespace Leantime\Domain\Notifications\Listeners;

use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Services\ProjectMemberNotifier;
use Leantime\Domain\Projects\Events\ProjectMemberAdded;

/**
 * Tells someone they were added to a project (see ProjectMemberNotifier). Runs after the
 * membership was written, so a failure here is logged and never fails the save.
 */
class NotifyProjectMember
{
    public function handle(ProjectMemberAdded $event): void
    {
        try {
            app()->make(ProjectMemberNotifier::class)->notify($event);
        } catch (\Throwable $e) {
            Log::warning('Project member notification failed for project #'.$event->projectId.' ('.get_class($e).($e->getCode() ? ' '.$e->getCode() : '').')');
        }
    }
}
