<?php

namespace Leantime\Domain\Oneonone\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Oneonone\Services\Oneonone as OneononeService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Personal "My 1:1s" page - timeline of the current user's own 1:1 sessions
 * plus a summary of their outstanding action items.
 */
class ShowMy extends Controller
{
    private OneononeService $service;

    public function init(OneononeService $service): void
    {
        $this->service = $service;
    }

    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$commenter, Roles::$editor, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $sessions = $this->service->getMySessions();
        $openActionItems = $this->service->getMyOpenActionItems();

        $stats = [
            'total' => count($sessions),
            'completed' => 0,
            'upcoming' => 0,
            'openActions' => count($openActionItems),
        ];

        foreach ($sessions as $session) {
            if (($session['status'] ?? '') === 'completed') {
                $stats['completed']++;
            }
            $isUpcoming = false;
            if (($session['status'] ?? '') === 'scheduled' && ! empty($session['meetingDate'])) {
                try {
                    $isUpcoming = dtHelper()->parseDbDateTime((string) $session['meetingDate'])->isFuture();
                } catch (\Exception $e) {
                    $isUpcoming = false;
                }
            }
            if ($isUpcoming) {
                $stats['upcoming']++;
            }
        }

        $this->tpl->assign('sessions', $sessions);
        $this->tpl->assign('openActionItems', $openActionItems);
        $this->tpl->assign('stats', $stats);

        return $this->tpl->display('oneonone.showMy');
    }
}
