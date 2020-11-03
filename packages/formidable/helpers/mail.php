<?php   
/**
 * @package Helpers
 * @category Formidable
 * @author Wim Bouter <wim@dewebmakers.nl>
 */ 
defined('C5_EXECUTE') or die("Access Denied.");

class MailHelper extends Concrete5_Helper_Mail {

	protected $attachments = array();	
	
	public function reset() {
		parent::reset();
		$this->replyto = array();
		$this->attachments = array();
	}
		
	/** 
	 * Adds a attachment to a mail
	 * @param string $filename
	 * @param string $name
	 * @param string $mime
	 * @return void
	 */
	public function addAttachment($file, $name, $mime) {
		$this->attachments[] = array($file, $name, $mime);
	}
				
	public function sendMail($resetData = true) {
		$_from[] = $this->from;
		$fromStr = $this->generateEmailStrings($_from);
		$toStr = $this->generateEmailStrings($this->to);
		
		if (!empty($this->replyto))
			$replyStr = $this->generateEmailStrings($this->replyto);
		if (!empty($this->cc))
			$ccStr = $this->generateEmailStrings($this->cc);
		if (!empty($this->bcc))
			$bccStr = $this->generateEmailStrings($this->bcc);
		
		if (ENABLE_EMAILS) {

			$zendMailData = self::getMailerObject();
			$mail=$zendMailData['mail'];
			$transport=(isset($zendMailData['transport']))?$zendMailData['transport']:NULL;
			
			if (is_array($this->from) && count($this->from)) {
				if ($this->from[0] != '') {
					$from = $this->from;
				}
			}
			if (!isset($from)) {
				$from = array(EMAIL_DEFAULT_FROM_ADDRESS, EMAIL_DEFAULT_FROM_NAME);
				$fromStr = EMAIL_DEFAULT_FROM_ADDRESS;
			}
			if(is_array($this->replyto)) {
				foreach ($this->replyto as $reply) {
					if (!empty($reply[0]))
						$mail->setReplyTo($reply[0], $reply[1]);
				}
			}
			$mail->clearRecipients();
			

			$mail->setFrom($from[0], $from[1]);
			$mail->setSubject($this->subject);
			foreach($this->to as $to) {
				if (!empty($to[0]))
					$mail->addTo($to[0], $to[1]);
			}
			
			if(is_array($this->cc) && count($this->cc)) {
				foreach($this->cc as $cc) {
					if (!empty($cc[0]))
						$mail->addCc($cc[0], $cc[1]);
				}
			}
			
			if(is_array($this->bcc) && count($this->bcc)) {
				foreach($this->bcc as $bcc) {
					if (!empty($bcc[0]))
						$mail->addBcc($bcc[0], $bcc[1]);
				}
			}
			
			$mail->setBodyText($this->body);
			if ($this->bodyHTML != false) {
				$mail->setBodyHTML($this->bodyHTML);
			}
			
			if(is_array($this->attachments) && count($this->attachments)) {
				foreach($this->attachments as $att) {
					$mail->createAttachment(@file_get_contents($att[0]), $att[2], 'attachment', 'base64', $att[1]);
				}
			}
			
			try {
				$mail->send($transport);
					
			} catch(Exception $e) {
				$l = new Log(LOG_TYPE_EXCEPTIONS, true, true);
				$l->write(t('Mail Exception Occurred. Unable to send mail: ') . $e->getMessage());
				$l->write($e->getTraceAsString());
				if (ENABLE_LOG_EMAILS) {
					$l->write(t('Template Used') . ': ' . $this->template);
					$l->write(t('To') . ': ' . $toStr);
					$l->write(t('From') . ': ' . $fromStr);
					if (!empty($this->cc))
						$l->write(t('CC') . ': ' . $ccStr);	
					if (!empty($this->bcc))
						$l->write(t('BCC') . ': ' . $bccStr);						
					if (!empty($this->replyto)) 
						$l->write(t('Reply-To') . ': ' . $replyStr);					
					$l->write(t('Subject') . ': ' . $this->subject);
					$l->write(t('Body') . ': ' . $this->body);
				}				
				$l->close();
			}
		}	
		
		// add email to log
		if (ENABLE_LOG_EMAILS) {
			$l = new Log(LOG_TYPE_EMAILS, true, true);
			if (ENABLE_EMAILS) {
				$l->write('**' . t('EMAILS ARE ENABLED. THIS EMAIL WAS SENT TO mail()') . '**');
			} else {
				$l->write('**' . t('EMAILS ARE DISABLED. THIS EMAIL WAS LOGGED BUT NOT SENT') . '**');
			}
			$l->write(t('Template Used') . ': ' . $this->template);
			$l->write(t('To') . ': ' . $toStr);
			$l->write(t('From') . ': ' . $fromStr);
			if (!empty($this->cc))
				$l->write(t('CC') . ': ' . $ccStr);	
			if (!empty($this->bcc))
				$l->write(t('BCC') . ': ' . $bccStr);						
			if (!empty($this->replyto)) 
				$l->write(t('Reply-To') . ': ' . $replyStr);
			$l->write(t('Subject') . ': ' . $this->subject);
			$l->write(t('Body') . ': ' . $this->body);
			$l->close();
		}			
		
		// clear data if applicable
		if ($resetData) 
			$this->reset();
	}
	
}

?>