<?php


namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class showAll
    {

        public function run()
        {

            $tpl = new core\template();
            $messageRepo = new repositories\messages();

            // Messages
            $msg = '';
            $id = null;
            $mailer = new core\mailer();

            // Compose
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

                    $messageRepo->sendMessage($values);
                    $tpl->setNotification('MESSAGE_SENT', 'success');

                    $mailer->setSubject("You received a new message from " . $_SESSION["userdata"]["name"]);
                    $actual_link = BASE_URL;
                    $mailer->setHtml("" . $_SESSION["userdata"]["name"] . " send you a new message <br /><a href='" . $actual_link . "'>Click here</a> to read it.");
                    $user = new repositories\users();
                    $userinfo = $user->getUser($_POST['username']);

                    if (isset($userinfo["notifications"]) && $userinfo["notifications"] != 0) {
                        $to = array($userinfo["username"]);
                        $mailer->sendMail($to, $_SESSION["userdata"]["name"]);
                    }

                } else {

                    $tpl->setNotification('MISSING_FIELDS', 'error');
                }
            }

            if (isset($_POST['reply'])) {
                if (isset($_POST['message'])) {
                    $values = array(
                        'content' => $_POST['message'],
                        'to_id' => $_POST['to_id'],
                        'from_id' => $_SESSION['userdata']['id']
                    );

                    $messageRepo->reply($values, $_POST['parent_id']);


                    $mailer->setSubject("You received a new message from " . $_SESSION["userdata"]["name"]);
                    $actual_link = BASE_URL;
                    $mailer->setHtml("" . $_SESSION["userdata"]["name"] . " send you a new message <br /><a href='" . $actual_link . "'>Click here</a> to read it.");
                    $user = new repositories\users();
                    $userinfo = $user->getUser($_POST['to_id']);

                    if (isset($userinfo["notifications"]) && $userinfo["notifications"] != 0) {
                        $to = array($userinfo["username"]);
                        $mailer->sendMail($to, $_SESSION["userdata"]["name"]);
                    }

                }
            }

            $myMessages = $messageRepo->getMessages($_SESSION['userdata']['id']);

            $users = new repositories\users();
            $user = $users->getUser($_SESSION['userdata']['id']);

            if (!isset($_GET['id'])) {
                $messages = $messageRepo->getMessages($_SESSION['userdata']['id'], 1);
                foreach ($messages as $message) {
                    $id = $message['id'];
                }
            } else {
                $id = $_GET['id'];
                $messageRepo->markAsRead($id);
            }

            $tpl->assign('info', $msg);
            $tpl->assign('displayId', $id);
            $tpl->assign('userEmail', $user['username']);
            $tpl->assign('messages', $myMessages);
            $tpl->assign('friends', $messageRepo->getPeople());

            $tpl->display('messages.showAll');

        }

    }

}
