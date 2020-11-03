<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementParagraph extends FormidableElement {
	
	public $element_text = 'Paragraph';
	public $element_type = 'paragraph';
	public $element_group = 'Layout Elements';	
	
	public $is_layout = true; // Is layout element, so change the view.... 
	
	public $properties = array(
		'label' => true,
		'content' => true,
		'appearance' => array(
			'p' => 'Paragraph (default)',
			'div' => 'Div',
			'pre' => 'Preformatted text',
			'blockquote' => 'Blockquote',
			'span' => 'Span',
			'address' => 'Address',
			'code' => 'Code'
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
		$text = Loader::helper('text');
		$this->setAttribute('input', '<'.$this->appearance.' class="'.$this->attributes['class'].'" name="'.$this->handle.'">'.$text->makenice($this->content).'</'.$this->appearance.'>');
	}
}