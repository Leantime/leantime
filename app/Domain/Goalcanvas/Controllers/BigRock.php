<?php

/**
 * Controller / Edit Canvas Item
 */

namespace Leantime\Domain\Goalcanvas\Controllers {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Goalcanvas\Models\Goalcanvas;
    use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvaService;
    use Leantime\Domain\Projects\Services\Projects as ProjectService;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Symfony\Component\HttpFoundation\Response;

    class BigRock extends \Leantime\Domain\Canvas\Controllers\EditCanvasItem
    {
        private GoalcanvaService $goalService;

        /**
         * @param  TicketService  $ticketService
         * @param  ProjectService  $projectService
         */
        public function init(
            GoalcanvaService $goalService
        ): void {
            $this->goalService = $goalService;
        }

        /**
         * @throws \Exception
         */
        public function get($params): Response
        {
            if (isset($params['id'])) {

                $bigrock = $this->goalService->getSingleCanvas($params['id']);
            } else {

                $bigrock = app()->make(Goalcanvas::class);
            }

            $this->tpl->assign('bigRock', $bigrock);

            return $this->tpl->displayPartial('goalcanvas::partials.bigRockDialog');
        }

        /**
         * @throws BindingResolutionException
         */
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
                    $this->tpl->closeModal();
                    $this->tpl->htmxRefresh();

                    return $this->tpl->emptyResponse();
                }

                return Frontcontroller::redirect(BASE_URL.'/goalcanvas/bigRock/'.$id.'');
            }
        }
    }
}
