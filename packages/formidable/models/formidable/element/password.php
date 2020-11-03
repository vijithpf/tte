<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementPassword extends FormidableElement {
	
	public $element_text = 'Password';
	public $element_type = 'password';
	public $element_group = 'Basic Elements';	
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,
		'confirmation' => true,						
		'min_max' => '',
		'chars_allowed' => '',
		'tooltip' => true,
		'handling' => true
	);
	
	public $dependency = array(
		'has_value_change' => true,
		'has_placeholder_change' => true
	);
		
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
					
		$this->properties['min_max'] = array(
			'chars' => t('Characters')
		);
			
		$this->properties['chars_allowed'] = array(
			'lcase' => t('lowercase chars').' (a-z)', 
			'ucase' => t('uppercase chars').' (A-Z)',
			'digits' => t('digits').' (0-9)',
			'symbols' => t('symbols').' (!#$%&()*+-=?[]{}|~)'
		);
	}	
	
	public function generate() 
	{				
		$form = Loader::helper('form');
		
		$_input  = $form->password($this->handle, $this->value, $this->attributes);
		$_input .= '<div class="ui-password-meter password_strength"></div>';
		
		$this->setAttribute('input', $_input);		
		
		if ($this->properties['confirmation']) 
		{
			if (strpos($this->attributes['class'], 'password_confirm') === false)
				$this->attributes['class'] .= ' password_confirm';
			
			if ($this->placeholder)
				$this->attributes['placeholder'] = t('Confirm %s', $this->label);

			$this->setAttribute('confirm', $form->password($this->handle.'_confirm', $this->value, $this->attributes));
		}
		$this->addJavascript("if ($.fn.mask) { $('#".$this->handle."').pwstrength() }");
	}	
	
	public function validate() 
	{
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required)
			$validator->required($this->request($this->handle));
		
		if ($this->chars_allowed)
			$validator->required($this->request($this->handle), @explode(',', $this->chars_allowed_value));
		
		if ($this->min_max)	
			$validator->min_max($this->request($this->handle), $this->min_value, $this->max_value, $this->min_max_type);
		
		if ($this->confirmation)
			$validator->confirmation($this->request($this->handle), $this->request($this->handle.'_confirm'));
			
		return $validator->getList();
	}	
}