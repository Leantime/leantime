<?php

/**
 * delCanvasItem class - Generic canvas controller / Delete Canvas Item
 */

namespace Leantime\Domain\Goalcanvas\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    // use Illuminate\Support\Str;
    use Symfony\Component\HttpFoundation\Response;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;  

    /**
     *
     */
    class DelCanvasItem extends Controller
    {
        /**
         * Constant that must be redefined
         */

        private GoalcanvasService $goalService;

        /**
         * init - initialize private variables
         */
        public function init(GoalcanvasService $goalService)
        {
            $this->goalService = $goalService;
        }


        public function post($params): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $id = (int)($_GET['id']);
            $this->goalService->deleteGoalCanvasItem($id);

            $this->tpl->setNotification(
                $this->language->__('notification.element_deleted'),
                'success',
                'GOALcanvasitem_deleted'
            );
            return Frontcontroller::redirect(BASE_URL . '/goalcanvas/showCanvas');
        }

        public function get($params): Response
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->tpl->assign('id', $params['id']);
            return $this->tpl->displayPartial('goalcanvas.delCanvasItem');
        }
    }
}
