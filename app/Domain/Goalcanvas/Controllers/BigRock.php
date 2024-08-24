<?php

/**
 * Controller / Edit Canvas Item
 */

namespace Leantime\Domain\Goalcanvas\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvaService;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class BigRock extends \Leantime\Domain\Canvas\Controllers\EditCanvasItem
    {
        protected const CANVAS_NAME = 'goal';

        private GoalcanvaRepository $canvasRepo;
        private CommentRepository $commentsRepo;
        private TicketService $ticketService;
        private ProjectService $projectService;
        private GoalcanvaService $goalService;

        /**
         * @param GoalcanvaRepository $canvasRepo
         * @param CommentRepository   $commentsRepo
         * @param TicketService       $ticketService
         * @param ProjectService      $projectService
         * @param GoalcanvaService    $goalService
         * @return void
         */
        public function init(
            GoalcanvaRepository $canvasRepo,
            CommentRepository $commentsRepo,
            TicketService $ticketService,
            ProjectService $projectService,
            GoalcanvaService $goalService
        ): void {
            $this->canvasRepo = $canvasRepo;
            $this->commentsRepo = $commentsRepo;
            $this->ticketService = $ticketService;
            $this->projectService = $projectService;
            $this->goalService = $goalService;
        }

        /**
         * @param $params
         * @return Response
         * @throws \Exception
         */
        public function get($params): Response
        {
            if (isset($params['id'])) {

                $bigrock = $this->goalService->getSingleCanvas($params['id']);

            } else {

                $bigrock = array("id"=>'', "title" => "", "prpojectId" => "", "author" => '');
            }


            $this->tpl->assign('bigRock', $bigrock);

            return $this->tpl->displayPartial('goalcanvas.bigRockDialog');
        }

        /**
         * @param $params
         * @return Response
         * @throws BindingResolutionException
         */
        public function post($params): Response
        {
            $bigrock = array("id"=>'', "title" => "", "projectId" => "", "author" => '');

            if (isset($_GET["id"])) {
                $id = (int) $_GET["id"];
                //Update
                $bigrock['id'] = $id;
                $bigrock['title'] = $params['title'];
                $this->goalService->updateGoalboard($bigrock);
                $this->tpl->setNotification("notification.goalboard_updated_successfully", "success", "goalcanvas_updated");
                return Frontcontroller::redirect(BASE_URL . "/goalcanvas/bigRock/" . $id);

            } else {
                //New
                $bigrock['title'] = $params['title'];
                $bigrock['projectId'] = session("currentProject");
                $bigrock['author'] = session("userdata.id");

                $id = $this->goalService->createGoalboard($bigrock);


                if ($id) {
                    $this->tpl->setNotification("notification.goalboard_created_successfully", "success", "wiki_created");
                    return Frontcontroller::redirect(BASE_URL . "/goalcanvas/bigRock/" . $id . "?closeModal=1");
                }

                return Frontcontroller::redirect(BASE_URL . "/goalcanvas/bigRock/" . $id . "");
            }
        }
    }
}
