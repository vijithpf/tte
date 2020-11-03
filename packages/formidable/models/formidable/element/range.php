<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementRange extends FormidableElement {
	
	public $element_text = 'Range';
	public $element_type = 'range';
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
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
		
		$this->properties['min_max'] = array(
			'value' => t('Value')
		);
	}
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	public function generate() 
	{						
		$form = Loader::helper('form');
		
		if ($this->min_max) 
		{
			$min_length = strlen($this->min_value);
			$max_length = strlen($this->max_value) - $min_length;
			
			if (Formidable::$html5)
			{
				$this->attributes['min'] = $this->min_value;
				$this->attributes['max'] = $this->max_value;
				$this->attributes['value'] = $this->value;
				
				if (strpos($this->attributes['class'], 'counter_disabled') === false)
					$this->attributes['class'] .= ' counter_disabled';
			}
		}
		
		if (Formidable::$html5)
		{
			if (sizeof($this->attributes) > 0) 
				foreach ($this->attributes as $_name => $_value)
					$_attributes .= $_name.'="'.$_value.'" ';
			
			$_input  = '<input type="range" name="'.$this->handle.'_range" id="'.$this->handle.'_range" '.$_attributes.'>';
			$_input .= '<span class="range" id="range_value">'.$this->value.'</span>';
			$_input .= $form->hidden($this->handle, $this->value);
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