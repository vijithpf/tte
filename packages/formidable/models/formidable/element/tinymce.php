<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementTinymce extends FormidableElement {
	
	public $element_text = 'TinyMCE (html)';
	public $element_type = 'tinymce';
	public $element_group = 'Special Elements';	
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'default' => array(
			'type' => 'textarea'
		),
		'required' => true,
		'appearance' => '',
		'tooltip' => true,
		'handling' => true
	);
			
	function __construct($elementID = 0) 
	{	
		parent::__construct($elementID);
		
		$this->properties['appearance'] = array(
			'simple' => t('Simple (Basic tools for html, bold, italic, lists, etc...)'),
			'advanced' => t('Advanced (Some extra options like images, links, special chars)'),
			'concrete' => t('Concrete5 (Sure, everything is possible!) (not recommended)')
		);
	}	
	
	public $dependency = array(
		'has_value_change' => false
	);
	
	public function generate()  
	{
		View::getInstance()->addFooterItem(Loader::helper('html')->javascript('tiny_mce/tiny_mce.js'));
		
		$form = Loader::helper('form');
		
		if (strpos($this->attributes['class'], 'ccm-'.$this->appearance.'-editor') === false)
			$this->attributes['class'] .= ' ccm-'.$this->appearance.'-editor';	
		
		$script = 'tinyMCE.init({ 
					mode: "textareas", 
				    width: "100%", 
					height: "150px", 
					inlinepopups_skin: "concreteMCE", 
					relative_urls : false, 
					convert_urls: false, 
				    theme: "'.$this->appearance.'",
				    theme_advanced_buttons1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,cleanup,code,charmap",   
				    theme_advanced_buttons2: \'\', 
					theme_advanced_buttons3 : \'\', 
				    theme_advanced_toolbar_location: "top", 
				    theme_advanced_toolbar_align: "left", 
				    plugins: "inlinepopups,safari,advlink", 
				    editor_selector: "ccm-'.$this->appearance.'-editor"
				   });';
				   			   
		$this->addJavascript($script);
					
		$this->setAttribute('input', $form->textarea($this->handle, $this->value, $this->attributes));
		
	}
	
	public function value($value = array())
	{			
		$editor = Loader::helper('editor', 'formidable');						
		
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
			
		parent::value(array('value' => $editor->translateTo($_value)));
	}
	
	public function result($value = array(), $seperator = ', ')
	{			
		$editor = Loader::helper('editor', 'formidable');						
		
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
		
		parent::result(!empty($_value)?array('value' => $editor->translateFromEditMode($_value)):'');
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
	
	public function callbackResult($value)
	{		
		$text = Loader::helper('text');	
		$_value = parent::callbackResult($value);		
		return $text->shorten($_value, 40);	
	}
}