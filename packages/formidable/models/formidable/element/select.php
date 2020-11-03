<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementSelect extends FormidableElement {
	
	public $element_text = 'Selectbox';
	public $element_type = 'select';
	public $element_group = 'Basic Elements';	
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,
		'options' => true,
		'placeholder' => array(
			'note' => array(
				'First choice in the selectbox. Leave empty for an empty option.'
			)
		),
		'option_other' => '',
		'min_max' => '',
		'tooltip' => true,
		'multiple' => true,
		'handling' => true
	);
	
	public $dependency = array(
		'has_value_change' => true,
		'has_placeholder_change' => false
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);			

		$this->properties['min_max'] = array(
			'options' => t('Selected options')
		);
			
		$this->properties['option_other'] = array(
			'text' => t('Single text'),
			'textarea' => t('Textarea')
		);
	}
	
	public function generate()  
	{
		$form = Loader::helper('form');
								
		if ($this->multiple)
			$this->attributes['multiple'] = 'multiple';
			
		$placeholder = false;
		if (sizeof($this->attributes) > 0) 
			foreach ($this->attributes as $_name => $_value) {
				if ($_name == 'placeholder') {
					$placeholder = $_value;	
					continue;
				}
				$_attributes .= $_name.'="'.$_value.'" ';
			}
														
		$_select = '<select name="'.$this->handle.'[]" id="'.$this->handle.'" '.$_attributes.'>';
		
		if ($placeholder !== false)
			$_select .= '<option value="">'.$placeholder.'</option>';

		$_options = unserialize($this->options);	
		if (sizeof($_options) > 0) 
		{
			for ($i=0; $i<sizeof($_options); $i++)
			{			
				if (!$_options[$i]['value'])
					$_options[$i]['value'] = $_options[$i]['name'];
									
				$_selected = '';
				if (@in_array($_options[$i]['value'], (array)$this->value) || (empty($this->value) && $_options[$i]['selected'] === true))
					$_selected = 'selected="selected"';
					
				$_select .= '<option value="'.$_options[$i]['value'].'" '.$_selected.'>'.$_options[$i]['name'].'</option>';
			}
		}
				
		if (intval($this->option_other) != 0)
		{
			$selected = '';
			if (sizeof($this->value) > 0 && @in_array('option_other', $this->value))
				$selected = 'selected="selected"';
			
			$_select .= '<option value="option_other" '.$selected.'>'.$this->option_other_value.'</option>';
			
			$this->setAttribute('other', $form->{$this->option_other_type}($this->handle.'_other', $this->value_other, $this->attributes));
		}		
		$_select .= '</select>';
		
		$this->setAttribute('input', $_select);
	}
			
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required) {
			$validator->required(array_filter($this->request($this->handle)));
		}
			
		if ($this->min_max)	
			$validator->min_max($this->request($this->handle), $this->min_value, $this->max_value, $this->min_max_type);
		
		if ($this->option_other)
			$validator->option_other($this->request($this->handle), $this->request($this->handle.'_other'));
					
		return $validator->getList();
	}

	// Use your own validation beacause placeholder is normally required
	// The selectbox don't need this to be required.
	public function validateProperties() 
	{
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidatorProperty();
				
		if ($this->properties['label'])				
			$validator->label($this->request('label'));
			
		if ($this->properties['min_max'])
			$validator->min_max($this->request('min_max'), $this->request('min_value'), $this->request('max_value'), $this->request('min_max_type'));
		
		if ($this->properties['tooltip'])
			$validator->tooltip($this->request('tooltip'), $this->request('tooltip_value'));

		if ($this->properties['options']) 
			$validator->options($this->request('options_name'));
		
		if ($this->properties['option_other']) 
			$validator->other($this->request('option_other'), $this->request('option_other_value'), $this->request('option_other_type'));
		
		if ($this->properties['appearance'])				
			$validator->appearance($this->request('appearance'));		
		
		if ($this->properties['css'])
			$validator->css($this->request('css'), $this->request('css_value'));		
		
		if ($this->properties['submission_update'])			
			$validator->default_value($this->request('submission_update'), $this->request('submission_update_'.$this->request('submission_update_type')));
												
		return $validator->getList();	
	}		
}