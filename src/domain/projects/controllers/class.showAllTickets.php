<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showAllTickets
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */

        public function run()
        {

            $tpl = new core\template();

            if(core\login::userIsAtLeast("clientManager")) {

                $projectRepo = new repositories\projects(); 
                $ticketRepo = new repositories\tickets(); 

                $tpl->assign('role', $_SESSION['userdata']['role']);

                if(core\login::userIsAtLeast("manager")) {

                    $allProjects = $projectRepo->getAll();
                    $tpl->assign('allProjects', $allProjects);

                    $tpl->assign('allTicketStates', $ticketRepo->getStateLabels());                    

                    $allProjectTickets = [];
                    foreach($allProjects as $project){
                        $allProjectTickets[$project['id']] = [];
                        $projectTickets = (array) $ticketRepo->getAllByProjectId($project['id']);
                        foreach($projectTickets as $ticket) {
                            $ticket = (array) $ticket;
                            if($ticket['status'] != 0) {
                                $allProjectTickets[$project['id']][$ticket['status'].'-'.$ticket['id']] = (array) $ticket;
                            }
                        }
                        ksort($allProjectTickets[$project['id']]);
                    }

                    $tpl->assign('allProjectTickets', $allProjectTickets);
        
                }else{
                    $tpl->assign('allProjects', $projectRepo->getClientProjects(core\login::getUserClientId()));
                }

                // $tpl->display('projects.showAll');
                $tpl->display('projects.showAllTickets');
            }else{

                $tpl->display('general.error');

            }

        }

    }

}
