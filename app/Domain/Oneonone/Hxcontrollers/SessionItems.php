<?php

namespace Leantime\Domain\Oneonone\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Oneonone\Repositories\Oneonone as OneononeRepo;
use Leantime\Domain\Oneonone\Services\Oneonone as OneononeService;

/**
 * HTMX controller for live editing of items inside a 1:1 session.
 *
 * Endpoints:
 *  - POST   /hx/oneonone/sessionItems/addItem
 *  - PATCH  /hx/oneonone/sessionItems/toggleItem
 *  - PATCH  /hx/oneonone/sessionItems/updateItem
 *  - DELETE /hx/oneonone/sessionItems/deleteItem
 *  - GET    /hx/oneonone/sessionItems/list
 *  - GET    /hx/oneonone/sessionItems/myOpen
 *
 * Note on `static::$view`: the HtmxController contract requires a static
 * `$view` property. Every action method explicitly assigns the static
 * before rendering (either `myOpen()` or `renderList()`), so the value is
 * deterministic per request and safe under persistent workers.
 */
class SessionItems extends HtmxController
{
    protected static string $view = 'oneonone::partials.itemList';

    private OneononeService $service;

    private OneononeRepo $repo;

    /** @var array<string, mixed>|null */
    private ?array $parsedNonPostBody = null;

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
        $sessionId = (int) $this->getRequestValue('sessionId', 0);
        $values = [
            'type' => $this->getRequestValue('type', 'talking_point'),
            'content' => $this->getRequestValue('content', ''),
            'assignedTo' => $this->getRequestValue('assignedTo', null),
            'dueDate' => $this->getRequestValue('dueDate', null),
        ];

        $savedItemId = $this->service->addItem($sessionId, $values);

        if ($savedItemId === false) {
            $this->tpl->setNotification($this->language->__('notification.oneonone.session_save_failed'), 'error');
            $this->renderList($sessionId, $values['type']);

            return;
        }

        $this->setHTMXEvent('oneonone_item_changed');
        $this->renderList($sessionId, $values['type']);
    }

    /** Toggle an item's open/done state. */
    public function toggleItem(): void
    {
        $itemId = (int) $this->getRequestValue('itemId', 0);
        $sessionId = (int) $this->getRequestValue('sessionId', 0);

        if ($sessionId === 0) {
            $sessionId = $this->getSessionIdForItem($itemId);
        }

        $toggled = $this->service->toggleItem($itemId);
        if (! $toggled) {
            $this->tpl->setNotification($this->language->__('notification.oneonone.session_save_failed'), 'error');
            $this->renderList($sessionId);

            return;
        }

        $this->setHTMXEvent('oneonone_item_changed');
        $this->renderList($sessionId);
    }

    /** Update an item's content (inline edit). */
    public function updateItem(): void
    {
        $itemId = (int) $this->getRequestValue('itemId', 0);
        $sessionId = (int) $this->getRequestValue('sessionId', 0);
        if ($sessionId === 0) {
            $sessionId = $this->getSessionIdForItem($itemId);
        }

        $values = [];
        foreach (['content', 'assignedTo', 'dueDate', 'status', 'type'] as $key) {
            $value = $this->getRequestValue($key, null);
            if ($value !== null) {
                $values[$key] = $value;
            }
        }

        $updated = $this->service->updateItem($itemId, $values);
        if (! $updated) {
            $this->tpl->setNotification($this->language->__('notification.oneonone.session_save_failed'), 'error');
            $this->renderList($sessionId);

            return;
        }

        $this->setHTMXEvent('oneonone_item_changed');
        $this->renderList($sessionId);
    }

    /** Delete an item. */
    public function deleteItem(): void
    {
        $itemId = (int) $this->getRequestValue('itemId', 0);
        $sessionId = (int) $this->getRequestValue('sessionId', 0);
        if ($sessionId === 0) {
            $sessionId = $this->getSessionIdForItem($itemId);
        }

        $deleted = $this->service->deleteItem($itemId);
        if (! $deleted) {
            $this->tpl->setNotification($this->language->__('notification.oneonone.delete_failed'), 'error');
            $this->renderList($sessionId);

            return;
        }

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

    /**
     * Read a request value using this precedence: parsed params, request bag,
     * query bag, parsed DELETE/PUT body, then provided default.
     *
     * @param  string  $key  Request key to read.
     * @param  mixed  $default  Default value when no key is present.
     * @return mixed
     */
    private function getRequestValue(string $key, mixed $default = null): mixed
    {
        $params = $this->incomingRequest->getRequestParams();
        if (array_key_exists($key, $params)) {
            return $params[$key];
        }

        $requestValues = $this->incomingRequest->request->all();
        if (array_key_exists($key, $requestValues)) {
            return $requestValues[$key];
        }

        $queryValues = $this->incomingRequest->query->all();
        if (array_key_exists($key, $queryValues)) {
            return $queryValues[$key];
        }

        $bodyValues = $this->getParsedNonPostBody();
        if (array_key_exists($key, $bodyValues)) {
            return $bodyValues[$key];
        }

        return $default;
    }

    /**
     * Parse and cache request body variables for non-POST form requests.
     *
     * @return array<string, mixed>
     */
    private function getParsedNonPostBody(): array
    {
        if ($this->parsedNonPostBody !== null) {
            return $this->parsedNonPostBody;
        }

        $this->parsedNonPostBody = [];
        if (! in_array(strtoupper($this->incomingRequest->method()), ['DELETE', 'PUT'], true)) {
            return $this->parsedNonPostBody;
        }

        $contentType = strtolower((string) $this->incomingRequest->headers->get('content-type', ''));
        if ($contentType !== '' && ! str_contains($contentType, 'application/x-www-form-urlencoded')) {
            return $this->parsedNonPostBody;
        }

        $rawContent = (string) $this->incomingRequest->getContent();
        if ($rawContent === '') {
            return $this->parsedNonPostBody;
        }

        parse_str($rawContent, $bodyVars);
        if (is_array($bodyVars)) {
            $this->parsedNonPostBody = $bodyVars;
        }

        return $this->parsedNonPostBody;
    }
}
