<?php

namespace Leantime\Core\Mcp;

use Illuminate\Support\ServiceProvider;
use Leantime\Domain\Mcp\Services\ToolRegistry;
use Leantime\Domain\Mcp\Tools\ApprovalsList;
use Leantime\Domain\Mcp\Tools\ApprovalsResolve;
use Leantime\Domain\Mcp\Tools\CommentsAdd;
use Leantime\Domain\Mcp\Tools\CommentsDelete;
use Leantime\Domain\Mcp\Tools\CommentsList;
use Leantime\Domain\Mcp\Tools\OperationsGet;
use Leantime\Domain\Mcp\Tools\ProjectMembers;
use Leantime\Domain\Mcp\Tools\ProjectMembersUpdate;
use Leantime\Domain\Mcp\Tools\ProjectsCreate;
use Leantime\Domain\Mcp\Tools\ProjectsGet;
use Leantime\Domain\Mcp\Tools\ProjectsList;
use Leantime\Domain\Mcp\Tools\ProjectsUpdate;
use Leantime\Domain\Mcp\Tools\TicketsCreate;
use Leantime\Domain\Mcp\Tools\TicketsGet;
use Leantime\Domain\Mcp\Tools\TicketsSearch;
use Leantime\Domain\Mcp\Tools\TicketsUpdate;
use Leantime\Domain\Mcp\Tools\TicketsUpdateStatus;
use Leantime\Domain\Mcp\Tools\TimesheetsLog;
use Leantime\Domain\Mcp\Tools\UsersGet;

class McpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ToolRegistry::class, function () {
            return new ToolRegistry([
                ProjectsList::class,
                ProjectsGet::class,
                ProjectsCreate::class,
                ProjectsUpdate::class,
                ProjectMembers::class,
                ProjectMembersUpdate::class,
                TicketsCreate::class,
                TicketsGet::class,
                TicketsSearch::class,
                TicketsUpdate::class,
                CommentsList::class,
                CommentsAdd::class,
                CommentsDelete::class,
                UsersGet::class,
                OperationsGet::class,
                TimesheetsLog::class,
                TicketsUpdateStatus::class,
                ApprovalsList::class,
                ApprovalsResolve::class,
            ]);
        });
    }
}
