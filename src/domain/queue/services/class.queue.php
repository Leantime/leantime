<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;

    class queue
    {

        private $queue;
        private $userRepo;
        private $settingsRepo;
        private $mailer;
        private $language;

        public function __construct()
        {
            // NEW Queuing messaging system
            $this->queue = new repositories\queue();

            // We need users and settings and a mailer
            $this->userRepo = new repositories\users();
            $this->settingsRepo = new \leantime\domain\repositories\setting();
            $this->mailer = new \leantime\core\mailer();
            $this->language = new \leantime\core\language();
        }

        // Fake template to be replaced by something better
        // TODO : Rework email templating system
        private function doFormatMail ($messageToSendToUser)
        {
            $outputHTML= $this->language->__('text.here_are_news')."<br/>\n";
            foreach ($messageToSendToUser as $chunk)
            {
                 $outputHTML.="<div style=\"border-top: 1px solid #ddd; margin: 3px; padding: 3px;\">";
                 $outputHTML.="<div style=\"margin: 0px; padding: 0px; float : right\">".$chunk['thedate']."</div>";
                 $outputHTML.="<div><p><em>".$chunk['subject']."</em></p>";
                 $outputHTML.=$chunk['message']."</div>";
                 $outputHTML.="</div>";
            }
            return $outputHTML;
        }

        public function processQueue()
        {


            $messages=$this->queue->listMessageInQueue('email');

            $allMessagesToSend=array();
            $n=0;
            foreach ($messages as $message) 
            {
                $n++;
                $currentUserId=$message['userId'];

                $allMessagesToSend[$currentUserId][$message['msghash']]=Array(
                    'thedate'=>$message['thedate'],
                    'subject'=> $message['subject'],
                    'message'=> $message['message'],
                    'projectId'=>$message['projectId']
                );
                // DONE here : here we need a message id to allow deleting messages of the queue when they are sent
                // and here we need to group the messages in an array to know which messages are grouped to group-delete them
                $allMessagesToDelete[$currentUserId][]=$message['msghash'];
            }

            foreach ($allMessagesToSend as $currentUserId => $messageToSendToUser)
            {
                $theuser=$this->userRepo->getUser($currentUserId);
                $recipient=$theuser['username'];

                // DONE : Deal with users parameters to allow them define a maximum (and minimum ?) frequency to receive mails
                // TODO : Update profile form to allow each user to edit his own messageFrequency option
                $lastMessageDate = strtotime($this->settingsRepo->getSetting("usersettings.".$theuser['id'].".lastMessageDate"));
                $nowDate = time();
                // echo for DEBUG PURPOSE
                debug_print( "Last message to ".$recipient." was on ".date('Y-m-d H:i:s', $lastMessageDate));
                $timeSince = abs($nowDate - $lastMessageDate);
                // echo for DEBUG PURPOSE
                debug_print("Time elapsed since : ".$timeSince);

                $messageFrequency=$this->settingsRepo->getSetting("usersettings.".$theuser['id'].".messageFrequency");
                // Check if there is a default value in DB
                if ( $messageFrequency == "" )
                {
                    $messageFrequency=$this->settingsRepo->getSetting("usersettings.default.messageFrequency");
                }
                // Last security to avoid flooding people.
                if ( $messageFrequency == "" )
                {
                    $messageFrequency=3600;
            	$this->settingsRepo->saveSetting("usersettings.default.messageFrequency", 3600);
                }
                // echo for DEBUG PURPOSE
                debug_print( "The message frequency for ".$recipient." : ".$messageFrequency);

                if ($timeSince < $messageFrequency ) 
                {
                    // echo for DEBUG PURPOSE
                    debug_print( "Elapsed time not enough for ".$recipient." : skipping till ".date("Y-m-d H:i:s", $lastMessageDate+$messageFrequency));
            	continue;
                }

                // TODO here : set up a true templating system to format the messages
                $formattedHTML=$this->doFormatMail($messageToSendToUser);

                // DONE Tranlastion needed somewhere ? 

                // DONE : Send the message with PHPMailer here
		if (count($messageToSendToUser) == 1)
                {
                    reset($messageToSendToUser);
                    $this->mailer->setSubject(current($messageToSendToUser)['subject']);

                } else
                {
                    $this->mailer->setSubject($this->language->__("email_notifications.latest_updates_subject"));
                }
                $this->mailer->setHtml($formattedHTML);
                $to = array($recipient);
                $this->mailer->sendMail($to, "Leantime System");

                // Delete the corresponding messages from the queue when the mail is sent
                // TODO here : only delete these if the send was successful
                // echo for DEBUG PURPOSE
                debug_print( "Messages send (about to delete) :");
                print_r($allMessagesToDelete[$currentUserId]);
                $this->queue->deleteMessageInQueue($allMessagesToDelete[$currentUserId]);

                // Store the last time a mail was sent to $recipient email
                $thedate=date('Y-m-d H:i:s');
                $this->settingsRepo->saveSetting("usersettings.".$theuser['id'].".lastMessageDate", $thedate);

            }

            return true;
        }

    }

}
