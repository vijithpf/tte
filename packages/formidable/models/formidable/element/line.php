<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementLine extends FormidableElement {
	
	public $element_text = 'Line / Break';
	public $element_type = 'line';
	public $element_group = 'Layout Elements';	
	
	public $is_layout = true; // Is layout element, so change the view.... 
	
	public $properties = array(
		'label' => true
	);
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
	}
	
	public function generate() 
	{				
		$this->setAttribute('input', '<br />');
	}
}