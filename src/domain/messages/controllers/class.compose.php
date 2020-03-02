<?php

namespace leantime\domain\controllers;

use leantime\core;
use leantime\domain\repositories;

class compose extends repositories\messages
{

    public function run()
    {

        $tpl = new core\template();

        $msg = '';

        if (isset($_POST['send'])) {
            if (isset($_POST['username'])
                && isset($_POST['subject'])
                && isset($_POST['content'])
            ) {

                $values = array(
                    'from_id' => $_SESSION['userdata']['id'],
                    'to_id' => $_POST['username'],
                    'subject' => $_POST['subject'],
                    'content' => $_POST['content']
                );

                $this->sendMessage($values);
                $msg = 'Message sent successfully.';

                $mailer = new core\mailer;

                $mailer->setSubject("You received a new message from " . $_SESSION["userdata"]["name"]);
                $actual_link = BASE_URL;
                $mailer->setHtml("" . $_SESSION["userdata"]["name"] . " send you a new message <br /><a href='" . $actual_link . "'>Click here</a> to read it.");
                $user = new repositories\users();
                $userinfo = $user->getUser($_POST['to_id']);
                var_dump($_POST['to_id']);
                var_dump($userinfo);
                if (isset($userinfo["notifications"]) && $userinfo["notifications"] != 0) {
                    $to = array($userinfo["username"]);
                    $mailer->sendMail($to, $_SESSION["userdata"]["name"]);
                }


            } else {

                $msg = 'All fields are required.';
            }
        }

        $tpl->assign('msg', $msg);
        $tpl->assign('friends', $this->getPeople());

        $tpl->display('messages.compose');

    }
}

?>
