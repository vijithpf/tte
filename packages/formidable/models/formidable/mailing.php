<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable', 'formidable');

class FormidableMailing extends Formidable {	
	
	private $send_type = 'bcc';
	
	private $elements = array();
	private $new_elements = array();
	
	public function __construct($mailingID = 0, $elements = array())
	{		
		if (!empty($elements))
			$this->elements = $elements;
									
		if (intval($mailingID) != 0)
			$this->getById($mailingID);		
	}
	
	public function getID() {
		return $this->mailingID;
	}
			
	private function getById($mailingID) 
	{
		if(!is_numeric($mailingID) || intval($mailingID) == 0) 
			return false;
		
		$db = Loader::db();
					
		$mailing = $db->getRow("SELECT * 
							    FROM FormidableFormMailings
							    WHERE mailingID = ?", array($mailingID));			
		if (!$mailing)
			return false;
		
		$this->setAttributes(array_filter($mailing));
		
		$this->from();
		
		$this->send = @explode(',', $this->send);		
		
		if ($this->send_cc)
			$this->send_type = 'cc';
		
		$editor = Loader::helper('editor', 'formidable');
		$this->message = $editor->translateFromEditMode($this->message);	
		
		$this->attachments = @explode(',', $this->attachments);
		
		if (intval($this->attachments_element) == 1)
			$this->attachments_element_value = @explode(',', $this->attachments_element_value);
		
		if (intval($this->template) == 1 && intval($this->templateID) != 0) {
			Loader::model('formidable/template', 'formidable');
			$template = new FormidableTemplate($this->templateID);
			if ($template->templateID)
				$this->template = $template;
		}
	}	
	
	public function save($data)
	{
		if (!$this->mailingID)	
			$this->add($data);
		else
			$this->update($data);	 
	}
			
	
	private function add($data)
	{									
		$db = Loader::db();
			
		$q = "INSERT INTO FormidableFormMailings (`".@implode('`,`', array_keys($data))."`) 
			  VALUES (".str_repeat('?,', sizeof($data)-1)."?)";
		
		$db->query($q, $data);
		$this->mailingID = $db->Insert_ID();		
	}
	
	private function update($data)
	{					
		$db = Loader::db();
		
		foreach ($data as $key => $value) {
			$update_string[] = '`'.$key.'` = ?';
			$update_data[] = $value;
		}
			
		$q = "UPDATE FormidableFormMailings SET ".@implode(', ', $update_string)."
			  WHERE mailingID = '".$this->mailingID."'";
		
		$db->query($q, $update_data);
	}
	
	public function update_element_handle($old_handle, $new_handle)
	{
		$editor = Loader::helper('editor', 'formidable');
		
		if (!empty($old_handle) && !empty($new_handle))
		{
			$pattern = array('/{%'.$old_handle.'.label%}/', '/{%'.$old_handle.'.value%}/');
			$replace = array('{%'.$new_handle.'.label%}', '{%'.$new_handle.'.value%}');
			
			$message = preg_replace($pattern, $replace, $this->message);
			
			$this->update(array('message' => $editor->translateTo($message)));
		}
	}
	
	public function duplicate($formID = 0, $new_elements = array())
	{
		$_params_m = get_object_vars($this);
		
		if (sizeof($this->send) > 0)
			$_params_m['send'] = @implode(',', $this->send);		

		if (sizeof($this->attachments) > 0)
			$_params_m['attachments'] = @implode(',', $this->attachments);
		
		if (intval($this->attachments_element) == 1)
			$_params_m['attachments_element_value'] = @implode(',', $this->attachments_element_value);
			
		$editor = Loader::helper('editor', 'formidable');
		$_params_m['message'] = $editor->translateTo($this->message);	
		
		// Set new formID and replace new elementIDs....
		if (intval($formID) != 0) 
		{
			$_params_m['formID'] = $formID;
			
			$this->new_elements = $new_elements;
			
			$_params_m['send'] = preg_replace_callback('/([0-9])/', array(&$this, '_replaceSingleElementID'), $_params_m['send']);
			$_params_m['from_type'] = preg_replace_callback('/([0-9])/', array(&$this, '_replaceSingleElementID'), $_params_m['from_type']);
			$_params_m['reply_type'] = preg_replace_callback('/([0-9])/', array(&$this, '_replaceSingleElementID'), $_params_m['reply_type']);					
			$_params_m['message'] = preg_replace_callback('/({%.*)_([0-9])(.*%})/', array(&$this, '_replaceMessageElementID'), $_params_m['message']);
			$_params_m['attachments_element_value'] = preg_replace_callback('/([0-9]+)/', array(&$this, '_replaceStringElementID'), $_params_m['attachments_element_value']);		
		}
		else 		
			$_params_m['subject'] .= ' ('.t('copy').')';	
		
		$unset = array('mailingID','html5','properties','from','new_elements','elements','results','send_type','javascript','jquery','css');
		foreach ($unset as $u) {
			unset($_params_m[$u]);
		}
		
		$nfm = new FormidableMailing();			
		$nfm->add($_params_m);
		
		return $nfm;
	}
	
	public function delete()
	{
		$db = Loader::db();
		
		$db->query("DELETE FROM FormidableFormMailings 
				    WHERE mailingID = ?
				    AND formID = ?", array($this->mailingID, $this->formID));
		
		return true;
	}
		
	public function validateProperties() 
	{
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidatorProperty();
		
		$validator->from(array('type' => $this->request('from_type'), 'name' => $this->request('from_name'), 'email' => $this->request('from_email')),
						 array('type' => $this->request('reply_type'), 'name' => $this->request('reply_name'), 'email' => $this->request('reply_email')));
		
		$validator->send_to($this->request('send'), $this->request('send_custom'), $this->request('send_custom_value'));
		
		$validator->subject($this->request('subject'));
		
		$validator->message($this->request('message'));
		
		return $validator->getList();		
	}
	
	public function setAbsoluteURLs($text){ 
	
		$prefix = BASE_URL; 
		
		$text = str_ireplace(array(' href=" http',' src=" http'),array(' href="http',' src="http'),$text);
		 
		// replace relative urls by absolute (prefix them with $prefix)
		$pattern = '/href=[\'|"](?!http|https|ftp|irc|feed|mailto|#)([\/]?)([^\'|"]*)[\'|"]/i';
		$replace = 'href="'.$prefix.'/$2"';
		$text = preg_replace($pattern, $replace, $text); 
		 
		// replace relative img urls by absolute (prefix them with $prefix)
		$pattern = '/src=[\'|"](?!http|https|ftp|irc|feed|mailto|#)([\/]?)([^\'|"]*)[\'|"]/i';
		$replace = 'src="'.$prefix.'/$2"';
		$text = preg_replace($pattern, $replace, $text); 		
		
		return $text; 
	}	
	
	public function from() 
	{
		$this->from = t('Unknown');
				
		if ($this->from_type == 'other')
			$this->from = $this->from_name.' ('.$this->from_email.')';	
		else
			if (is_object($this->elements[$this->from_type]))
				$this->from = $this->elements[$this->from_type]->label.' ('.$this->elements[$this->from_type]->element_text.')';
	}
		
	public function send()
	{					
		Loader::model('file_version');

		$th = Loader::helper('text');
		$mh = Loader::helper('mail', 'formidable');										
				
		// Set Subject
		$_subject = $this->prepareSubject();
		$mh->setSubject($th->sanitize($_subject));
								
		// Set From 
		if (intval($this->from_type) != 0) {
			if (!empty($this->elements[$this->from_type]->result)) {
				$this->from_name = '';
				$this->from_email = $this->elements[$this->from_type]->result;
			}
		}				
		$mh->from($this->from_email, $this->from_name);
				
		// Set Reply To 
		if ($this->reply_type == 'from') {
			$this->reply_name = $this->from_name;
			$this->reply_email = $this->from_email;
		} elseif (intval($this->reply_type) != 0) {
			if (!empty($this->elements[$this->from_type]->result)) {
				$this->reply_name = '';
				$this->reply_email = $this->elements[$this->from_type]->result;
			}
		}
		$mh->replyto($this->reply_email?$this->reply_email:$this->from_email, $this->reply_name);
				
		// Set To / CC 			
		$_send_to = array();
		if (sizeof($this->send) > 0) { 
			foreach ($this->send as $_send) { 
				$_element = $this->elements[$_send];
				if ($_element->properties['options']) {
					$_options = unserialize($_element->options);					
					if (sizeof($_options) > 0) {
						$_values = array();
						foreach((array)$_element->value as $_value) {
							if (is_array($_value)) {
								$_values[] = $_value[0];	
							} else {
								$_values[] = $_value;	
							}
						}
						foreach ($_options as $_option) {
							if (is_array($_option))								
								if (in_array($_option['name'], $_values))
									$_send_to[] = array($_option['value'], $_option['name']);
							else
								if (in_array($_option, $_values))
									$_send_to[] = $_option;
						}
					}							
				}
				else $_send_to[] = $_element->result;
			}
		}
		if (intval($this->send_custom) != 0)
			$_send_to = @array_filter(@array_merge((array)$_send_to, (array)@explode(',', $this->send_custom_value)));
		
		if (sizeof($_send_to) > 0) {
			$first = true;
			foreach ($_send_to as $value) {
				$to_name = '';
				$to_mail = $value;
				if (@is_array($to_mail)) {							
					$to_mail = $value[0];
					$to_name = $value[1];
				}
				if (!empty($to_mail)) {
					if ($first) 
						$mh->to(trim($to_mail), trim($to_name));	
					else 
						$mh->{$this->send_type}(trim($to_mail), trim($to_name));
				}
				$first = false;
			}
		}
				
		// Set Message
		$_message = $this->prepareMessage();

		$mh->setBodyHTML($_message);		
		$mh->setBody($th->sanitize($_message));				
								
		// Set attachments
		$_attachments = array();
		if (!empty($this->attachments)) 
			$_attachments += $this->attachments;
		
		$files_to_mail = $this->files_to_mail;		
		if ($this->attachments_element == 1 && !empty($files_to_mail))
			foreach ((array)$this->attachments_element_value as $_element)					
				foreach ((array)$files_to_mail[$_element] as $_file) 
					$_attachments[] = $_file['file_id'];
									
		if (sizeof($_attachments) > 0) {
			foreach ($_attachments as $_attachment) {
				if (empty($_attachment))
					continue;
										
				$f = File::getByID(intval($_attachment));
				if (intval($f->getFileID()) != 0) {
					$fv = $f->getApprovedVersion();											
					$mh->addAttachment($fv->getPath(), $fv->getFileName(), $fv->getMimeType());					
				}
			}
		}	

		// Send the mail!				
		$mh->sendMail(true);
		$mh->reset();
						
		return true;
	}
	
	private function prepareSubject($format = '')
	{
		return $this->prepareContent($this->subject, $format);
	}

	private function prepareMessage($format = '')
	{
		$content = $this->prepareContent($this->message, $format);
		if (is_object($this->template))
			$content = str_replace('{%FORMIDABLE_MAILING%}', $content, $this->template->template);

		return $content;
	}

	private function prepareContent($content, $format = '')
	{				
		$_format = '%s: %s <br />';
		if ($format != '')
			$_format = $format;
				
		$eh = Loader::helper('editor', 'formidable');
		
		$_message = $eh->translateFrom($content);		
		$_message = $this->setAbsoluteURLs($_message);
		
		// Convert all advanced elements in message				
		foreach ($this->advanced as $advanced) 
		{			
			$labels[] = '/{%'.$advanced['handle'].'.label%}/';
			$values[] = preg_quote($advanced['label']);	
									
			$labels[] = '/{%'.$advanced['handle'].'.value%}/';						
			$values[] = preg_quote($advanced['value']);
			
			$all_advanced_elements .= sprintf($_format, preg_quote($advanced['label']), preg_quote($advanced['value']));							
			
		}
		$labels[] = '/{%all_advanced_data%}/';
		$values[] = $all_advanced_elements;
				
		// Convert all form elements in message									
		if (sizeof($this->elements) > 0)  {
			foreach ($this->elements as $element) {
							
				$show = true;
				foreach((array)$element->dependency_validation as $dependency_rule) {						
										
					if (!empty($dependency_rule)) {
						$show = false;
						foreach((array)$dependency_rule as $dependency) {						
							$_element = $this->elements[$this->getElementIdByHandle($dependency['element'])];							
							if (!empty($dependency['value'])) {								
								if (count(array_intersect((array)$dependency['value'], (array)$_element->value)))								
									$show = true;
							} else {
								if (!empty($_value))
									$show = true;
							}
							if ($dependency['inverse']) {
								if ($show) 
									$show = false;
								else
									$show = true;
							}
						}
					}
				}
				
				if (!$show || (trim($element->result) == '' && $this->discard_empty))
					continue;
					
				$labels[] = '/{%'.$element->handle.'.label%}/';				
				if ($element->is_layout)
					$values[] = preg_quote($element->input);	
				else
					$values[] = preg_quote($element->label);
					
				$labels[] = '/{%'.$element->handle.'.value%}/';					
				$values[] = preg_quote($element->result);												
					
				if ($element->is_layout) {
					if (!$this->discard_layout)
						$all_elements .= preg_quote($element->input);
				} else
					$all_elements .= sprintf($_format, $element->label, preg_quote($element->result));
			}
			
			// Add all elements labels
			$labels[] = '/{%all_elements%}/';
			$values[] = $all_elements;
		}
		
		// Remove empty labels / values
		$labels[] = '/{%(.*)%}(|:)/';
		$values[] = '';
		
		$labels[] = "/<[^\/>]*>([\s]?)*<\/[^>]*>/";	
		$values[] = '';			
		
		// Remove empty tags
		$_message = preg_replace($labels, $values, $_message);
		
		$_message = $this->inversePregQuote($_message);	
		
		// Remove empty tags
		//$pattern = "/<[^\/>]*>([\s]?)*<\/[^>]*>/";		
		//$_message = preg_replace($pattern, '', $_message);
		 
		return $_message;
	}
	
	private function _replaceSingleElementID($matches)
	{
		return $this->new_elements[$matches[0]];
	}
	
	private function _replaceStringElementID($matches)
	{
		return $this->new_elements[$matches[0]];
	}
	
	private function _replaceMessageElementID($matches)
	{
		return $matches[1].'_'.$this->new_elements[$matches[2]].$matches[3];
	}
	
	private function inversePregQuote($str)
	{
		return strtr($str, array(
			'\\.'  => '.',
			'\\\\' => '\\',
			'\\+'  => '+',
			'\\*'  => '*',
			'\\?'  => '?',
			'\\['  => '[',
			'\\^'  => '^',
			'\\]'  => ']',
			'\\$'  => '$',
			'\\('  => '(',
			'\\)'  => ')',
			'\\{'  => '{',
			'\\}'  => '}',
			'\\='  => '=',
			'\\!'  => '!',
			'\\<'  => '<',
			'\\>'  => '>',
			'\\|'  => '|',
			'\\:'  => ':',
			'\\-'  => '-'
		));
	}
}