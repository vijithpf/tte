<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementHtmlCode extends FormidableElement {
	
	public $element_text = 'HTML (code)';
	public $element_type = 'html_code';
	public $element_group = 'Layout Elements';	
	
	public $is_layout = true; // Is layout element, so change the view.... 
	
	public $properties = array(
		'label' => true,
		'html_code' => true,
		'css' => false
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
		$this->setAttribute('input', '<div name="'.$this->handle.'">'.$this->html_code.'</div>');
	}
}