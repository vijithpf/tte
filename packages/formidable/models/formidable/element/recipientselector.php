<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementRecipientselector extends FormidableElement {
	
	public $element_text = 'Recipient Selector';
	public $element_type = 'recipientselector';
	public $element_group = 'Special Elements';	
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,
		'options' => true,
		'min_max' => '',
		'tooltip' => true,
		'multiple' => true,
		'handling' => true
	);
	
	public $dependency = array(
		'has_value_change' => true
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);			

		$this->properties['min_max'] = array(
			'options' => t('Selected options')
		);
	}
	
	public function generate()  
	{
		$form = Loader::helper('form');
								
		if ($this->multiple)
			$this->attributes['multiple'] = 'multiple';
			
		if (sizeof($this->attributes) > 0) 
			foreach ($this->attributes as $_name => $_value)
				$_attributes .= $_name.'="'.$_value.'" ';
		
		// Prepare value for setting up the element
		$_value = array();
		if (is_array($this->value))
			foreach ($this->value as $_v)
				$_value[] = array_shift($_v);
														
		$_select = '<select name="'.$this->handle.'[]" id="'.$this->handle.'" '.$_attributes.'>';
		
		$_options = unserialize($this->options);	
		if (sizeof($_options) > 0) 
		{
			for ($i=0; $i<sizeof($_options); $i++)
			{												
				$_selected = '';
				if (@in_array($_options[$i]['value'], (array)$this->value) || (empty($this->value) && $_options[$i]['selected'] === true))
					$_selected = 'selected="selected"';
					
				$_select .= '<option value="'.$_options[$i]['name'].'" '.$_selected.'>'.$_options[$i]['name'].'</option>';
			}
		}		
		$_select .= '</select>';
		
		$this->setAttribute('input', $_select);
	}
			
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required)
			$validator->required($this->request($this->handle));
			
		if ($this->min_max)	
			$validator->min_max($this->request($this->handle), $this->min_value, $this->max_value, $this->min_max_type);
		
		if ($this->option_other)
			$validator->option_other($this->request($this->handle), $this->request($this->handle.'_other'));
					
		return $validator->getList();
	}	
	
	public function value($value = array()) {	
	
		if (!empty($value))
			$_value = is_array($value)?$value['value']:(string)$value;
		
		$_options = unserialize($this->options);	
		if (!empty($_options)) 
			foreach ($_options as $_option)
				$_available[$_option['name']] = array($_option['name'], $_option['value']);	
					
		if (!empty($_value)) 
		{
			foreach ($_value as $_v)
			{
				if (is_array($_v))
					$_v = array_shift($_v);
						
				if (array_key_exists($_v, $_available))
					$_result[] = $_available[$_v];
			}
		}					
		parent::value(array('value' => $_result));			
	}	
	
	public function result($value = array(), $seperator = ', ')
	{				
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
							
		if (is_array($_value))
			foreach ($_value as $_v)
				$_result[] = array_shift($_v);
						
		parent::result(!empty($_result)?array('value' => @implode($seperator, $_result)):'');
	}
}