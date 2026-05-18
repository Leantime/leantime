<?php

namespace Leantime\Domain\WeeklyPlanning\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Delete a weekly plan (POST only). Only the team lead who created the plan or an admin may delete it.
 */
class DeletePlan extends Controller
{
    private WeeklyPlanningService $service;

    public function init(WeeklyPlanningService $service): void
    {
        Auth::authOrRedirect([Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $this->service = $service;
    }

    /**
     * GET: show a confirmation page.
     */
    public function get(array $params): Response
    {
        $planId = (int) ($params['id'] ?? 0);

        if (! $planId) {
            return Frontcontroller::redirect(BASE_URL.'/weekly-planning/showTeam');
        }

        $plan = $this->service->getPlanById($planId);

        if (! $plan) {
            $this->tpl->setNotification($this->language->__('weeklyplanning.notifications.plan_not_found'), 'error');

            return Frontcontroller::redirect(BASE_URL.'/weekly-planning/showTeam');
        }

        $userId = (int) session('userdata.id');
        $isOwner = (int) ($plan['teamLeadId'] ?? 0) === $userId;
        $isAdmin = Auth::userIsAtLeast(Roles::$admin);

        if (! $isOwner && ! $isAdmin) {
            $this->tpl->setNotification($this->language->__('notifications.not_authorized'), 'error');

            return Frontcontroller::redirect(BASE_URL.'/weekly-planning/showTeam');
        }

        $this->tpl->assign('plan', $plan);

        return $this->tpl->display('weeklyplanning.deletePlan');
    }

    /**
     * POST: perform the delete and redirect.
     */
    public function post(array $params): Response
    {
        $planId = (int) ($params['id'] ?? $_POST['id'] ?? 0);

        if (! $planId) {
            return Frontcontroller::redirect(BASE_URL.'/weekly-planning/showTeam');
        }

        if ($this->service->deletePlan($planId)) {
            $this->tpl->setNotification($this->language->__('weeklyplanning.notifications.plan_deleted'), 'success');
        } else {
            $this->tpl->setNotification($this->language->__('weeklyplanning.notifications.plan_delete_failed'), 'error');
        }

        return Frontcontroller::redirect(BASE_URL.'/weekly-planning/showTeam');
    }
}
