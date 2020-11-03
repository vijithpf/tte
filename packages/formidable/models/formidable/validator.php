<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable', 'formidable');

class FormidableValidator extends Formidable {
	
	protected $err = array();
	
	private $elementID = '';
	private $label = '';
	private $value = '';
	private $dependencies = array();
	
	private $do_check = true;
	
	public $str = '';
	public $nbr = '';
	
	public function __construct($elementID, $label, $dependencies = array())
	{
		$this->elementID = $elementID;
		$this->label = $label;
		$this->dependencies = $dependencies;
		
		$this->str = Loader::helper('validation/strings');
		$this->nbr = Loader::helper('validation/numbers');
		
		$this->check_dependencies();
	}
	
	public function check_dependencies() {			
		if (!empty($this->dependencies)) {
			$do_check_or = array();
			foreach((array)$this->dependencies as $dependency_rule) {
				if (!empty($dependency_rule)) {					
					$do_check_and = array();				
					foreach((array)$dependency_rule as $dependency) {						
						$tmp_do_check = false;
						$_value = (array)$this->request($dependency['element']);
						if (!empty($dependency['value'])) {
							$_dependency_value = (array)$dependency['value'];
							if ($_dependency_value == array_intersect($_dependency_value, $_value))	
								$tmp_do_check = true;
						} else {
							if (!empty($_value))
								$tmp_do_check = true;
						}						
						if ($dependency['inverse']) {
							if ($tmp_do_check) $tmp_do_check = false;
							else $tmp_do_check = true;
						}						
						$do_check_and[] = $tmp_do_check;
					}	
					if (in_array(false, (array)$do_check_and)) {
						$do_check_or[] = false;
					} else {
						$do_check_or[] = true;
					}
				}
			}
			if (!in_array(true, (array)$do_check_or)) {
				$this->do_check = false;
				return;	
			}
		}
	}
	
	public function add($error) {
		if ($this->do_check)
			$this->err[] = sprintf($error, $this->label);	
	}
		
	public function required($value)
	{
		if (!$this->str->notempty($value))
			$this->add(t('Field "%s" is invalid'));	
	}
	
	public function integer($value) {
		if (!$this->nbr->integer($value))
			$this->add(t('Field "%s" is an invalid numeric value'));
	}
	
	public function email($value)
	{
		if (!$this->str->email($value))
			$this->add(t('Field "%s" is an invalid e-mailaddress'));	
	}
	
	public function url($value)
	{
		if(!filter_var($value, FILTER_VALIDATE_URL))
			$this->add(t('Fields "%s" is an invalid url'));	
	}
	
	public function confirmation($value, $confirmation)
	{
		if (!empty($value)) {
			if (strtolower($value) != strtolower($confirmation))
				$this->add(t('Fields "%s" and it\'s confirmation don\'t match'));	
		}
	}
	
	public function chars_allowed($value, $allowed)
	{		
		if (!empty($value)) {
			if (sizeof($allowed) > 0) {
				foreach($allowed as $allow) {
					$check_for[] = ($allow=='lcase')?'a-z':'';	
					$check_for[] = ($allow=='ucase')?'A-Z':'';	
					$check_for[] = ($allow=='digits')?'0-9':'';	
					$check_for[] = ($allow=='symbols')?'\!\#$%&()\*+-=?\[\]{}|~':'';	
				}
				
				if (sizeof($check_for) > 0)
					if (!preg_match('/^['.@implode('', $check_for).']*$/', $value))
						$this->add(t('Fields "%s" only allows the following characters: %s', '%s', @implode(' ',$check_for)));	
			}
		}
	}
	
	public function min_max($value, $min, $max, $type)
	{
		if (!empty($value)) {
			switch ($type) {
				case 'words':
					$words = explode(" ", $value);
					if (sizeof($words) < $min || (sizeof($words) > $max && $max > 0))
						if ($max > 0)
							$this->add(t('Field "%s" should be between %s and %s words', '%s', $min, $max));
						else
							$this->add(t('Field "%s" should have at least %s words', '%s', $min));
				break;
				
				case 'chars':
					if (!$this->str->min($value, $min) || (!$this->str->max($value, $max) && $max > 0))
						if ($max > 0)
							$this->add(t('Field "%s" should be between %s and %s charachters', '%s', $min, $max));
						else
							$this->add(t('Field "%s" should be at least %s charachters', '%s', $min));	
				break;
				
				case 'value':
					if ($value < $min || ($value > $max && $max > 0))
						if ($max > 0)
							$this->add(t('Field "%s" should be a numeric value between %s and %s', '%s', $min, $max));	
						else
							$this->add(t('Field "%s" should be a numeric value equal or greater than %s', '%s', $min));
				break;
				
				case 'options':
					if (sizeof($value) < $min || (sizeof($value) > $max && $max > 0))
						if ($min == $max && $max > 0)
							$this->add(t('Field "%s" should have %s options selected', '%s', $min));
						else
							$this->add(t('Field "%s" should have between %s and %s options selected', '%s', $min, $max));
				break;
				
				case 'files':
					if (sizeof($value) < $min || (sizeof($value) > $max && $max > 0))
						if ($min == $max && $max > 0)
							$this->add(t('Field "%s" should have %s files', '%s', $min));
						else
							$this->add(t('Field "%s" should have between %s and %s files', '%s', $min, $max));
				break;
			}
		}
	}
	
