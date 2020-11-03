<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementWysiwyg extends FormidableElement {
	
	public $element_text = 'Content (WYSIWIG)';
	public $element_type = 'wysiwyg';
	public $element_group = 'Layout Elements';	
	
	public $is_layout = true; // Is layout element, so change the view.... 
	
	public $properties = array(
		'label' => true,
		'tinymce' => true,
		'css' => true
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
		$_attributes = $this->attributes;				
		$this->setAttribute('input', '<div name="'.$this->handle.'" class="'.$_attributes['class'].'">'.Loader::helper('editor', 'formidable')->translateFromEditMode($this->tinymce_value).'</div>');		
	}
}