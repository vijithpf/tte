<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementInteger extends FormidableElement {
	
	public $element_text = 'Integer';
	public $element_type = 'integer';
	public $element_group = 'Pre-Defined Elements';	
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
	    'default' => true,
		'placeholder' => true,						
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
		
		$this->properties['min_max'] = array(
			'value' => t('Value'), 
			'chars' => t('Characters')
		);
	}
	
	public function generate() 
	{						
		$form = Loader::helper('form');
		
		if ($this->min_max) 
		{
			if ($this->min_max_type == 'value')
			{
				$min_length = strlen($this->min_value);
				$max_length = strlen($this->max_value) - $min_length;
				
				if (Formidable::$html5)
				{
					$this->attributes['min'] = $this->min_value;
					$this->attributes['max'] = $this->max_value;
				}
			}
			elseif ($this->element['min_max_type'] == 'chars')
			{
				$min_length = intval($this->min_value);
				$max_length = intval($this->max_value) - $min_length;
				
				if (Formidable::$html5)
					if (!empty($max_length))
						$this->attributes['max'] = str_repeat('9', $max_length);
			}
			
			if (Formidable::$html5)
			{
				$this->attributes['value'] = $this->value;
				$this->attributes['step'] = 1;
				
				if (strpos($this->attributes['class'], 'counter_disabled') === false)
					$this->attributes['class'] .= ' counter_disabled';
			}
		}
		
		if (Formidable::$html5)
		{
			if (sizeof($this->attributes) > 0) 
				foreach ($this->attributes as $_name => $_value)
					$_attributes .= $_name.'="'.$_value.'" ';
			
			$_input = '<input type="number" name="'.$this->handle.'" id="'.$this->handle.'" '.$_attributes.'>';
		}
		else
		{
			$_format  = ($min_length!=0)?str_repeat('9', $min_length):'';
			$_format .= ($max_length!=0)?'?'.str_repeat('9', $max_length):'';
						
			$this->addJavascript("if ($.fn.mask) { $('#".$this->handle."').mask('".$_format."', {placeholder:''})}");

			$_input = $form->text($this->handle, $this->value, $this->attributes);	
		}
		
		$this->setAttribute('input', $_input);	 			
	}
	
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if (intval($this->request($this->handle)) > 0)
			$validator->integer($this->request($this->handle));
				
		if ($this->required)
			$validator->required($this->request($this->handle));
			
		if ($this->min_max)	
			$validator->min_max($this->request($this->handle), $this->min_value, $this->max_value, $this->min_max_type);
		
		return $validator->getList();
	}
}