<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementSlider extends FormidableElement {
	
	public $element_text = 'Slider (jQuery)';
	public $element_type = 'slider';
	public $element_group = 'Special Elements';	
	
	private $_spacer = ' - ';
		
	public $properties = array(
		'label' => true,
		'label_hide' => true,
	    'default' => true,
		'required' => true,
		'min_max' => '',
		'tooltip' => true,
		'handling' => true				
	);
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
		
		$this->properties['min_max'] = array(
			'value' => t('Value')
		);
		
		$this->properties['appearance'] = array(
			'horizontal_range' => t('Horizontal - Range (default)'),
			'horizontal_min' => t('Horizontal - Range with fixed minimum'),
			'horizontal_max' => t('Horizontal - Range with fixed maximum'),
			'vertical_range' => t('Vertical - Range'),
			'vertical_min' => t('Vertical - Range with fixed minimum'),
			'vertical_max' => t('Vertical - Range with fixed maximum')
		);
	}
	
	public function generate() 
	{						
		$form = Loader::helper('form');
		
		if (!$this->min_max) 
		{
			$this->min_value = 0;	
			$this->max_value = 999;	
		}
		else
			if (intval($this->max_value) == 0)
				$this->max_value = 999;	
		
		list($type, $range) = @explode('_', $this->appearance);
		if ($type == '') 
			$type = 'horizontal';
		
		if ($range == '' || $range == 'range') 
		{
			$_value = $this->value;	
			if (is_array($_value))
				$_value = @implode(',', $_value);
			
			if (empty($_value))
				$_value = intval($this->min_value).','.intval($this->max_value);
										
			$_script = '$("div#'.$this->handle.'").slider({
							orientation: "'.$type.'",
							range: true,
							min: '.$this->min_value.',
							max: '.$this->max_value.',
							values: ['.$_value.'],
							slide: function(event, ui) {											
								$("input#'.$this->handle.'").val(ui.values[0]+","+ui.values[1]);
								$("span#'.$this->handle.'").text(ui.values[0]+"'.$this->_spacer.'"+ui.values[1]);
							}
						});';
		}
		else
		{	
			$_value = intval($this->min_value); 			
			if (!empty($this->value)) {
				$_value = $this->value; 		
				if (is_array($_value))
					$_value = $_value['value'];	
			}	

			$_script = '$("div#'.$this->handle.'").slider({
							orientation: "'.$type.'",
							range: "'.$range.'",
							min: '.$this->min_value.',
							max: '.$this->max_value.',
							value: '.$_value.',
							slide: function(event,ui) {
								$("input#'.$this->handle.'").val(ui.value);
								$("span#'.$this->handle.'").text(ui.value);
							}
						});';		
		}
		
		if (strpos($this->attributes['class'], 'counter_disabled') === false)
			$this->attributes['class'] .= ' counter_disabled';
		
		if (empty($this->result))
			$this->result(array('value' => $_value));
			
		$_input .= '<div id="'.$this->handle.'" class="slider '.$type.' '.$range.' '.$this->attributes['class'].'"></div>';
		$_input .= '<span id="'.$this->handle.'" class="slider '.$type.' '.$range.' '.$this->attributes['class'].'">'.$this->result.'</span>';
		$_input .= '<input type="hidden" name="'.$this->handle.'" id="'.$this->handle.'" value="'.$_value.'" class="'.$this->attributes['class'].'">';

		$this->setAttribute('input', $_input);
		
		$this->addJavascript($_script);
	 			
	}
	
	public function validate() {
		
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		list($type, $range) = @explode('_', $this->appearance);
		if ($range == '' || $range == 'range')
			list($min, $max) = explode(',', $this->request($this->handle)); 
			
		if (strlen($this->request($this->handle)) > 0)
		{
			if ($range == '' || $range == 'range')
			{
				$validator->integer($min);
				$validator->integer($max);
			}
			else
				$validator->integer($this->request($this->handle));
		}
		
		if ($this->required)
			$validator->required($this->request($this->handle));
					
		if ($this->min_max)	
		{
			if ($range == '' || $range == 'range')
			{
				$validator->min_max($min, $this->min_value, $this->max_value, $this->min_max_type);
				$validator->min_max($max, $this->min_value, $this->max_value, $this->min_max_type);
			}
			else
				$validator->min_max($this->request($this->handle), $this->min_value, $this->max_value, $this->min_max_type);
		}

		return $validator->getList();
	}
	
	public function result($value = array(), $seperator = ', ') 
	{			
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
		
		$_result = $_value;	
		
		if (strpos($_result, ',') !== false)
			$_result = @explode(',', $_result);
		
		if (is_array($_result))
			$_result = t('%s - %s', $_result[0], $_result[1]);
						
		parent::result(!empty($_result)?array('value' => $_result):'');
	}

}