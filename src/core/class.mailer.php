<?php

/**
 * Mail class - mails with php mail()
 *
 * @author  Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license GNU/GPL, see license.txt
 */
namespace leantime\core {

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    class mailer
    {

        /**
         * @access public
         * @var    string
         */
        public $cc;

        /**
         * @access public
         * @var    string
         */
        public $bcc;

        /**
         * @access public
         * @var    string
         */
        public $from = '';

        /**
         * @access public
         * @var    string
         */
        public $text = '';

        /**
         * @access public
         * @var    string
         */
        public $subject;


        private $mailAgent;

        private $emailDomain;

        private $language;

        /**
         * __construct - get configurations
         *
         * @access public
         * @return void
         */
        public function __construct()
        {

            $config = new config();

            $this->from = $config->email;

            //PHPMailer
            $this->mailAgent = new PHPMailer();


            //Use SMTP or php mail().
            if($config->useSMTP === true) {

                $this->mailAgent->SMTPDebug = 0;                                  // Enable verbose debug output
                $this->mailAgent->Timeout = 20;

                $this->mailAgent->isSMTP();                                      // Set mailer to use SMTP
                $this->mailAgent->Host = $config->smtpHosts;          // Specify main and backup SMTP servers
                $this->mailAgent->SMTPAuth = true;                               // Enable SMTP authentication
                $this->mailAgent->Username = $config->smtpUsername;                 // SMTP username
                $this->mailAgent->Password = $config->smtpPassword;                           // SMTP password
                $this->mailAgent->SMTPAutoTLS = $config->smtpAutoTLS ?? true;                 // Enable TLS encryption automatically if a server supports it
                $this->mailAgent->SMTPSecure = $config->smtpSecure;                            // Enable TLS encryption, `ssl` also accepted
                $this->mailAgent->Port = $config->smtpPort;                                    // TCP port to connect to
                $this->mailAgent->CharSet = 'UTF-8';                    //Ensure UTF-8 is used for emails

            }else{

                $this->mailAgent->isMail();

            }

            $this->emailDomain = $config->email;

            $this->logo = $_SESSION["companysettings.logoPath"];
            $this->companyColor = $_SESSION["companysettings.mainColor"];

            $this->language = new language();

        }

        /**
         *
         * setText - sets the mailtext
         *
         * @access public
         * @param  $text
         * @return void
         */
        public function setText($text)
        {

            $this->text = $text;

        }

        /**
         *
         * setHTML - set Mail html (no function yet)
         *
         * @access public
         * @param  $html
         * @return void
         */
        public function setHtml($html)
        {

            $this->html = $html;

        }

        /**
         * setSubject - set mail subject
         *
         * @access public
         * @param  $subject
         * @return void
         */
        public function setSubject($subject)
        {

            $this->subject = $subject;

        }

        /**
         * sendMail - send the mail with mail()
         *
         * @access public
         * @param  array $to
         * @param  $from
         * @return void
         * @throws \phpmailerException
         */
        public function sendMail(array $to, $from)
        {


            $this->mailAgent->isHTML(true);                                  // Set email format to HTML

            $this->mailAgent->setFrom($this->emailDomain, $from . " (Leantime)");

            $this->mailAgent->Subject = $this->subject;

            $logoParts = parse_url($this->logo);

            if(isset($logoParts['scheme'])) {
                //Logo is URL
                $inlineLogoContent = $this->logo;
            }else{
                //Logo comes from local file system
                $this->mailAgent->addEmbeddedImage(ROOT."".$this->logo, 'companylogo');
                $inlineLogoContent = "cid:companylogo";
            }

            $bodyTemplate = '
		<table width="100%" style="background:#eeeeee; padding:15px; ">
		<tr>
			<td align="center" valign="top">
				<table width="600"  style="width:600px; background-color:#ffffff; border:1px solid #ccc;">
					<tr>
						<td style="padding:3px 10px; background-color:#' . $this->companyColor . '">
							<table>
								<tr>
								<td width="150"><img alt="Logo" src="'.$inlineLogoContent. '" width="150" style="width:150px;"></td>
								<td></td>
							</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="padding:10px; font-family:Arial; color:#666; font-size:14px; line-height:1.7;">
							'.$this->language->__('email_notifications.hi').'
							<br /><br />
							' . nl2br($this->html) . '
							<br /><br />
						</td>
					</tr>
				</table>
				
			</td>
		</tr>
		<tr>
			<td align="center">
			'.sprintf($this->language->__('email_notifications.unsubscribe'), BASE_URL.'/users/editOwn/').'
			</td>
		</tr>
		</table>';

            $this->mailAgent->Body = $bodyTemplate;
            $this->mailAgent->AltBody = $this->text;

            $to = array_unique($to);

            foreach ($to as $recip) {
                $this->mailAgent->addAddress($recip);
                $this->mailAgent->send();
                $this->mailAgent->clearAllRecipients();
            }


        }

    }

}
