<?php

/**
 * Controller / Edit Canvas Item
 */

namespace Leantime\Domain\Goalcanvas\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Goalcanvas\Permissions\GoalcanvasPermissions;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvaService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Goal board (big rock) create/edit dialog. Standalone, independent of the canvas domain.
 */
class BigRock extends Controller
{
    private GoalcanvaService $goalService;

    public function init(
        GoalcanvaService $goalService
    ): void {
        $this->goalService = $goalService;
    }

    /**
     * @throws \Exception
     */
    #[RequiresPermission(GoalcanvasPermissions::VIEW, entityScoped: true)]
    public function get($params): Response
    {
        if (isset($params['id'])) {

            // getSingleCanvas authorizes VIEW against the board's real project and returns
            // false for a missing/foreign/unauthorized board.
            $bigrock = $this->goalService->getSingleCanvas($params['id']);
            if ($bigrock === false) {
                $bigrock = ['id' => '', 'title' => '', 'projectId' => '', 'author' => ''];
            }

        } else {

            $bigrock = ['id' => '', 'title' => '', 'projectId' => '', 'author' => ''];
        }

        $this->tpl->assign('bigRock', $bigrock);

        return $this->tpl->displayPartial('goalcanvas.bigRockDialog');
    }

    /**
     * @throws BindingResolutionException
     */
    #[RequiresPermission(GoalcanvasPermissions::EDIT, entityScoped: true)]
    public function post($params): Response
    {
        $bigrock = ['id' => '', 'title' => '', 'projectId' => '', 'author' => ''];

        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            // Update
            $bigrock['id'] = $id;
            $bigrock['title'] = $params['title'];
            $this->goalService->updateGoalboard($bigrock);
            $this->tpl->setNotification('notification.goalboard_updated_successfully', 'success', 'goalcanvas_updated');

            return Frontcontroller::redirect(BASE_URL.'/goalcanvas/bigRock/'.$id);

        } else {
            // New
            $bigrock['title'] = $params['title'];
            $bigrock['projectId'] = session('currentProject');
            $bigrock['author'] = session('userdata.id');

            $id = $this->goalService->createGoalboard($bigrock);

            if ($id) {
                $this->tpl->setNotification('notification.goalboard_created_successfully', 'success', 'wiki_created');

                return Frontcontroller::redirect(BASE_URL.'/goalcanvas/bigRock/'.$id.'?closeModal=1');
            }

            return Frontcontroller::redirect(BASE_URL.'/goalcanvas/bigRock/'.$id.'');
        }
    }
}
