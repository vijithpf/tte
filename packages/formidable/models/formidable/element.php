<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable', 'formidable');

class FormidableElement extends Formidable {
	
	public function __construct($elementID = 0)
	{								
		if (intval($elementID) != 0)
			$this->getById($elementID);	
		
		// If specific element is loaded generate and load attributes
		if (get_class($this) != 'FormidableElement')
		{	
			$this->setDefaultAttributes();	// Load default attributes
			$this->default_value();			// Load default_value in element
		}
	}
	
	public function getID() {
		return $this->elementID;
	}

	private function getById($elementID) 
	{
		if(!is_numeric($elementID) || intval($elementID) == 0) 
			return false;
		
		$db = Loader::db();
					
		$element = $db->getRow("SELECT * 
							    FROM FormidableFormElements
							    WHERE elementID = ?", array($elementID));			
		if (!$element)
			return false;
			
		$db = Loader::db();
					
		$element = $db->getRow("SELECT * 
							    FROM FormidableFormElements
							    WHERE elementID = ?", array($elementID));			
		if (!$element)
			return false;
		
		$params = Loader::helper('json')->decode($element['params']);
		unset($element['params']);
		
		$depens = Loader::helper('json')->decode($element['dependencies']);
		unset($element['dependencies']);
		
		$this->setAttributes(array_filter($element));
		$this->setAttributes($params);
		
		$this->dependencies = $depens;
		
		$this->initializeDependency();
	}
		
	public function save($data)
	{
		if (!$this->elementID)	
			$this->add($data);
		else
			$this->update($data);	 
	}
	
	private function add($data)
	{					
		$db = Loader::db();
		$txt = Loader::helper('text');
		
		if (!$data['sort'])
			$data['sort'] = $this->getNextSort(intval($data['formID']));
			
		$q = "INSERT INTO FormidableFormElements (`".@implode('`,`', array_keys($data))."`) 
			  VALUES (".str_repeat('?,', sizeof($data)-1)."?)";
		
		$db->query($q, $data);
		$this->elementID = $db->Insert_ID();		
		$this->handle = $txt->sanitizeFileSystem($data['label'].'_'.$this->elementID);
		
		$this->update(array('label_import' => $this->handle));
	}
	
	private function update($data)
	{					
		$db = Loader::db();
		$txt = Loader::helper('text');
		
		foreach ($data as $key => $value) {
			$update_string[] = '`'.$key.'` = ?';
			$update_data[] = $value;
		}
		if ($data['label'])
			$update_string[] = '`label_import` = \''.$txt->sanitizeFileSystem($data['label'].'_'.$this->elementID).'\'';
			
		$q = "UPDATE FormidableFormElements SET ".@implode(', ', $update_string)."
			  WHERE elementID = '".$this->elementID."'";
		
		$db->query($q, $update_data);
		
		if ($data['label'])
			$this->handle = $txt->sanitizeFileSystem($data['label'].'_'.$this->elementID);
	}
	
	public function duplicate($formID = 0, $layoutID = 0)
	{
		$text = Loader::helper('text');
		
		$db = Loader::db();
		$element = $db->getRow("SELECT * 
							    FROM FormidableFormElements
							    WHERE elementID = ?", array($this->elementID));			
		if (!$element)
			return false;
		
		// Set new formID and layoutID if there...
		if (intval($formID) != 0)
			$element['formID'] = $formID;
		
		if (intval($layoutID) != 0)
			$element['layoutID'] = $layoutID;
		
		if (intval($formID) == 0 && intval($layoutID) == 0)
		{			
			$element['label'] .= ' ('.t('copy').')';
			$element['label_import'] = $text->sanitizeFileSystem($element['label']);
		}
		$element['sort'] = $this->getNextSort(intval($element['formID']));
		
		$unset = array('elementID');
		foreach ($unset as $u) {
			unset($element[$u]);
		}
				
		$nfe = new FormidableElement();			
		$nfe->add($element);		
	
		return $nfe;
	}
	
	public function delete()
	{
		$db = Loader::db();
		
		$r = $db->query("DELETE FROM FormidableFormElements 
					     WHERE elementID = ?
					     AND formID = ?", array($this->elementID, $this->formID));
		
		$this->orderElement($this->formID);
		$this->deleteCustomColumnSet($this->formID);
						
		return true;
	}
	
	public function validate() 
	{
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required)
			$validator->required($this->request($this->handle));
		
		return $validator->getList();	
	}
	
	public function validateProperties() 
	{
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidatorProperty();
				
		if ($this->properties['label'])				
			$validator->label($this->request('label'));
		
		if ($this->properties['placeholder'])
			$validator->placeholder($this->request('placeholder'), $this->request('placeholder_value'));
		
		if ($this->properties['default'])			
			$validator->default_value($this->request('default_value'), $this->request('default_value_'.$this->request('default_value_type')));
	
		if ($this->properties['mask'])
			$validator->mask($this->request('mask'), $this->request('mask_format'));
				
		if ($this->properties['min_max'])
			$validator->min_max($this->request('min_max'), $this->request('min_value'), $this->request('max_value'), $this->request('min_max_type'));
		
		if ($this->properties['tooltip'])
			$validator->tooltip($this->request('tooltip'), $this->request('tooltip_value'));
		
		if ($this->properties['tinymce_value'])
			$validator->tinymce($this->request('tinymce_value'));
		
		if ($this->properties['html_code'])
			$validator->html_code($this->request('html_code'));
												
		if ($this->properties['options']) 
			$validator->options($this->request('options_name'));
		
		if ($this->properties['option_other']) 
			$validator->other($this->request('option_other'), $this->request('option_other_value'), $this->request('option_other_type'));
		
		if ($this->properties['appearance'])				
			$validator->appearance($this->request('appearance'));
		
		if ($this->properties['format'])				
			$validator->format($this->request('format'), $this->request('format_other'));
					
		if ($this->properties['advanced'])
			$validator->advanced($this->request('advanced'), $this->request('advanced_value'));
		
		if ($this->properties['allowed_extensions'])
			$validator->allowed_extensions($this->request('allowed_extensions'), $this->request('allowed_extensions_value'));
											
		if ($this->properties['fileset'])
			$validator->fileset($this->request('fileset'), $this->request('fileset_value'));
		
		if ($this->properties['css'])
			$validator->css($this->request('css'), $this->request('css_value'));		
		
		if ($this->properties['submission_update'])			
			$validator->default_value($this->request('submission_update'), $this->request('submission_update_'.$this->request('submission_update_type')));
												
		return $validator->getList();	
	}
	
	
	public function validateDependencies() 
	{
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidatorDependencies();
		
		$dependencies = $this->request('dependency');
		if (!empty($dependencies)) {
			$validator->dependency($dependencies);
		}				
		return $validator->getList();		
	}
	
	public function label($format = '', $values = array()) 
	{
		if (!$this->label)
			return '';
			
		if ($format != '' && sizeof($values) > 0)
		{
			foreach ($values as $value)
				$v[] = $this->{$value};
				
			return vsprintf($format, $v);
		}			
		return '<label for="'.$this->handle.'">'.$this->label.'</label>';
	}
	
	public function input($format = '', $values = array()) 
	{
		if (!$this->input)
			return '';
			
		if ($format != '' && sizeof($values) > 0)
		{
			foreach ($values as $value)
				$v[] = $this->{$value};
				
			return vsprintf($format, $v);
		}	
		return $this->input;
	}
	
	public function confirm($format = '', $values = array()) 
	{
		if (!$this->confirm)
			return '';
			
		if ($format != '' && sizeof($values) > 0)
		{
			foreach ($values as $value)
				$v[] = $this->{$value};
				
			return vsprintf($format, $v);
		}	
		return $this->confirm;
	}
	
	public function tooltip() 
	{
		$text = Loader::helper('text');	
		if (!empty($this->tooltip))	
			return '<div id="tooltip_'.$this->elementID.'" class="tooltip">'.$text->sanitize($this->tooltip_value).'</div>';
		
		return '';
	}	
	
	public function post() 
	{		
		$_value['value'] = $this->request($this->handle);
		
		$_other = $this->request($this->handle.'_other');
		if (!empty($_other))
			$_value['value_other'] = $_other;
		
		$this->value($_value);
	}
	
	private function default_value()
	{
		$th = Loader::helper('text');
		
		$value = '';
		if ($this->default_value_type == 'value') 
			$value = $this->default_value_value;
		
		if ($this->default_value_type == 'request') 
			$value = $this->request($this->default_value_value);

		if ($this->default_value_type == 'user_attribute') 
			$obj = $this->getCurrentUser();				
		
		if ($this->default_value_type == 'collection_attribute')
			$obj = $this->getCurrentCollection();				
		
		if (is_object($obj)) {			
			if (strstr($this->default_value_value, 'ak_'))
				$value = $obj->getAttribute(substr($this->default_value_value, 3));	
			else {
				$class = 'get'.$th->camelcase($this->default_value_value);
				if (method_exists($obj, $class))
					$value = $obj->{$class}();	
			}
		}
			
		$this->value(array("value" => $value));
	}
			
	public function value($value = array())
	{																		
		if (!empty($value)) 
			foreach ((array)$value as $key => $val)
				$this->setAttribute($key, $val);	
				
		$option_other = false;
		if (@in_array('option_other', $this->value) && $this->properties['options'])
			$this->setAttribute('value_other', $this->value_other);
		
		//Set new results
		$this->serialized_value();
		$this->result();
		
		// Regenerate field
		$this->generate();
	}
	
	public function serialized_value()
	{										
		$_value['value'] = $this->value;
		if (!empty($this->value_other))
			$_value['value_other'] = $this->value_other;
						
		$this->setAttribute('serialized_value', serialize($_value));
	}
	
	public function result($value = array(), $seperator = ', ')
	{								
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
			
		$_value_other = $this->value_other;
		if (!empty($value['value_other']))
			$_value_other = $value['value_other'];
							
		$option_other = false;
		if (@in_array('option_other', $_value) && $this->properties['options'])
			$option_other = array_pop($_value);
		
		if ($option_other !== false)
			array_push($_value, $this->option_other_value.' '.$_value_other);
				
		if (is_array($_value))
			$_value = @implode($seperator, $_value); 
						
		$this->setAttribute('result', $_value);
	}
	
	public function submissionUpdate($cID = 0) {
			
		if (!$this->submission_update)
			return;
		
		if ($this->submission_empty == 1 && empty($this->result))
			return;
			
		$th = Loader::helper('text');
		
		if ($this->submission_update_type == 'user_attribute') {
			$obj = $this->getCurrentUser();	
			if (is_object($obj)) {			
				if (strstr($this->submission_update_value, 'ak_'))
					$obj->setAttribute(substr($this->submission_update_value, 3), $this->result);	
				else {
					switch ($this->submission_update_value) {
						case 'user_name':		
							$data = array('uName' => $this->result);	
						break;
						case 'user_email':		
							$data = array('uEmail' => $this->result);	
						break;	
						case 'user_password':	
							$data = array('uPassword' => $this->result, 'uPasswordConfirm' => $this->result);	
						break;
					}										
					$obj->update($data);					
				}
			}
		} elseif ($this->submission_update_type == 'collection_attribute') {
			$obj = $this->getCurrentCollection($cID);	
			if (is_object($obj)) {			
				if (strstr($this->submission_update_value, 'ak_'))
					$obj->setAttribute(substr($this->submission_update_value, 3), $this->result);	
				else {					
					$class = 'set'.$th->camelcase($this->submission_update_value);
					if (method_exists($obj, $class))
						$obj->{$class}($this->result);	
			
				}
			}
		}
	}
	
	public function callbackResult($value)
	{		
		if (empty($value))
			return '';
			
		$_value = unserialize($value);
		if ($_value !== false)
			$this->result($_value, ', ');
			
		$lh = Loader::helper('link', 'formidable');
		if (!empty($this->result))
			return $lh->url_and_email_ify($this->result);
		
		return '';	
	}
	
	private function getCurrentUser() {
		$u = new User();
		if (!is_object($u)) 
			return;
		
		$ui = UserInfo::getByID($u->getUserID());		
		if (!is_object($ui))
			return;
		
		return $ui;	
	}
	
	private function getCurrentCollection($cID = 0) {
		$c = Page::getByID($cID);
		
		if (!is_object($c) || intval($c->getCollectionID()) == 0)	
			$c = Page::getCurrentPage();
		
		if (!is_object($c) || intval($c->getCollectionID()) == 0)
			return;
					
		return $c;	
	}		
	
	public function callbackResultExport($value)
	{
		return $this->callbackResult($value);
	}
	
	public function getNextSort($formID) 
	{			
		return parent::getNextSort('element', $formID);
	}
	
	public function initializeDependency() {
		
		$text = Loader::helper('text');
		
		if (empty($this->dependencies))
			return false;
					
		foreach ((array)$this->dependencies as $rule => $_dependency) {									
			$_actions = $_elements = $tmp_elements = array();
			
			foreach ($_dependency->actions as $action_rule => $_action) {
				if ($_action->action == 'enable')
					$_actions['enable'] = true;				
				
				if ($_action->action == 'show')
					$_actions['show'] = true;	
				
				if ($_action->action == 'value') 
					$_actions['value'] = $_action->action_value.$_action->action_select;
				
				if ($_action->action == 'placeholder') 
					$_actions['placeholder'] = $_action->action_value;
					
				if ($_action->action == 'class') 
					$_actions['class'] = $_action->action_value;	
			}
			
			foreach ($_dependency->elements as $element_rule => $_element) {
				$_el = new FormidableElement($_element->element);
				if (!$_el->elementID)
					continue;
				
				$key = array_search($_el->handle, (array)$tmp_elements);
				if ($key !== false)
					$element_rule = $key;			
				
				$tmp_elements[$element_rule] = $_elements[$element_rule]['handle'] = $_el->handle;
				
				// TODO
				// Recipient selector in this list?
				if ($_el->element_type == 'radio' || $_el->element_type == 'checkbox' || $_el->element_type == 'select') {
					$_options = @array_filter((array)unserialize($_el->options));
					if (sizeof($_options) > 0) {		
						for ($i=0; $i<sizeof($_options); $i++) {
							if (!$_options[$i]['value'])
								$_options[$i]['value'] = $_options[$i]['name'];
							
							if ($_el->element_type == 'select') {					
								$_elements[$element_rule]['options'][$_options[$i]['value']] = $_options[$i]['value'];
							} else {
								$_elements[$element_rule]['options'][$_options[$i]['value']] = $text->sanitizeFileSystem($_el->handle).($i+1);
							}
						}
					}
				}
				$_elements[$element_rule]['type'] = $_el->element_type;
				
				if (!empty($_element->element_value))
					if (!in_array($_element->element_value, (array)$_elements[$element_rule]['values']))
						$_elements[$element_rule]['values'][] = $_element->element_value;			
				
				if (!empty($_element->condition) && in_array($_element->condition, array('empty', 'not_empty'))) {					
					if ($_element->condition == 'empty')					
						$_elements[$element_rule]['empty'][] = 1;
					
					if ($_element->condition == 'not_empty')					
						$_elements[$element_rule]['not_empty'][] = 1;		
				}

				if (!empty($_element->condition) && !empty($_element->condition_value)) {
					
					if ($_element->condition == 'contains')
						if (!in_array($_element->condition_value, (array)$_elements[$element_rule]['values']))
							$_elements[$element_rule]['match'][] = $_element->condition_value;
					
					if ($_element->condition == 'not_contains')
						if (!in_array($_element->condition_value, (array)$_elements[$element_rule]['values']))
							$_elements[$element_rule]['not_match'][] = $_element->condition_value;
							
					if ($_element->condition == 'equals')					
						if (!in_array($_element->condition_value, (array)$_elements[$element_rule]['values']))
							$_elements[$element_rule]['values'][] = $_element->condition_value;
					
					if ($_element->condition == 'not_equals')					
						if (!in_array($_element->condition_value, (array)$_elements[$element_rule]['values']))
							$_elements[$element_rule]['not_values'][] = $_element->condition_value;							
				}
				
				// inverse values when no_value is selected...
				$_inverse = false;
				if (@in_array('no_value', (array)$_elements[$element_rule]['values']))
					$_inverse = true;
			}
						
			if (!empty($_actions) && !empty($_elements))
				$dependencies[$rule] = array('actions' => $_actions,
											 'elements' => $_elements,
											 'inverse' => $_inverse);
		}		
		//var_dump($dependencies);							
		if (!empty($dependencies)) {
			
			// Setup dependencies for validation
			$_validation = false;
			foreach ($dependencies as $rule => $dependency) {
				foreach ($dependency['actions'] as $action => $value) {	
					if ($action == 'show' || $action == 'enable')
						$_validation = true;
				}
								
				if ($_validation) {
					$dependency_rule = array();
					foreach ($dependency['elements'] as $key => $element) {
						$_handle = $element['handle'];
						$_value = (array)$element['values'];

						if (sizeof($element['options']) > 0) {						
							if (in_array('any_value', $_value)) {
								$_value = (array)$element['options'];							
								if ($element['type'] == 'radio' || $element['type'] == 'checkbox') {
									$_value = array_keys((array)$element['options']);		
								}
							} elseif (in_array('no_value', $_value)) {
								$_value = array();	
							}
						}
						$dependency_rule[] = array('element' => $_handle,
												   'value' => $_value);
					}
				}
				if (!empty($dependency_rule))
					$dependency_validation[] = $dependency_rule;
			}
			if (!empty($dependency_validation)) 		
				$this->dependency_validation = $dependency_validation;
			
			// Build action
			foreach ($dependencies as $rule => $dependency) {
				
				$_method .= 'if (';
				foreach ($dependency['elements'] as $key => $element) {
					
					if ($key > 0)
						$_method .= ' || ';
																
					$_multi = false;
					if ($element['type'] != 'select' && $element['type'] != 'recipientselector') {
						if (sizeof($element['options']) > 0) {						
							if (@in_array('any_value', (array)$element['values']) || @in_array('no_value', (array)$element['values']))
								$_method .= '(selector == \''.@implode('\' || selector == \'', $element['options']).'\') ';	
							else {
								$_tmp_options = '';
								foreach ((array)$element['values'] as $_value)
									$_tmp_options[] = $element['options'][$_value];								
								$_method .= '(selector == \''.@implode('\' || selector == \'', $_tmp_options).'\') ';		
							}
							$_multi = true;
						}
					}					
					if (!$_multi)
						$_method .= 'selector == \''.$element['handle'].'\'';
				}
				$_method .= ') { ';
				$_method .= 'ccmFormidableDependencyChange(\''.$this->handle.'\', [';
				$_method_not .= 'ccmFormidableDependencyChange(\''.$this->handle.'\', [';
																			
				foreach ($dependency['actions'] as $action => $value) {					
					switch ($action) {
						case 'value':
							if ($dependency['inverse']) {
								$_method .= '[\'value\', \'\'],';	
								$_method_not .= '[\'value\', \''.$value.'\'],';									
							} else { 
								$_method .= '[\'value\', \''.$value.'\'],';	
								$_method_not .= '[\'value\', \'\'],';								
							}
						break;
						case 'class':
							if ($dependency['inverse']) {
								$_method .= '[\'class\', \''.$value.'\', \'remove\'],';	
								$_method_not .= '[\'class\', \''.$value.'\', \'add\'],';									
							} else { 
								$_method .= '[\'class\', \''.$value.'\', \'add\'],';	
								$_method_not .= '[\'class\', \''.$value.'\', \'remove\'],';								
							}
						break;
						case 'placeholder':
							if ($dependency['inverse']) {
								$_method .= '[\'placeholder\', \'\'],';	
								$_method_not .= '[\'placeholder\', \''.$value.'\'],';									
							} else { 
								$_method .= '[\'placeholder\', \''.$value.'\'],';	
								$_method_not .= '[\'placeholder\', \'\'],';								
							}							
						break;
						case 'show':
							if ($dependency['inverse']) {
								$_method .= '[\'hide\', true],';
								$_method_not .= '[\'show\', true],';	
							} else {
								$_method .= '[\'show\', true],';
								$_method_not .= '[\'hide\', true],';
							}	
						break;
						case 'enable':
							if ($dependency['inverse']) {
								$_method .= '[\'disable\', true],';	
								$_method_not .= '[\'enable\', true],';
							} else {
								$_method .= '[\'enable\', true],';	
								$_method_not .= '[\'disable\', true],';
							}
						break;	
					}
				}
				
				$_method = substr($_method, 0, -1);
				$_method_not = substr($_method_not, 0, -1);
				
				$_method .= ']); ';
				$_method_not .= ']); ';
				$_method .= '} ';
			}
			
			$_javascript .= 'if (($(\'[name="'.$this->handle.'"], [name^="'.$this->handle.'["]\').length > 0) && ($.fn.dependsOn)) { ';
			$_javascript .= '$(\'[name="'.$this->handle.'"], [name^="'.$this->handle.'["]\').dependsOn(';	
			
			foreach ($dependencies as $rule => $dependency) {
				
				if ($rule > 0)
					$_javascript .= ').or(';
				
				$last_key = count($dependency['elements']);
				
				$_javascript .= '{ ';				
				
				foreach ($dependency['elements'] as $key => $element) {
										
					$_multi = false;
					
					if ($element['type'] != 'select' && $element['type'] != 'recipientselector') {
						if (sizeof($element['options']) > 0 && (@in_array('any_value', (array)$element['values']) || @in_array('no_value', (array)$element['values']))) {
							$_javascript .= '\'[id="'.@implode('"], [id="', $element['options']).'"]\' : { ';	
							$_multi = true;
						} elseif (sizeof($element['options']) > 0 && (!@in_array('any_value', (array)$element['values']) && !@in_array('no_value', (array)$element['values']))) {
							$_tmp_options = '';
							foreach ((array)$element['values'] as $_value)
								$_tmp_options[] = $element['options'][$_value];								
							$_javascript .= '\'[id="'.@implode('"], [id="', $_tmp_options).'"]\' : { ';	
							$_multi = true;
						}
					}
					
					if (!$_multi)
						$_javascript .= '\'[name="'.$element['handle'].'"], [name^="'.$element['handle'].'["]\' : { ';
											
					if (!empty($element['values'])) {
						if ($element['type'] == 'checkbox' || $element['type'] == 'radio') {
							$_javascript .= 'checked: true ';
						} else {
							if (@in_array('any_value', (array)$element['values']))
								$_javascript .= 'values: [\''.@implode('\', \'', $element['options']).'\'] ';	
							else		
								$_javascript .= 'values: [\''.@implode('\', \'', $element['values']).'\'] ';
						}
					}
					
					if (!empty($element['not_values'])) {
						if ($element['type'] == 'checkbox' || $element['type'] == 'radio') {
							$_javascript .= 'checked: false ';
						} else {
							if (@in_array('no_value', (array)$element['values']))
								$_javascript .= 'not: [\''.@implode('\', \'', $element['options']).'\'] ';	
							else		
								$_javascript .= 'not: [\''.@implode('\', \'', $element['not_values']).'\'] ';
						}
					}

					if (!empty($element['empty']))
						$_javascript .= 'notmatch: /([^\s])/ ';	
					
					if (!empty($element['not_empty']))
						$_javascript .= 'match: /([^\s])/ ';

					if (!empty($element['match']))
						$_javascript .= 'match: /'.@implode('|', $element['match']).'/gi ';	
					
					if (!empty($element['not_match']))
						$_javascript .= 'notmatch: /'.@implode('|', $element['not_match']).'/gi ';
							
					
					$_javascript .= '} ';
					
					if ($key < $last_key - 1)
						$_javascript .= ', ';
				}				
				$_javascript .= '} ';
				
				if ($rule == 0) {
					$_javascript .= ', { ';
					$_javascript .= 'disable: false, hide: false, ';
					$_javascript .= 'onEnable: function(e, selector) { ';
					$_javascript .= $_method;
					$_javascript .= '}, ';
					$_javascript .= 'onDisable: function(e) { ';
					$_javascript .= $_method_not;
					$_javascript .= '} ';
					$_javascript .= '} ';		
				}
			}
			$_javascript .= '); ';	
			$_javascript .= '} ';
		}
								
		if (!empty($_javascript))
			$this->addJavascript($_javascript);	
	}
}