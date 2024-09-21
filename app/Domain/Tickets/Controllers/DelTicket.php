<?php

namespace Leantime\Domain\Tickets\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Controller\Frontcontroller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Tickets\Services\Tickets as TicketService;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class DelTicket extends Controller
    {
        private TicketService $ticketService;

        /**
         * @param TicketService $ticketService
         * @return void
         */
        public function init(TicketService $ticketService): void
        {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);

            $this->ticketService = $ticketService;
        }

        /**
         * @return Response
         * @throws \Exception
         */
        public function get(): Response
        {

            //Only admins
            if (Auth::userIsAtLeast(Roles::$editor)) {



                if (isset($_GET['id'])) {
                    $id = (int)($_GET['id']);

                    try{

                        $this->ticketService->canDelete($id);

                    }catch(\Exception $e) {

                        $this->tpl->assign("error", $e->getMessage());
                        return $this->tpl->displayPartial('tickets.delTicket');
                    }

                    $this->tpl->assign("error", "");
                    $this->tpl->assign('ticket', $this->ticketService->getTicket($id));
                    return $this->tpl->displayPartial('tickets.delTicket');

                } else {
                    return $this->tpl->display('errors.error404', responseCode: 404);
                }
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }

        /**
         * @param $params
         * @return Response
         * @throws \Exception
         */
        public function post($params): Response
        {
            if (isset($_GET['id'])) {
                $id = (int)($_GET['id']);
            }

            //Only admins
            if (Auth::userIsAtLeast(Roles::$editor)) {
                if (isset($params['del'])) {
                    $result = $this->ticketService->delete($id);

                    if ($result === true) {
                        $this->tpl->setNotification($this->language->__("notification.todo_deleted"), "success");
                        return Frontcontroller::redirect(session("lastPage"));
                    } else {
                        $this->tpl->setNotification($this->language->__($result['msg']), "error");
                        $this->tpl->assign('ticket', $this->ticketService->getTicket($id));
                        return $this->tpl->displayPartial('tickets.delTicket');
                    }
                } else {
                    return $this->tpl->display('errors.error403', responseCode: 403);
                }
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }
    }
}
