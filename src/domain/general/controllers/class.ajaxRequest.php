<?php

namespace leantime\domain\controllers;

/**
 * Handles all Jax Requests
 *
 */

use leantime\core;
use leantime\domain\repositories;

class ajaxRequest
{

    /**
     * run - display template and edit data
     *
     * @access public
     */
    public function run()
    {

        $login = new core\login(core\session::getSID());

        //Check if user is logged in
        if ($login->logged_in() !== true) {

            exit();

        }


        $helper = new core\helper();
        $projects = new repositories\projects();
        $tickets = new repositories\tickets();


        $module = $_GET['module'];


        //Organize ajax handlers by module and action

        if ($module == "tickets.showAll") {


            // AJAX status change
            if (isset($_POST['ticketId'])) {

                $ticketId = $_POST['ticketId'];
                $newStatus = $_POST['newStatus'];

                if ($tickets->getAccessRights($ticketId)) {

                    if ($tickets->changeStatus($ticketId, $newStatus) === true) {

                        echo "Status was changed";

                    } else {

                        echo "Error with change";

                    }

                } else {
                    echo "You have no rights to do that.";
                }


            }

        } else if ($module == "tickets.showTicket") {

            $users = new repositories\users();

            $id = $_GET['id'];

            $results = $tickets->getTimelineHistory($id);

            $ticket = $tickets->getTicket($id);
            $jsonArr = array();

            $description = strip_tags($ticket['description']);
            $description = str_replace("\n", "", $description);
            $description = str_replace("\r", "", $description);

            $json = '{"timeline":
			    {   "headline":"Ticket History for ' . $ticket['headline'] . '",
			        "type":"default",
					"text":"' . $description . '",
					"startDate":"' . $ticket['timelineDate'] . '",
			        "date": [ ';


            //Creation Date
            $items[] = '{
					"startDate":"' . $ticket['timelineDate'] . '",
	                "headline":"Ticket Created",
	                "text":"<p>Ticket created by ' . $ticket['userFirstname'] . ', ' . $ticket['userLastname'] . '</p>",
	                "asset":
	                {  "media":"",
	                    "credit":"",
	                    "caption":""
	                }
				}';


            foreach ($results as $row) {


                $items[] = '{
					"startDate":"' . $row['date'] . '",
	                "headline":"Ticket Update",
	                "text":"<p>' . $row['firstname'] . ', ' . $row['lastname'] . ' changed ' . $row['changeType'] . ' to ' . $row['changeValue'] . '</p>",
	                "asset":
	                {  "media":"' . $users->getProfilePicture($row['userId']) . '",
	                    "credit":"' . $row['firstname'] . ', ' . $row['lastname'] . '",
	                    "caption":""
	                }
				}';
            }

            $comments = new repositories\comments();
            $allcomments = $comments->getComments('ticket', $id);

            foreach ($allcomments as $comment) {


                $items[] = '{
					"startDate":"' . $comment['timelineDate'] . '",
	                "headline":"New Comment",
	                "text":' . json_encode('<p>' . $comment['firstname'] . ', ' . $comment['lastname'] . ' said:<br /> </p>' . $comment['text']) . ',
	                "asset":
	                {	"media":"' . $users->getProfilePicture($comment['userId']) . '",
	                    "credit":"' . $comment['firstname'] . ', ' . $comment['lastname'] . '",
	                    "caption":""
	                }
				}';
            }

            $file = new repositories\files();
            $files = $file->getFilesByModule('ticket', $id);
            $tempStr = '';
            $tempStr3 = '';
            $imgExtensions = array('jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv');

            foreach ($files as $fileRow) {

                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/userdata/' . $fileRow['module'] . '/' . $fileRow['encName'] . '.' . $fileRow['extension'])) {
                    $tempStr3 .= "<img style='max-height: 50px; max-width: 70px;' src='userdata/" . $fileRow["module"] . "/" . $fileRow['encName'] . "." . $fileRow["extension"] . "' />";
                    $filepath = "userdata/" . $fileRow["module"] . "/" . $fileRow['encName'] . "." . $fileRow["extension"] . "";


                } else {
                    $tempStr3 .= "<img style='max-height: 50px; max-width: 70px;' src='userdata/file.png' />";
                    $filepath = "userdata/file.png";
                }

                $tempStr = '{
					"startDate":"' . $fileRow['timelineDate'] . '",
	                "headline":"New File",
	                "text":"' . $fileRow['firstname'] . ', ' . $fileRow['lastname'] . ' uploaded:<br /><a href=\'' . $filepath . '\'>' . $fileRow['realName'] . '.' . $fileRow['extension'] . '</a>",';


                $tempStr3 .= '<span class=\'filename\'>' . $fileRow['realName'] . '.' . $fileRow['extension'] . '</span>
                        </a>",';


                $tempStr .= '
	               "asset":
	                {	"media":"' . $filepath . '",
	                    "credit":"' . $fileRow['realName'] . '.' . $fileRow['extension'] . '",
	                    "caption":""
	                }
				}';

                $items[] = $tempStr;

            }

            $json .= implode(",", $items);

            $json .= '	
					]
    				}
				}';
            header('Content-type: text/json');
            header('Content-type: application/json');
            echo $json;

        } else {

            echo "There are no ajax actions for this module";

        }


    }

}

?>
