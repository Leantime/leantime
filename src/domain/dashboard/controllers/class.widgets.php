<?php

namespace leantime\domain\controllers;

defined('RESTRICTED') or die('No access');

use leantime\domain\repositories;

class widgets extends repositories\dashboard
{

    public function run()
    {

        $user_id = $_SESSION['userdata']['id'];

        if (isset($_REQUEST["value"])) {
            // SET value

            $value = $_REQUEST["value"];

            $returns = $this->getWidgets($user_id); //wrong method?

            if (empty($returns)) {

                $this->addWidgetData($value, $user_id); //wrong method?

            } else {

                $this->updateWidgetData($value, $user_id); //wrong method?
            }

        } else {
            // GET value

            $returns = $this->getWidgetsValue($user_id);

            echo $returns[0];
        }
    }

}

?>
