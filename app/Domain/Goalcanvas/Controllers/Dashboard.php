<?php

/**
 * Controller
 */

namespace Leantime\Domain\Goalcanvas\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Core\Mailer;
    use Leantime\Domain\Canvas\Repositories\Canvas;
    use Leantime\Domain\Canvas\Services\Canvas as CanvasService;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas;
    use Leantime\Domain\Projects\Services\Projects;
    use Leantime\Domain\Queue\Repositories\Queue as QueueRepo;
    use Illuminate\Support\Str;
    use Leantime\Core\Frontcontroller;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class Dashboard extends Controller
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'goal';

        private Projects $projectService;
        private Goalcanvas $goalService;
        private object $canvasRepo;

        /**
         * init - initialize private variables
         */
        public function init(
            Projects $projectService,
            Goalcanvas $goalService
        ) {
            $this->projectService = $projectService;
            $this->goalService = $goalService;
            $repoName = app()->getNamespace() . "Domain\\goalcanvas\\Repositories\\goalcanvas";
            $this->canvasRepo = app()->make($repoName);
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */

         public function get($params): Response
         {

             $result = $this->goalService->handleDashboardGetRequest($params);
     
             $this->tpl->assign('currentCanvas', $result['currentCanvasId']);
             $this->tpl->assign('goalStats', $result['goalAnalytics']);
             $this->tpl->assign('canvasIcon', $result['canvasIcon']);
             $this->tpl->assign('canvasTypes', $result['canvasTypes']);
             $this->tpl->assign('statusLabels', $result['statusLabels']);
             $this->tpl->assign('relatesLabels', $result['relatesLabels']);
             $this->tpl->assign('dataLabels', $result['dataLabels']);
             $this->tpl->assign('disclaimer', $result['disclaimer']);
             $this->tpl->assign('allCanvas', $result['allCanvas']);
             $this->tpl->assign('canvasItems', $result['canvasItems']);
             $this->tpl->assign('users', $result['users']);
     
             return $this->tpl->display(static::CANVAS_NAME . 'canvas.dashboard');
         }


         public function post($params): Response
         {
             $result = $this->goalService->handleDashboardPostRequest($params);
     
             if ($result['redirect']) {
                 return Frontcontroller::redirect($result['redirectUrl']);
             }
     
             if ($result['notification']) {
                 $this->tpl->setNotification($result['notification']['message'], $result['notification']['type']);
             }
     
             return $this->tpl->display(static::CANVAS_NAME . 'canvas.dashboard');
         }
    }
}
