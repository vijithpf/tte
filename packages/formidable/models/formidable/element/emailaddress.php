<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementEmailaddress extends FormidableElement {
	
	public $element_type = 'emailaddress';
	public $element_text = 'Email Address';
	public $element_group = 'Pre-Defined Elements';	
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'default' => true,
		'placeholder' => true,
		'required' => true,
		'confirmation' => true,						
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
	}
	
	public function generate()   
	{
		$form = Loader::helper('form');
				
		$_type = 'text';
		if (Formidable::$html5)
			$_type = 'email';
			
		$this->setAttribute('input', $form->{$_type}($this->handle, $this->value, $this->attributes));

		if (!empty($this->confirmation))
		{
			if (strpos($this->attributes['class'], 'emailaddress_confirm') === false)
				$this->attributes['class'] .= ' emailaddress_confirm';
				
			if ($this->placeholder)
				$this->attributes['placeholder'] = t('Confirm %s', $this->label);
								
			$this->setAttribute('confirm', $form->{$_type}($this->handle.'_confirm', $this->value, $this->attributes));
		}
	}
	
	public function validate() 
	{		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if (strlen($this->request($this->handle)) > 0)
			$validator->email($this->request($this->handle));
			
		if ($this->required)
			$validator->required($this->request($this->handle));
			
		if ($this->confirmation)
			$validator->confirmation($this->request($this->handle), $this->request($this->handle.'_confirm'));
		
		return $validator->getList();	
	}	
}