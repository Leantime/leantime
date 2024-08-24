<?php

namespace Leantime\Domain\Queue\Workers;

use Leantime\Core\Language;
use Leantime\Core\Mailer;
use Leantime\Domain\Queue\Repositories\Queue;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Users\Repositories\Users;

class EmailWorker {


    public function __construct(
        private Users $userRepo,
        private Setting $settingsRepo,
        private Mailer $mailer,
        private Queue $queue,
        private Language $language
    ) {
    }

    public function handleQueue($messages) {


        $allMessagesToSend = array();
        $n = 0;
        foreach ($messages as $message) {
            $n++;
            $currentUserId = $message['userId'];

            //Don't send messages older than 2 weeks.
            $fromTz = new \DateTimeZone("UTC");
            $messageDate = \DateTime::createFromFormat("Y-m-d H:i:s", $message['thedate'], $fromTz);

            $today = new \DateTime(datetime:'now', timezone: $fromTz);

            if($messageDate->diff($today)->days <= 14) {

                $allMessagesToSend[$currentUserId][$message['msghash']] = array(
                    'thedate' => $message['thedate'],
                    'subject' => $message['subject'],
                    'message' => $message['message'],
                    'projectId' => $message['projectId'],
                );

            }
            // DONE here : here we need a message id to allow deleting messages of the queue when they are sent
            // and here we need to group the messages in an array to know which messages are grouped to group-delete them
            //Discard all messages
            $allMessagesToDelete[$currentUserId][] = $message['msghash'];
        }

        foreach ($allMessagesToSend as $currentUserId => $messageToSendToUser) {
            $theuser = $this->userRepo->getUser($currentUserId);

            if ($theuser === null || $theuser === false) {
                continue;
            }

            $recipient = $theuser['username'];

            // DONE : Deal with users parameters to allow them define a maximum (and minimum ?) frequency to receive mails
            $lastMessageDate = strtotime($this->settingsRepo->getSetting("usersettings." . $theuser['id'] . ".lastMessageDate"));
            $nowDate = time();

            // echo for DEBUG PURPOSE
            //debug_print("Last message to " . $recipient . " was on " . date('Y-m-d H:i:s', $lastMessageDate));
            $timeSince = abs($nowDate - $lastMessageDate);

            //Get company message frequency default
            $messageFrequency = $this->settingsRepo->getSetting("companysettings.messageFrequency");

            //Check if user has frequency set
            if (empty($messageFrequency)) {
                $messageFrequency = $this->settingsRepo->getSetting("usersettings." . $theuser['id'] . ".messageFrequency");
            }

            // Last security to avoid flooding people.
            if (empty($messageFrequency)) {
                $messageFrequency = 900;
            }
            // echo for DEBUG PURPOSE
            //debug_print("The message frequency for " . $recipient . " : " . $messageFrequency);

            if ($timeSince < $messageFrequency) {
                // echo for DEBUG PURPOSE
                //debug_print("Elapsed time not enough for " . $recipient . " : skipping till " . date("Y-m-d H:i:s", $lastMessageDate + $messageFrequency));
                continue;
            }

            // TODO here : set up a true templating system to format the messages
            $formattedHTML = $this->doFormatMail($messageToSendToUser);

            // DONE Tranlastion needed somewhere ?

            // DONE : Send the message with PHPMailer here
            $this->mailer->setContext('latest_updates');
            if (count($messageToSendToUser) == 1) {
                reset($messageToSendToUser);
                $this->mailer->setSubject(current($messageToSendToUser)['subject']);
            } else {
                $this->mailer->setSubject($this->language->__("email_notifications.latest_updates_subject"));
            }
            $this->mailer->setHtml($formattedHTML);
            $to = array($recipient);

            $this->mailer->sendMail($to, "Leantime System");

            // Delete the corresponding messages from the queue when the mail is sent
            // TODO here : only delete these if the send was successful
            // echo for DEBUG PURPOSE
            //debug_print("Messages send (about to delete) :");

            $this->queue->deleteMessageInQueue($allMessagesToDelete[$currentUserId]);

            // Store the last time a mail was sent to $recipient email
            $thedate = date('Y-m-d H:i:s');
            $this->settingsRepo->saveSetting("usersettings." . $theuser['id'] . ".lastMessageDate", $thedate);
        }
    }

    // Fake template to be replaced by something better
    // TODO : Rework email templating system
    /**
     * @param $messageToSendToUser
     * @return string
     */
    private function doFormatMail($messageToSendToUser): string
    {
        $outputHTML = $this->language->__('text.here_are_news') . "<br/>\n";
        foreach ($messageToSendToUser as $chunk) {
            $outputHTML .= "<div style=\"border-top: 1px solid #ddd; margin: 3px; padding: 3px;\">";
            $outputHTML .= "<div style=\"margin: 0px; padding: 0px; float : right\">" . $chunk['thedate'] . "</div>";
            $outputHTML .= "<div><p><em>" . $chunk['subject'] . "</em></p>";
            $outputHTML .= $chunk['message'] . "</div>";
            $outputHTML .= "</div>";
        }
        return $outputHTML;
    }

}