	public function option_other($value, $other_value)
	{
		$value = (array)$value;
		$other = array_pop($value);
		if ($other == 'option_other')
			if (!$this->str->notempty($other_value))
				$this->add(t('Field "%s" is invalid or empty'));
	}
	
	public function allowed_extensions($value, $extensions)
	{
		if (sizeof($value) > 0)
			for ($i=0; $i<sizeof($value); $i++)
				if (!preg_match('/'.$value[$i]['ext'].'/i', $extensions))
					$this->add(t('Field "%s" files are not permitted'));	
	}
		
	public function getList() {
		
		if (!empty($this->err)) 
			return $this->err;			
		
		return false;
	}
}

class FormidableValidatorProperty extends FormidableValidator {
		
	public function __construct()
	{
		parent::__construct('', '');
	}	
	
	public function label($value) 
	{
		if (!$this->str->notempty($value))
			$this->add(t('Field "%s" is invalid', t('Label / Name')));	
	}

	public function placeholder($enable, $value)
	{
		if ($enable)
			if (!$this->str->notempty($value))
				$this->add(t('Field "%s" is invalid', t('Placeholder')));	
	}
	
	public function default_value($enable, $value)
	{
		if ($enable) 
			if (!$this->str->notempty($value))
				$this->add(t('Field "%s" is invalid', t('Default')));	
	}
	
	public function mask($enable, $value)
	{
		if ($enable)
			if (!$this->str->notempty($value))
				$this->add(t('Field "%s" is invalid', t('Masking')));	
	}
	
	public function min_max($enable, $min, $max, $type)
	{
		if ($enable) 
		{
			if (!$this->nbr->integer($min))
				$this->add(t('Field "%s" isn\'t a valid integer', t('Minimum value')));
			
			if (!$this->nbr->integer($max) && $max != '')
				$this->add(t('Field "%s" isn\'t a valid integer', t('Maximum value')));
			
			if (!$this->str->notempty($type))
				$this->add(t('Field "%s" is invalid', t('Minimum/Maximum type')));						
		}
	}
	
	public function tooltip($enable, $value)
	{
		if ($enable)
			if (!$this->str->notempty($value))
				$this->add(t('Field "%s" is invalid', t('Tooltip')));	
	}
	
	public function tinymce($value)
	{
		if (!$this->str->notempty($value))
			$this->add(t('Field "%s" is invalid', t('Content')));	
	}
	
	public function options($values)
	{
		$_hasname = false;
		if (sizeof($values) > 0)
			foreach($values as $key => $value)
				if ($this->str->notempty($value))
					$_hasname = true;

		if (!$_hasname)
			$this->add(t('Field "%s" is invalid', t('Option')));
	}
	
	
	public function other($enable, $value, $type)
	{
		if ($enable)
		{
			if (!$this->str->notempty($value))
				$this->add(t('Field "%s" is invalid', t('Other option (value)')));
			
			if (!$this->str->notempty($type))
				$this->add(t('Field "%s" is invalid', t('Other option (type)')));	
		}
	}
	
	public function html_code($value)
	{
		if (!$this->str->notempty($value))
			$this->add(t('Field "%s" is invalid', t('Code')));	
	}
	
	public function appearance($value)
	{
		if (!$this->str->notempty($value))
			$this->add(t('Field "%s" is invalid', t('Appearance')));	
	}
	
	public function format($value, $other)
	{
		if (!$this->str->notempty($value))
			$this->add(t('Field "%s" is invalid', t('Format')));
		else
		{
			if ($value == 'other')
				if (!$this->str->notempty($other))
					$this->add(t('Field "%s" is invalid', t('Format (other)')));	
		}
	}
	
	public function advanced($enable, $value)
	{
		if ($enable)
			if (!$this->str->notempty($value))
				$this->add(t('Field "%s" is invalid', t('Advanced options')));	
	}
	
	public function allowed_extensions($enable, $value)
	{
		if ($enable) {
			if (!$this->str->notempty($value) || !$this->str->min($value, 2))
				$this->add(t('Field "%s" is invalid', t('Allowed extensions')));	
			else
			{
				$extensions_string = strtolower(str_replace(array("*","."," "), "", UPLOAD_FILE_EXTENSIONS_ALLOWED));
                $allowed_extensions = explode(";", $extensions_string);
				
				$values_string = strtolower(str_replace(array("*","."," "), "", $value));
				$values = explode(",", $values_string);
				
				$_diff = array_diff($values, $allowed_extensions);
				if (!empty($_diff))
					$this->add(t('Extensions "%s" in "%s" aren\'t allowed globally (check Allowed File Types)', @implode(', ', $_diff), t('Allowed extensions')));	
		    }
		}
	}
	
