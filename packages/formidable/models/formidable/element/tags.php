<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementTags extends FormidableElement {
	
	public $element_text = 'Tags';
	public $element_type = 'tags';
	public $element_group = 'Special Elements';	
		
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
		'has_value_change' => false,
		'has_placeholder_change' => false
	);
	
	function __construct($elementID = 0) 
	{				
		parent::__construct($elementID);
		
		$this->properties['min_max'] = array(
			'tags' => t('Tags'), 
		);
	}
	
	public function generate() 
	{				
		if (strpos($this->attributes['class'], 'datepicker') === false)
			$this->attributes['class'] .= ' tagselector';
				
		$form = Loader::helper('form');		
		$this->setAttribute('input', $form->text($this->handle, $this->value, $this->attributes));
		
		$_options[] = 'width:\'auto\', defaultText:\''.t('add a tag').'\'';
		if (intval($this->min_max) == 1) {
			if ($this->min_value)
				$_options[] = 'minChars:'.$this->min_value;
			
			if ($this->max_value) {
				$_options[] = 'maxChars:'.$this->max_value;	
				$_options[] = 'onChange: function() { ccmFormidableTagsCounter($(\'input[id="'.$this->handle.'"]\')); }';	
			}
		}
		
		$this->addJavascript("if ($.fn.tagsInput) { $('#".$this->handle."').tagsInput({".@implode(', ',$_options)."}); }");
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