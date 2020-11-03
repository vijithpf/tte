<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementRating extends FormidableElement {
	
	public $element_text = 'Rating (stars)';
	public $element_type = 'rating';
	public $element_group = 'Special Elements';	
	
	private $_format = '<div class="rating %s">%s<div id="stars-cap-%s"></div></div>';
			
	public $properties = array(
		'label' => true,
		'label_hide' => true,
	    'default' => true,
		'placeholder' => true,
	    'mask' => '',
	    'required' => true,
	    'min_max' => '',
	    'tooltip' => true
	);
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
		
		$this->properties['appearance'] = array(
			3 => '3 '.t('stars'),
			5 => '5 '.t('stars'),
			10 => '10 '.t('stars')
		);
	}
	
	public function generate() 
	{				
		$form = Loader::helper('form');
		
		if (strpos($this->attributes['class'], 'stars') === false)
				$this->attributes['class'] .= ' stars';
		
		if (sizeof($this->attributes) > 0) 
			foreach ($this->attributes as $_name => $_value)
				$_attributes .= $_name.'="'.$_value.'" ';
	
		for ($i=1; $i<=$this->appearance; $i++)
		{			
			$_checked = '';
			if ($i == $this->value)
				$_checked = 'checked';
			
			$_input .= '<input type="radio" name="'.$this->handle.'" id="'.$this->handle.'_'.$i.'" value="'.$i.'" '.$_checked.' '.$_attributes.'>';	
		}		
				
		$this->setAttribute('input', vsprintf($this->_format, array($this->attributes['class'], $_input, $this->handle)));
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
	
	public function result($value = array(), $seperator = ', ') 
	{			
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
		
		$_result = t('%s of %s', $_value, $this->appearance);
						
		parent::result(!empty($_result)?array('value' => $_result):'');
	}
}