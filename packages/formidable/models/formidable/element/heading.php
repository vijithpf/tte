<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementHeading extends FormidableElement {
	
	public $element_text = 'Heading';
	public $element_type = 'heading';
	public $element_group = 'Layout Elements';	
	
	public $is_layout = true; // Is layout element, so change the view.... 
	
	public $properties = array(
		'label' => true,
		'appearance' => array(
			'h1' => 'Heading 1 (h1)',
			'h2' => 'Heading 2 (h2)',
		    'h3' => 'Heading 3 (h3)',
		    'h4' => 'Heading 4 (h4)',
		    'h5' => 'Heading 5 (h5)',
		    'h6' => 'Heading 6 (h6)'
		)
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
		$this->setAttribute('input', '<'.$this->appearance.' class="'.$this->attributes['class'].'" name="'.$this->handle.'">'.$this->label.'</'.$this->appearance.'>');
	}
}