<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementTextarea extends FormidableElement {
	
	public $element_text = 'Text Area';
	public $element_type = 'textarea';
	public $element_group = 'Basic Elements';	
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'default' => array(
			'type' => 'textarea'
		),
		'placeholder' => true,
		'required' => true,
		'min_max' => '',
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
			'words' => t('Words'),
			'chars' => t('Characters')
		);
	}	
	
	public function generate()  
	{
		$form = Loader::helper('form');
		$this->setAttribute('input', $form->textarea($this->handle, $this->value, $this->attributes));
	}
	
	public function result($value = array(), $seperator = ', ')
	{			
		$text = Loader::helper('text');							
		
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
			
		parent::result(array('value' => $text->makenice($_value)));
	}
		
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required)
			$validator->required($this->request($this->handle));
			
		if ($this->min_max)	
			$validator->min_max($this->request($this->handle), $this->min_value, $this->max_value, $this->min_max_type);
		
		return $validator->getList();
	}
	
	public function callbackResult($value)
	{		
		$text = Loader::helper('text');	
		$_value = parent::callbackResult($value);		
		return $text->shorten($_value, 40);	
	}
	
	public function callbackResultExport($value) {
		return parent::callbackResult($value);
	}
}