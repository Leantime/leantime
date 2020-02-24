<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;

    class tickets
    {

        private $projectRepository;
        private $sprintRepository;
        private $ticketRepository;

        public function __construct()
        {

            $this->tpl = new core\template();
            $this->projectRepository = new repositories\projects();
            $this->ticketRepository = new repositories\tickets();
        }



        //GET
        public function getTicket($id)
        {

            $ticket = (object) $this->ticketRepository->getTicket($id);

            if($ticket) {

                $ticket->date = date('m/d/Y', strtotime($ticket->date));
                $ticket->editFrom = date('m/d/Y', strtotime($ticket->editFrom));
                $ticket->editTo = date('m/d/Y', strtotime($ticket->editTo));

                return $ticket;
            }

            return $ticket;
        }

        public function getOpenUserTicketsThisWeekAndLater ($userId, $projectId) {

            $searchCriteria = array("currentProject" => $projectId, "users" => $userId, "status" => "not_done", "searchType"=> "", "searchterm" => "", "sprint" => "", "milestone" => '');
            $allTickets = $this->ticketRepository->getAllBySearchCriteria($searchCriteria);

            $tickets = array(
                "thisWeek" => array(),
                "later" => array()
            );

            foreach($allTickets as $row){

                if($row['dateToFinish'] == "0000-00-00 00:00:00" || $row['dateToFinish'] == "1969-12-31 00:00:00") {
                    $tickets["later"][] = $row;
                }else {
                    $date = new \DateTime($row['dateToFinish']);

                    $nextFriday = strtotime('friday this week');
                    $nextFridayDateTime = new \DateTime();
                    $nextFridayDateTime->setTimestamp($nextFriday);
                    if($date <= $nextFridayDateTime){
                        $tickets["thisWeek"][] = $row;
                    }else{
                        $tickets["later"][] = $row;
                    }
                }


            }

            return $tickets;

        }

        public function getAllMilestones($projectId)
        {

            if($projectId > 0) {
                return $this->ticketRepository->getAllMilestones($projectId);
            }

            return false;

        }

        //Add
        public function quickAddTicket($params)
        {

            $values = array(
                'headline' => $params['headline'],
                'type' => 'Task',
                'description' => isset($params['description']) ? $params['description'] : '',
                'projectId' => $_SESSION['currentProject'],
                'editorId' => $_SESSION['userdata']['id'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => date("Y-m-d H:i:s"),
                'dateToFinish' => isset($params['dateToFinish']) ? strip_tags($params['dateToFinish']) : "",
                'status' => isset($params['status']) ? (int) $params['status'] : "",
                'storypoints' => '',
                'hourRemaining' => '',
                'planHours' => '',
                'sprint' => isset($params['sprint']) ? (int) $params['sprint'] : "",
                'acceptanceCriteria' => '',
                'tags' => '',
                'editFrom' => '',
                'editTo' => '',
                'dependingTicketId' => isset($params['milestone']) ? (int) $params['milestone'] : ""
            );

            if($values['headline'] == "") {
                $error = array("status"=>"error", "message"=>"Headline Missing");
                return $error;
            }

            //$params is an array of field names. Exclude id
            return $this->ticketRepository->addTicket($values);

        }

        public function quickAddMilestone($params)
        {

            $values = array(
                'headline' => $params['headline'],
                'type' => 'milestone',
                'description' => '',
                'projectId' => $_SESSION['currentProject'],
                'editorId' => $_SESSION['userdata']['id'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => date("Y-m-d H:i:s"),
                'dateToFinish' => "",
                'status' => "new",
                'storypoints' => '',
                'hourRemaining' => '',
                'planHours' => '',
                'sprint' => '',
                'dependingTicketId' =>$params['dependentMilestone'],
                'acceptanceCriteria' => '',
                'tags' => $params['tags'],
                'editFrom' => date('Y-m-d 00:00:01', strtotime($params['editFrom'])),
                'editTo' => date('Y-m-d 00:00:01', strtotime($params['editTo']))
            );


            if($values['headline'] == "") {
                $error = array("status"=>"error", "message"=>"Headline Missing");
                return $error;
            }

            //$params is an array of field names. Exclude id
            return $this->ticketRepository->addTicket($values);

        }

        //Update
        public function patchTicket($id, $params)
        {

            //$params is an array of field names. Exclude id
            unset($params["id"]);

            return $this->ticketRepository->patchTicket($id, $params);

        }

        public function quickUpdateMilestone($params)
        {

            $values = array(
                'headline' => $params['headline'],
                'type' => 'milestone',
                'description' => '',
                'projectId' => $_SESSION['currentProject'],
                'editorId' => $_SESSION['userdata']['id'],
                'userId' => $_SESSION['userdata']['id'],
                'date' => date("Y-m-d H:i:s"),
                'dateToFinish' => "",
                'status' => "new",
                'storypoints' => '',
                'hourRemaining' => '',
                'planHours' => '',
                'sprint' => '',
                'acceptanceCriteria' => '',
                'dependingTicketId' => $params['dependentMilestone'],
                'tags' => $params['tags'],
                'editFrom' => date('Y-m-d 00:00:01', strtotime($params['editFrom'])),
                'editTo' => date('Y-m-d 23:59:59', strtotime($params['editTo']))
            );

            if($values['headline'] == "") {
                $error = array("status"=>"error", "message"=>"Headline Missing");
                return $error;
            }

            //$params is an array of field names. Exclude id
            return $this->ticketRepository->updateTicket($values, $params["id"]);

        }


    }

}
