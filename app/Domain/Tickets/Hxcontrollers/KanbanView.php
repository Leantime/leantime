<?php

declare(strict_types=1);

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Services\KanbanViewSettings;
use Symfony\Component\HttpFoundation\Response;

/**
 * Saves the per-user kanban board view preferences: card fields (#1859) and sort (#1536).
 *
 * Cards are rendered server-side, so a successful save asks HTMX to reload the board.
 */
class KanbanView extends HtmxController
{
    protected static string $view = 'tickets::partials.kanbanViewMenu';

    private KanbanViewSettings $kanbanViewSettings;

    /**
     * Controller constructor
     */
    public function init(KanbanViewSettings $kanbanViewSettings): void
    {
        $this->kanbanViewSettings = $kanbanViewSettings;
    }

    /**
     * Stores the submitted card fields and sort for the current user and the board's project.
     *
     * The project comes from the form (the board it was rendered for), not the session, which
     * another tab may have switched; the service validates it and authorizes against it.
     *
     * @return Response Empty response that refreshes the board (or shows an error toast).
     */
    #[RequiresPermission(TicketsPermissions::VIEW, entityScoped: true)]
    public function save(): Response
    {
        $projectId = filter_var($this->incomingRequest->input('projectId'), FILTER_VALIDATE_INT);
        if ($projectId === false || $projectId <= 0) {
            $this->tpl->setNotification($this->language->__('notifications.kanban_view_not_saved'), 'error');

            return $this->tpl->emptyResponse();
        }
        $visibleFieldNames = $this->incomingRequest->input('fields', []);
        $sort = $this->incomingRequest->input('sort', KanbanViewSettings::DEFAULT_SORT);

        $saved = $this->kanbanViewSettings->saveForCurrentUser($projectId, $visibleFieldNames, $sort);

        if (! $saved) {
            $this->tpl->setNotification($this->language->__('notifications.kanban_view_not_saved'), 'error');

            return $this->tpl->emptyResponse();
        }

        return new Response('', Response::HTTP_OK, ['HX-Refresh' => 'true']);
    }
}
