<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementPhone extends FormidableElement {
	
	public $element_text = 'Phone number';
	public $element_type = 'phone';
	public $element_group = 'Pre-Defined Elements';	
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
	    'default' => true,
		'placeholder' => true,
	    'mask' => array(
			'placeholder' => '(999) 999-9999',
			'formats' => array(
				'(999) 999-9999',
				'+99 (999) 999-9999',
				'+99(9)999-999999',
				'(+99) 9999 9999',
				'9999 9999',
				'(9999) 999999',
				'(9999) 99 99 99',
				'9999 99 99 99',
				'999-999-9999'
			),
			'note' => ''
		),
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
		
		$this->properties['mask']['note'] = array(
			t('Your mask not available? Add a text field and create your own mask'),
			t('Send your mask to %s', '<a href="mailto:wim@dewebmakers.nl">wim@dewebmakers.nl</a>'),
			t('More information about masking: <a href="%s" target="_blank">click here</a>', 'http://digitalbush.com/projects/masked-input-plugin/')
		);
	}
	
	public function generate() 
	{				
		$form = Loader::helper('form');
		
		$_type = 'text';
		if (Formidable::$html5)
			$_type = 'telephone';
			
		$this->setAttribute('input', $form->{$_type}($this->handle, $this->value, $this->attributes));

		if (!empty($this->mask))
			$this->addJavascript("if ($.fn.mask) { $('#".$this->handle."').mask('".$this->properties['mask']['formats'][intval($this->mask_format)]."') }");
	}
	
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required)
			$validator->required($this->request($this->handle));
			
		return $validator->getList();
	}
}