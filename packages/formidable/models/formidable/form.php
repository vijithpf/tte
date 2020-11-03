<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable', 'formidable');

class FormidableForm extends Formidable {
	
	public function __construct($formID = 0, $answerSetID = 0)
	{		
		if (!empty($formID))
			$this->getById($formID);
				
		if (!empty($this->formID)) {				
			// Setting up HTML5
			$this->setHTML5($this->html5);
			
			// Load layouts (and elements)
			$this->getLayouts();
			
			// Load mailings
			$this->getMailings();	
					
			// Count submissions
			$this->countResults();	
			
			// Get last submission
			$this->lastResult();		
							
			// Load result from answerSetID (if set)
			$_answerSetID = intval($_SESSION['answerSetID']);
			if (!empty($answerSetID))
				$_answerSetID = intval($answerSetID);
							
			$this->answerSetID = $_answerSetID;
								
			$this->getResult();	
		}
	}
	
	public function getID() {
		return $this->formID;
	}
		
	private function getById($formID) 
	{		
		$db = Loader::db();
					
		$f = $db->getRow("SELECT * 
						  FROM FormidableForms 
						  WHERE formID = ?", array($formID));	
		if (empty($f))
			return false;

		$this->setAttributes(array_filter($f));
	}
			
	public function getLayouts()
	{
		Loader::model('formidable/layout', 'formidable');
		
		$db = Loader::db();
		$layouts = $db->getAll("SELECT layoutID,
									   rowID						 
								 FROM FormidableFormLayouts
								 WHERE formID = ?
								 ORDER BY rowID ASC, sort ASC", array($this->formID));
								 		
		if (sizeof($layouts) > 0)
		{
			foreach ($layouts as $layout)
			{
				$nfl = new FormidableLayout(intval($layout['layoutID']));
				
				// Set elements
				if (sizeof($nfl->elements) > 0) 
					foreach ($nfl->elements as $element) 
						$this->elements[$element->elementID] = $element;
				
				// Set layout
				$this->layouts[intval($layout['rowID'])][intval($layout['layoutID'])] = $nfl;		
			}
		}
		else
		{
			//Build new FormidableLayout and add all existing elements to the new FormidableLayout
			$nfl = new FormidableLayout();
			if ($nfl->save(array('formID' => $this->formID, 'rowID' => 0)))
			{
				$this->layouts[] = $nfl;
			
				$db->query("UPDATE FormidableFormElements
							SET layoutID = ? 
							WHERE formID = ?", array($nfl->layoutID, $this->formID));
			}			
		}
	}
	
	public function getElements($types = 'all')
	{		
		$this->elements = array();
		
		if ($types == 'all' && sizeof($this->layouts) > 0)
		{
			$rows = $this->layouts;		
			if (sizeof($rows) > 0) 
				foreach ($rows as $layouts) 						
					if (sizeof($layouts) > 0) 
						foreach ($layouts as $layout) 
							if (sizeof($layout->elements) > 0) 
								foreach ($layout->elements as $element) 
									$this->elements[$element->elementID] = $element;
			return;
		}		
		
		switch ($types) 
		{			
			case 'send_to':
				$q  = "SELECT elementID,
							  element_type 
					   FROM FormidableFormElements
					   WHERE formID = ?
					   AND (element_type = 'emailaddress' OR element_type = 'recipientselector') 
					   ORDER BY sort ASC";
			break;
			
			case 'upload':
				$q = "SELECT elementID,
							 element_type 
					  FROM FormidableFormElements
					  WHERE formID = ?
					  AND element_type = 'fileupload' 
					  ORDER BY sort ASC";
			break;
			
			case 'all':
			default:
				$q = "SELECT elementID,
							 element_type							 
					  FROM FormidableFormElements
					  WHERE formID = ?
					  ORDER BY sort ASC";
			break;	
		}		
					
		$db = Loader::db();
		$elements = $db->getAll($q, array($this->formID));
		if (sizeof($elements) > 0)
			foreach ($elements as $element)
				$this->elements[$element['elementID']] = $this->loadElement($element['element_type'], $element['elementID']);	
	}
	
	public function getMailings()
	{
		Loader::model('formidable/mailing', 'formidable');
		
		$db = Loader::db();		
		$mailings = $db->getAll("SELECT * 
								 FROM FormidableFormMailings
								 WHERE formID = ?", array($this->formID));
							   
		if (sizeof($mailings) > 0)
			foreach ($mailings as $mailing)
				$this->mailings[$mailing['mailingID']] = new FormidableMailing(intval($mailing['mailingID']), $this->elements);	
			
	}	
	
	public function getResult()
	{			
		Loader::model('formidable/result', 'formidable');
		$this->results = new FormidableResult($this->answerSetID);	
					
		// Assign results to elements
		if (count($this->layouts)) 
		{
			foreach($this->layouts as $rowID => $row) 
			{
				if (count($row)) 
				{
					foreach($row as $layoutID => $layout) 
					{
						if(count($layout->elements)) 
						{
							foreach($layout->elements as $elementID => $element) 
							{
								// If form is submitted, overwrite values....else load values from results...
								if ($this->request('action') == 'submit')
									$this->layouts[$rowID][$layoutID]->elements[$elementID]->post();								
								elseif (!empty($this->results->answers))
								{
									$_value = unserialize($this->results->answers[$element->elementID]['answer_unformated']);
									$this->layouts[$rowID][$layoutID]->elements[$elementID]->value($_value);
								}
							}
						}
					}
				}
			}
		}	
	}
			
	public function countResults($by = '', $value = '', $return = false)
	{
		$this->submissions = 0;
		
		$db = Loader::db();
		
		$q = "SELECT COUNT(answerSetID) AS total 
			  FROM FormidableAnswerSets 
			  WHERE formID = ?
			  AND temp != 1";
			  
		if ($by != '' && $value != '')
			$q .= " AND ? = ?";
			
		$data = $db->getOne($q, array($this->formID, $by, $value));
		if ($data)
			$this->submissions = $data;
			
		if ($return)
			return $this->submissions;
	}
	
	public function lastResult($by = '', $value = '', $return = false)
	{
		$this->last_submission = t('Never');
		
		$db = Loader::db();
		
		$q = "SELECT submitted 
			  FROM FormidableAnswerSets 
			  WHERE formID = ?
			  AND temp != 1";
			  
		if ($by != '' && $value != '')
			$q .= " AND ? = ?";
		
		$q .= " ORDER BY submitted DESC";	
		$data = $db->getOne($q, array($this->formID, $by, $value));
		if ($data) 
			$this->last_submission = date(DATE_APP_GENERIC_MDY, strtotime($data));
		
		if ($return)
			return $this->last_submission;
	}
	
	public function checkLimits() 
	{
		if (!$this->formID)
			return false;
		
		if ($this->limit_submissions) {
			switch ($this->limit_submissions_type) {
				case 'total':
					if ($this->countResults('', '', true) >= intval($this->limit_submissions_value))
						return true;
				break;
				
				case 'ip':
					if ($this->countResults('ip', $_SERVER['REMOTE_ADDR'], true) >= intval($this->limit_submissions_value))
						return true;
				break;
				
				case 'user':
					$u = new User();
					if ($u->isLoggedIn()) 
						if ($this->countResults('userID', $_SERVER['REMOTE_ADDR'], true) >= intval($this->limit_submissions_value))
							return true;
				break;	
			}
		}		
		return false;
	}
	
	public function checkSchedule() 
	{
		if (!$this->formID)
			return false;
		
		if ($this->schedule) {
			if (strtotime($this->schedule_start) > 0 && strtotime("now") <= strtotime($this->schedule_start))
				return true;
			
			if (strtotime($this->schedule_end) > 0 && strtotime("now") > strtotime($this->schedule_end))
				return true;	
		}		
		return false;
	}
	
	public function save($params)
	{
		if (!$this->formID)	
			$this->add($params);
		else
			$this->update($params);	 
	}
	
	private function add($params)
	{			
		$db = Loader::db();
		
		$q = "INSERT INTO FormidableForms (`".@implode('`,`', array_keys($params))."`) 
			  VALUES (".str_repeat('?,', sizeof($params)-1)."?)";
					  				
		$db->query($q, $params);	
		$this->formID = $db->Insert_ID();		
	}
	
	private function update($params)
	{					
		$db = Loader::db();
		
		$_params = array_slice($params, 1);
		
		foreach ($_params as $key => $value) {
			$_string[] = '`'.$key.'`=?';
			$_data[] = $value;
		}
			
		$q = "UPDATE FormidableForms SET ".@implode(',', $_string)."
			  WHERE formID = ".intval($this->formID);
		
		$db->query($q, $_data);
	}
	
	public function duplicate()
	{		
		$_params_f = get_object_vars($this);
		$_params_f['label'] .= ' ('.t('copy').')';
				
		// Filter layouts
		$rows = $this->layouts;
				
		// Filter mailings
		$mailings = $this->mailings;
		
		$unset = array('formID','answerSetID','layouts','elements','mailings','results','submissions','last_submission','javascript','jquery');
		foreach ($unset as $u) {
			unset($_params_f[$u]);
		}	
		
		if (is_array($_params_f['css'])) {
			unset($_params_f['css']);
		}

		$nf = new FormidableForm();			
		$nf->add($_params_f);
		
		Loader::model('formidable/layout', 'formidable');
		Loader::model('formidable/element', 'formidable');
		Loader::model('formidable/mailing', 'formidable');
		
		if (sizeof($rows) > 0)
		{
			foreach ($rows as $layouts)
			{						
				if (sizeof($layouts) > 0)
				{
					foreach ($layouts as $layout)
					{
						$ofl = new FormidableLayout($layout->layoutID);			
						$nfl = $ofl->duplicate($nf->formID);
						
						if (sizeof($layout->elements) > 0)
						{
							foreach ($layout->elements as $element)
							{
								$ofe = new FormidableElement($element->elementID);				
								$nfe = $ofe->duplicate($nf->formID, $nfl->layoutID);
								
								$_new_elementID[$element->elementID] = $nfe->elementID;
							}
						}
					}
				}
			}
		}
		
		
		// Convert dependecies to new elements
		foreach ($_new_elementID as $oeID => $neID) {
			
			$_dependencies = $_dep_action = $_dep_element = array();
			
			$json = Loader::helper('json');
			
			$nfe = new FormidableElement($neID);
			if (empty($nfe->elementID))
				continue;
			
			if (empty($nfe->dependencies))
				continue;
			
			foreach ((array)$nfe->dependencies as $_r => $_dep) {						
				$_dep_action = $_dep_element = array();
				foreach ($_dep->actions as $_ac) {	
					$_dep_action[] = array_filter(array('action' => $_ac->action,
													    'action_value' => (string)$_ac->action_value,
													    'action_select' => (string)$_ac->action_select));
				}
				foreach ($_dep->elements as $_el) {	
					$_dep_element[] = array_filter(array('element' => $_new_elementID[$_el->element],
														 'element_value' => (string)$_el->element_value,
														 'condition' => (string)$_el->condition,
														 'condition_value' => (string)$_el->condition_value));
				}
				$_dependencies[] = array('actions' => $_dep_action,
										 'elements' => $_dep_element);
			}			
			$nfe->save(array('dependencies' => $json->encode($_dependencies)));
		}
		
		if (sizeof($mailings) > 0)
		{
			foreach ($mailings as $mailing)
			{	
				$ofm = new FormidableMailing($mailing->mailingID);				
				$nfm = $ofm->duplicate($nf->formID, $_new_elementID);
			}
		}
		
		return true;
	}
	
	public function delete()
	{		
		$rows = $this->layouts;
		$mailings = $this->mailings;
		$results = $this->results;
				
		Loader::model('formidable/layout', 'formidable');
		Loader::model('formidable/element', 'formidable');
		Loader::model('formidable/mailing', 'formidable');
		Loader::model('formidable/result', 'formidable');
		
		if (sizeof($rows) > 0)
		{
			foreach ($rows as $layouts)
			{						
				if (sizeof($layouts) > 0)
				{
					foreach ($layouts as $layout)
					{
						$fl = new FormidableLayout($layout->layoutID);			
						$fl->delete();
						
						if (sizeof($layout->elements) > 0)
						{
							foreach ($layout->elements as $element)
							{
								$fe = new FormidableElement($element->elementID);				
								$fe->delete();
							}
						}
					}
				}
			}
		}
		
		if (sizeof($mailings) > 0)
		{
			foreach ($mailings as $mailing)
			{	
				$fm = new FormidableMailing($mailing->mailingID);				
				$fm->delete();
			}
		}
		
		if (sizeof($results) > 0)
		{
			foreach ($results as $result)
			{	
				$fr = new FormidableResult($result->answerSetID);				
				$fr->delete();
			}
		}

		$db = Loader::db();
		
		$db->query("DELETE FROM FormidableForms 
					WHERE formID = ?", array($this->formID));
		
		return true;
	}
	
	public function validate() {				
		
		$errors = array('message' => array(),
						'clear' => true);
		
		// Check formID matches
		if (intval($this->request('formID')) != intval($this->formID))
			$errors['message'][] = array('message' => t("Wrong form is loaded in the page, please try again"));	
								
		// Validate IP
		$ip = Loader::helper('validation/ip');
		if (!$ip->check())
			$errors['message'][] = array('message' => $ip->getErrorMessage());		
		
		// Check for spammers...
		$antispam = Loader::helper('validation/antispam');
		if (!$antispam->check(@implode("\n\r", $this->request()), 'formidable_block'))
			$errors['message'][] = array('message' => t("Unable to complete action due to our spam policy. Please contact the administrator of this site for more information."));		
			
		// Validate token
		$valt = Loader::helper('validation/token');
		if (!$valt->validate('formidable_form'))
			$errors['message'][] = array('message' => $valt->getErrorMessage());	
				
		if (count($errors['message']))
			return $errors;
		
		// Validate elements;		
		if (intval($this->review) == 0 || (intval($this->review) == 1 && $this->request('action') == 'submit')) {			
			
			// Validate captcha		
			if ($this->captcha) {		
				$captcha = Loader::helper('validation/captcha');
				if (!$captcha->check()) {
					$errors['clear'] = false;
					$errors['message'][] = array('elementID' => 'ccmCaptchaCode',
												 'handle' => 'ccmCaptchaCode',
												 'message' => t('Field "%s" is invalid', $this->captcha_label));
				}
			}
			if(count($this->elements)) {
				foreach($this->elements as $element) {
					$_err = $element->validate();
					if ($_err !== false) {
						$errors['clear'] = false;
						foreach ((array)$_err as $_e) { 
							$errors['message'][] = array('elementID' => $element->elementID,
														 'handle' => $element->handle,
														 'message' => $_e);
						}
					}
				}
			}
		}
		return $errors;
	}
}


class FormidableFormList extends DatabaseItemList {
	
	public function __construct() 
	{
		$this->setBaseQuery();
	}
	
	protected function setBaseQuery() 
	{
		$q = "SELECT ff.formID AS formID,
					 ff.label AS label
			  FROM FormidableForms AS ff";	
		$this->setQuery($q);		
	}
}