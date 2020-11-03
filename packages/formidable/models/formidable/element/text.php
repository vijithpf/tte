<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementText extends FormidableElement {
	
	public $element_text = 'Text Field';
	public $element_type = 'text';
	public $element_group = 'Basic Elements';	
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
	    'default' => true,
		'placeholder' => true,
	    'mask' => '',
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
		
		$this->properties['mask'] = array(
			'note' => array(
				'a - '.t('Represents an alpha character').'(A-Z,a-z)',
				'9 - '.t('Represents a numeric character').'(0-9)',
				'* - '.t('Represents an alphanumeric character').'(A-Z,a-z,0-9)',
				'? - '.t('Represents optional data, everything behind the questionmark is optional'),
				t('Examples:'),
				t('Phone').': (999) 999-9999',
				t('Product Code').': a*-999-a999',
				t('More information about masking: <a href="%s" target="_blank">click here</a>', 'http://digitalbush.com/projects/masked-input-plugin/')
			)
		);
		
		$this->properties['min_max'] = array(
			'words' => t('Words'), 
			'chars' => t('Characters')
		);
	}
	
	public function generate() 
	{				
		$form = Loader::helper('form');
				
		$this->setAttribute('input', $form->text($this->handle, $this->value, $this->attributes));
		
		if (!empty($this->mask))
			$this->addJavascript("if ($.fn.mask) { $('#".$this->handle."').mask('".$this->mask_format."') }");
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
}