<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementHidden extends FormidableElement {
	
	public $element_text = 'Hidden Field';
	public $element_type = 'hidden';
	public $element_group = 'Basic Elements';	
	
	public $properties = array(
		'label' => true,
		'default' => true,
		'css' => false
	);
	
	public $dependency = array(
		'has_value_change' => true
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
	}
	
	public function generate() 
	{				
		$form = Loader::helper('form');
		$this->setAttribute('input', $form->hidden($this->handle, $this->value, $this->attributes));
	}
}