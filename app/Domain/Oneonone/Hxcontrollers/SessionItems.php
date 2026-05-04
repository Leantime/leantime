<?php

namespace Leantime\Domain\Oneonone\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Oneonone\Repositories\Oneonone as OneononeRepo;
use Leantime\Domain\Oneonone\Services\Oneonone as OneononeService;

/**
 * HTMX controller for live editing of items inside a 1:1 session.
 *
 * Endpoints:
 *  - POST  /hx/oneonone/sessionItems/addItem
 *  - PATCH /hx/oneonone/sessionItems/toggleItem
 *  - PATCH /hx/oneonone/sessionItems/updateItem
 *  - DELETE /hx/oneonone/sessionItems/deleteItem
 *  - GET   /hx/oneonone/sessionItems/list
 */
class SessionItems extends HtmxController
{
    protected static string $view = 'oneonone::partials.itemList';

    private OneononeService $service;

    private OneononeRepo $repo;

    public function init(OneononeService $service, OneononeRepo $repo): void
    {
        $this->service = $service;
        $this->repo = $repo;
    }

    /** Render the user's own open action items panel (refreshes on oneonone_item_changed). */
    public function myOpen(): void
    {
        static::$view = 'oneonone::partials.myOpenActions';
        $this->tpl->assign('openActionItems', $this->service->getMyOpenActionItems());
    }

    /** Render the full grouped item list for a session (used for re-rendering after mutations). */
    public function list(): void
    {
        $sessionId = (int) ($this->incomingRequest->query->get('sessionId') ?? 0);
        $type = $this->incomingRequest->query->get('type');
        $this->renderList($sessionId, is_string($type) ? $type : null);
    }

    /** Add a new item. Expects sessionId, type, content (and optionally assignedTo, dueDate). */
    public function addItem(): void
    {
        $sessionId = (int) ($_POST['sessionId'] ?? 0);
        $values = [
            'type' => $_POST['type'] ?? 'talking_point',
            'content' => $_POST['content'] ?? '',
            'assignedTo' => $_POST['assignedTo'] ?? null,
            'dueDate' => $_POST['dueDate'] ?? null,
        ];

        $this->service->addItem($sessionId, $values);

        $this->setHTMXEvent('oneonone_item_changed');
        $this->renderList($sessionId, $values['type']);
    }

    /** Toggle an item's open/done state. */
    public function toggleItem(): void
    {
        $itemId = (int) ($_REQUEST['itemId'] ?? 0);

        $sessionId = $this->getSessionIdForItem($itemId);
        $this->service->toggleItem($itemId);

        $this->setHTMXEvent('oneonone_item_changed');
        $this->renderList($sessionId);
    }

    /** Update an item's content (inline edit). */
    public function updateItem(): void
    {
        $itemId = (int) ($_REQUEST['itemId'] ?? 0);
        $sessionId = $this->getSessionIdForItem($itemId);

        $values = [];
        foreach (['content', 'assignedTo', 'dueDate', 'status', 'type'] as $key) {
            if (array_key_exists($key, $_REQUEST)) {
                $values[$key] = $_REQUEST[$key];
            }
        }

        $this->service->updateItem($itemId, $values);

        $this->setHTMXEvent('oneonone_item_changed');
        $this->renderList($sessionId);
    }

    /** Delete an item. */
    public function deleteItem(): void
    {
        $itemId = (int) ($_REQUEST['itemId'] ?? 0);
        $sessionId = $this->getSessionIdForItem($itemId);

        $this->service->deleteItem($itemId);

        $this->setHTMXEvent('oneonone_item_changed');
        $this->renderList($sessionId);
    }

    private function getSessionIdForItem(int $itemId): int
    {
        $item = $this->repo->getItem($itemId);

        return $item ? (int) $item['sessionId'] : 0;
    }

    private function renderList(int $sessionId, ?string $focusType = null): void
    {
        static::$view = 'oneonone::partials.itemList';

        $session = $sessionId > 0 ? $this->service->getSession($sessionId) : null;

        $this->tpl->assign('session', $session);
        $this->tpl->assign('itemsByType', $session ? $this->service->getItemsGrouped($sessionId) : []);
        $this->tpl->assign('itemTypes', $this->repo->itemTypes);
        $this->tpl->assign('canEdit', $session ? $this->service->canEditSession($session) : false);
        $this->tpl->assign('focusType', $focusType);
    }
}
