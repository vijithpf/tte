<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementFullname extends FormidableElement {
	
	public $element_text = 'Full Name';
	public $element_type = 'fullname';
	public $element_group = 'Pre-Defined Elements';	
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,
		'tooltip' => true,
		'format' => array(
			'formats' => '',
			'note' => ''
		),
		'handling' => true
	);
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
		
		$this->properties['format']['formats'] = array(
			'{firstname} {prefix} {lastname}' => t('firstname prefix lastname'),
			'{firstname} {lastname}' => t('firstname lastname'),
			'{prefix} {lastname}' => t('prefix lastname'),
			'other' => t('Other format: ')
		);
		$this->properties['format']['note'] = array(
			'{firstname} - '.t('Firstname'),
			'{prefix} - '.t('Prefix'),
			'{lastname} - '.t('Lastname'),			
			'{n} - '.t('Break / New line'),
			t('You can also use specialchars like ,.!;: etc...')
		);	
	}
	
	public function generate() 
	{				
		$form = Loader::helper('form');
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' firstname';
		$_attributes['placeholder'] = t('Firstname');		
		$_firstname = $form->text($this->handle.'[firstname]', isset($this->value['firstname'])?$this->value['firstname']:'', $_attributes);
		
		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' prefix';
		$_attributes['placeholder'] = t('Prefix');		
		$_prefix = $form->text($this->handle.'[prefix]', isset($this->value['prefix'])?$this->value['prefix']:'', $_attributes);

		$_attributes = $this->attributes;		
		$_attributes['class'] .= ' lastname';
		$_attributes['placeholder'] = t('Lastname');		
		$_lastname = $form->text($this->handle.'[lastname]', isset($this->value['lastname'])?$this->value['lastname']:'', $_attributes);
				
		$find = array('/{n}/', '/[,.:;!?]/', '/{firstname}/', '/{prefix}/', '/{lastname}/');
		$replace = array('<br />', '', $_firstname, $_prefix, $_lastname);
		
		$this->setAttribute('input', preg_replace($find, $replace, $this->get_format()));
	}
	
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required)
		{
			$_format = $this->get_format();
							
			$_value = $this->request($this->handle);
							
			if (preg_match('/{firstname}/', $_format))
				$validator->required($_value['firstname']);
			
			if (preg_match('/{lastname}/', $_format))
				$validator->required($_value['lastname']);
		}
		return $validator->getList();
	}
	
	public function result($value = array(),  $seperator = ' ') 
	{			
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
		
		$_result = '';
		
		if (!empty($_value['firstname']) || !empty($_value['lastname'])) {	
			$find = array('/{n}/', '/[,.:;!?]/', '/{firstname}/', '/{prefix}/', '/{lastname}/');
			$replace = array(', ', 
							 '', 
							 isset($_value['firstname'])?$_value['firstname']:'', 
							 isset($_value['prefix'])?$_value['prefix']:'',  
							 isset($_value['lastname'])?$_value['lastname']:'');  
		
			$_result = trim(preg_replace($find, $replace, $this->get_format()));
		}
		$this->setAttribute('result', $_result);
	}
		
	private function get_format() 
	{
		$_format = strtolower($this->format);
		if ($_format == 'other')
			$_format = strtolower($this->format_other);
		
		return $_format;
	}	
}