<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementRadio extends FormidableElement {
	
	public $element_text = 'Radiobuttons';
	public $element_type = 'radio';
	public $element_group = 'Basic Elements';
	
	private $_format = '<div class="radio %s">%s <label for="%s">%s</label></div>';
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,
		'options' => true,
		'option_other' => '',
		'appearance' => '',
		'tooltip' => true,
		'handling' => true
	);
		
	public $dependency = array(
		'has_value_change' => true
	);
		
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
			
		$this->properties['option_other'] = array(
			'text' => t('Single text'),
			'textarea' => t('Textarea')
		);
		
		$this->properties['appearance'] = array(
			'w100' => t('One column'),			
			'w50' => t('Two columns'),
			'w33' => t('Three columns'),
			'w25' => t('Four columns'),
			'w20' => t('Five columns'),
			'auto' => t('Automatically (let the width decide)')
		);
	}	
	
	public function generate() 
	{
		$form = Loader::helper('form');
		$text = Loader::helper('text');
		
		if (strpos($this->attributes['class'], 'counter_disabled') === false)
			$this->attributes['class'] .= ' counter_disabled';	

		if (sizeof($this->attributes) > 0) 
			foreach ($this->attributes as $_name => $_value)
				$_attributes .= $_name.'="'.$_value.'" ';
							
		$_options = unserialize($this->options);
		if (sizeof($_options) > 0) 
		{		
			for ($i=0; $i<sizeof($_options); $i++)
			{
				$_id = $text->sanitizeFileSystem($this->handle).($i+1);
				
				if (!$_options[$i]['value'])
					$_options[$i]['value'] = $_options[$i]['name'];
									
				$_checked = '';
				if (@in_array($_options[$i]['value'], (array)$this->value) || (empty($this->value) && $_options[$i]['selected'] === true))
					$_checked = 'checked="checked" ';
				
				$_radio = '<input type="radio" name="'.$this->handle.'[]" id="'.$_id.'" value="'.$_options[$i]['value'].'" '.$_checked.' '.$_attributes.'>';
				$_element .= vsprintf($this->_format, array($this->appearance, $_radio, $_id, $_options[$i]['name']));
			}
		}		
		if (intval($this->option_other) != 0)
		{
			$_checked = '';
			if (@in_array('option_other', (array)$this->value))
				$_checked = 'checked="checked" ';
							
			$_id = $text->sanitizeFileSystem($this->handle).($i+1);
			
			$_radio = '<input type="radio" name="'.$this->handle.'[]" id="'.$_id.'" value="option_other" '.$_checked.' '.$_attributes.'>';			
			$_element .= vsprintf($this->_format, array($this->appearance, $_radio, $_id, $this->option_other_value));
			
			$this->setAttribute('other', $form->{$this->option_other_type}($this->handle.'_other', $this->value_other, $this->attributes));
		}
				
		$this->setAttribute('input', $_element);
	}
		
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required)
			$validator->required($this->request($this->handle));
				
		if ($this->option_other)	
			$validator->option_other($this->request($this->handle), $this->request($this->handle.'_other'));
					
		return $validator->getList();
	}
}