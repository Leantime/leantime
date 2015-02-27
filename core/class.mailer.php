<?php

/**
 * Mail class - mails with php mail()
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package classes
 *
 *
 */

class mailer {

	/**
	 * @access public
	 * @var string
	 */
	public $cc;

	/**
	 * @access public
	 * @var string
	 */
	public $bcc;

	/**
	 * @access public
	 * @var string
	 */
	public $from='';

	/**
	 * @access public
	 * @var string
	 */
	public $text='';

	/**
	 * @access public
	 * @var string
	 */
	public $subject;

	/**
	 * __construct - get configurations
	 *
	 * @access public
	 * @return
	 */
	public function __construct(){

		$config = new config();

		$this->from = $config->email;

	}

	/** setText - sets the mailtext
	 *
	 * @access public
	 * @param $text
	 * @return
	 */
	public function setText($text){

		$this->text = $text;

	}

	/** setHTML - set Mail html (no function yet)
	 *
	 * @access public
	 * @param $html
	 * @return
	 */
	public function setHtml($html){

		$this->html = $html;

	}

	/**
	 * setSubject - set mail subject
	 *
	 * @access public
	 * @param $subject
	 * @return
	 */
	public function setSubject($subject){

		$this->subject = $subject;

	}

	/**
	 * sendMail - send the mail with mail()
	 *
	 * @access public
	 * @param array $to
	 * @return unknown_type
	 */
	public function sendMail(array $to){

		$this->text = stripslashes($this->text);

		$this->text = wordwrap($this->text, 70); //70 is in most mail-accounts standard

		$message = "".$this->text."";

		$header = "From: $this->from\n";

		$header .= "Content-Type: text/plain; charset=utf-8\n"; //textMail

		// optional Header
		$recip = '';

		//everybody gets his own mail
		//not performant with many emailaddresses
		foreach($to as $recip){

			mail(''.$recip.'', ''.$this->subject.'',''.$message.'',''.$header.'');

		}

	}

}

?>