	public function fileset($enable, $value)
	{
		if ($enable)
			if (!$this->str->notempty($value))
				$this->add(t('Field "%s" is invalid', t('Assign to fileset')));	
	}
	
	public function css($enable, $value)
	{
		if ($enable)
			if (!$this->str->notempty($value))
				$this->add(t('Field "%s" is invalid', t('CSS Class')));	
	}
	
	public function from($from, $reply)
	{
		if ($from['type'] != 'other') 
		{
			if (!$this->str->notempty($from['type']))
				$this->add(t('Field %s is invalid', t('From')));
		}	
		else 
		{
			if (!$this->str->notempty($from['name']))
				$this->add(t('Field %s is invalid', t('From (Name)')));
			
			if (!$this->str->email($from['email']))
				$this->add(t('Field %s is invalid', t('From (Email Address)')));
			
			if ($reply['type'] != 'other') 
			{
				if (!$this->str->notempty($reply['type']))
					$this->add(t('Field %s is invalid', t('Reply To')));
			}	
			else 
			{
				if (!$this->str->notempty($reply['name']))
					$this->add(t('Field %s is invalid', t('Reply To (Name)')));
				
				if (!$this->str->email($reply['email']))
					$this->add(t('Field %s is invalid', t('Reply To (Email Address)')));
			}	
		}	
	}	
	
	public function send_to($send, $custom, $custom_value)
	{      
		if (intval($custom) != 1) {
			if(!is_array($send))
				$select = array_filter(@explode(',', $send));
			else
				$select = $send;
	
			if (sizeof($select) <= 0)
				$this->add(t('Field %s isn\'t selected', t('Send to')));
		}
		else
		{
			$emails = @explode(',', $custom_value);
			if (sizeof($emails) <= 0) 
				$this->add(t('Field %s is invalid', t('Send to (custom)')));   
			else
			{
				foreach ($emails as $email)
				{
					if (!$this->str->email(trim($email)))
					{
						$this->add(t('Field %s is invalid', t('Send to (custom)')));
					break;
					}
				}                        
			}
		}
	} 
	public function subject($value) 
	{
		if (!$this->str->notempty($value))
			$this->add(t('Field "%s" is invalid', t('Subject')));	
	}

	public function message($value) 
	{
		if (!$this->str->notempty($value))
			$this->add(t('Field "%s" is invalid', t('Message')));	
	}

}	

class FormidableValidatorDependencies extends FormidableValidator {
		
	public function __construct()
	{
		parent::__construct('', '');
	}
	
	public function dependency($dependencies) 
	{
		$rule = 1;
		
		foreach ((array)$dependencies as $dependency) {
			$_actions = $_elements = $tmp_action = array();
			foreach ((array)$dependency['action'] as $action) {
				$_actions[] = array_filter(array('action' => $action['action'],
												 'action_value' => $action['action_value'],
												 'action_select' => $action['action_select']));
				$tmp_action[] = $action['action'];
			}
			foreach ((array)$dependency['element'] as $element) {
				$_elements[] = array_filter(array('element' => $element['element'],
												  'element_value' => $element['element_value'],
												  'condition' => $element['condition'],
												  'condition_value' => $element['condition_value']));
			}
			if (!empty($_actions)) {
				if (array_unique($tmp_action) != $tmp_action)
					$this->add(t('Dependency Rule #%s: %s is already used', $rule, ucfirst($action['action'])));
				else {	
					foreach ($_actions as $_action) {
						if (!empty($_action['action'])) {	
							if ($_action['action'] == 'class' || $_action['action'] == 'placeholder')	{
								if (empty($_action['action_value'])) 
									$this->add(t('Dependency Rule #%s: %s is invalid or not selected', $rule, $action['action']));
							} elseif ($_action['action'] == 'value')	{
								if (empty($_action['action_value']) && empty($_action['action_select']))	{
									$this->add(t('Dependency Rule #%s: %s is invalid or not selected', $rule, $action['action']));
								}
							}						
							if (!empty($_elements)) {
								foreach ($_elements as $_element) {			
									if (empty($_element['element']))	
										$this->add(t('Dependency Rule #%s: no depending element selected', $rule));
									
									if ($_element['condition'] != 'enabled' && $_element['condition'] != 'disabled' &&
										$_element['condition'] != 'empty' && $_element['condition'] != 'not_empty' &&
										!empty($_element['condition']))	
										if (empty($_element['condition_value']))	
											$this->add(t('Dependency Rule #%s: condition value is invalid', $rule));
								}
							} else
								$this->add(t('Dependency Rule #%s: no depending element selected', $rule));	
						}
					}
				}
			}			
			$rule++;		
		}
	}
}