<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable/element', 'formidable');

class FormidableElementFileupload extends FormidableElement {
	
	public $element_text = 'File(s) Upload';
	public $element_type = 'fileupload';
	public $element_group = 'Special Elements';	
	
	// Max 25 files can be uploaded per element.
	private $max_uploaded_files = 25;
	
	private $preview_extensions = array('jpg', 'png', 'gif');
	
	public $properties = array(
		'label' => true,
		'label_hide' => true,
		'required' => true,
		'allowed_extensions' => 'jpg, gif, jpeg, png, tiff, docx, doc, xls, xlsx, csv, pdf, zip',
		'fileset' => true,
		'min_max' => '',
		'tooltip' => true,
		'handling' => false
	);
	
	public $dependency = array(
		'has_value_change' => false
	);
			
	function __construct($elementID = 0) 
	{	
		parent::__construct($elementID);
		
		$this->properties['min_max'] = array(
			'files' => t('Files')
		);
	}	
	
	public function generate()  
	{
		$pkg = Package::getByHandle('formidable');	
		
		View::getInstance()->addFooterItem(Loader::helper('html')->javascript($pkg->getRelativePath().'/libraries/3rdparty/ajaxupload/js/ajaxupload.js'));
		
		$language = 'auto';
		
		if (Config::get('SITE_LOCALE'))		
			$language = Config::get('SITE_LOCALE');					
		
		if (Package::getByHandle('multilingual')) {
			$ms = MultilingualSection::getByLocale($_SESSION['LOCALE']);		
			if (is_object($ms)) {
				$language = $ms->getLocale();
			}			
		}
				
		$form = Loader::helper('form');
		$form = Loader::helper('form');
		$image = Loader::helper('image');
		$valt = Loader::helper('validation/token');
				
		if (strpos($this->attributes['class'], 'ccm_formidable_upload') === false)
			$this->attributes['class'] .= ' ccm_formidable_upload';	
		
		$this->attributes['data-uploaderID'] = $this->elementID;
		$this->attributes['data-uploaderName'] = $this->handle;
		
		if (!$this->min_max)				
			$this->max_value = $this->max_uploaded_files;
		
		$allowed_ext = '';
		if ($this->allowed_extensions)	
			$allowed_ext = @implode(', ', (array)@explode(',', preg_replace(array('/ /', '/\*\./', '/\./'), '', $this->allowed_extensions_value)));
				
		$this->attributes['data-min_files'] = intval($this->max_value);
		$this->attributes['data-max_files'] = intval($this->max_value);
		$this->attributes['data-current_files'] = is_array($this->value)?sizeof($this->value):0;
		$this->attributes['data-ccm_token'] = $valt->generate('formidable_uploader_'.$this->elementID);
		$this->attributes['data-allowed_extensions'] = $allowed_ext;
		$this->attributes['data-allowed_global_extensions'] = @implode(', ', (array)@explode(';', preg_replace(array('/ /', '/\*\./', '/\./'), '', UPLOAD_FILE_EXTENSIONS_ALLOWED)));
		
		if (!empty($language)) 
			$this->attributes['data-language'] = $language;		
			
		if (sizeof($this->attributes) > 0) 
			foreach ($this->attributes as $_name => $_value)
				$_attributes .= $_name.'="'.$_value.'" ';
				
		$_input  = '<div id="formidable_uploader_'.$this->elementID.'" '.$_attributes.' name="'.$this->handle.'"></div>';
		//$_input .= '<div id="formidable_uploader_'.$this->elementID.'_drop" class="'.$this->attributes['class'].' drop_area"></div>';
		
		if (!empty($this->value))
		{
			$i = 0;
			$_input .= '<div class="ax-uploader" id="formidable_uploaded_files_'.$this->elementID.'">';
			$_input .= '<fieldset>';
			$_input .= '<ul class="ax-file-list">';
			foreach ((array)$this->value as $file)
			{
				$_input .= '<li id="file_'.$i.'">';
				$_input .= '<a class="ax-prev-container ax-filetype-'.$file['ext'].'">';
				if ($file['ext'] == 'jpg' || $file['ext'] == 'gif' || $file['ext'] == 'png')
					$_input .= $image->outputThumbnail(DIR_FILES_UPLOADED.'/tmp/formidable/'.$this->elementID.'/'.$file['name'], 40, 40, '', true); 
				$_input .= '</a>';
				$_input .= '<div class="ax-details">';
				$_input .= '<div class="ax-file-name">'.$file['name'].'</div>';
				$_input .= '<div class="ax-file-size">'.$this->convert_size($file['size']).'</div>';
				$_input .= '</div>';
				$_input .= '<div class="ax-toolbar">';
				$_input .= '<a href="javascript:;" class="ax-file-remove" onclick="ccmFormidableUploaderDropFile('.$this->elementID.', '.$i.');"><span>'.t('Delete').'</span></a>';
				$_input .= '</div>';
				$_input .= $form->hidden($this->handle.'[999'.$i.'][name]', $file['name']);
				$_input .= $form->hidden($this->handle.'[999'.$i.'][ext]', $file['ext']);
				$_input .= $form->hidden($this->handle.'[999'.$i.'][size]', $file['size']);
				$_input .= '</li>';
				$i++;
			}
			$_input .= '</ul>';
			$_input .= '</fieldset>';
			$_input .= '</div>';
		}
		
		$this->addJavascript("if(!window.ccmFormInited".$this->elementID." && typeof window.ccmFormidableUploaderInit == 'function') { window.ccmFormInited".$this->elementID." = true; ccmFormidableUploaderInit('".$this->elementID."'); }");	
        
		$this->setAttribute('input', $_input);
		
	}
	
	public function value($value = array())
	{			
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
		
		if (!empty($_value)) 
			foreach ((array)$_value as $file) 
				$files[] = array('name' => $file['name'],
								 'ext' => $file['ext'],
								 'size' => $file['size'],
								 'file_id' => $file['file_id']);
								 		
		parent::value(array('value' => $files));
	}
	
	public function result($value = array(), $seperator = ', ')
	{			
		$ih = Loader::helper('image');
		
		$_value = $this->value;
		if (!empty($value))
			$_value = $value['value'];
			
		if (!empty($_value)) {
			foreach ((array)$_value as $file) {
				if (intval($file['file_id']) != 0) {
					$f = File::getByID($file['file_id']);
					if (is_object($f)) {
						if (in_array($file['ext'], $this->preview_extensions))
							$files[] = '<div class="upload_preview" data_fID="'.$f->getFileID().'">
											<a href="javascript:;">'.$ih->outputThumbnail($f, 50, 50, $file['name'], true).'</a>
											<a href="'.$f->getForceDownloadURL().'">'.$file['name'].'</a> ('.$this->convert_size($file['size']).')										
											<div class="upload_preview-hover" data_fID-hover="'.$f->getFileID().'">
												<div>'.$ih->outputThumbnail($f, 300, 300, $file['name'], true).'</div>
											</div>
										</div>';
						else
							$files[] = '<a href="'.$f->getForceDownloadURL().'">'.$file['name'].'</a> ('.$this->convert_size($file['size']).')';	
					}
				} else
					$files[] = $file['name'].' ('.$this->convert_size($file['size']).')';	
			}
		} 		
		parent::result(!empty($files)?array('value' => @implode('<br />', $files)):'');
	}
	
	public function callbackResult($value)
	{		
		$_value = unserialize($value);
		if (!empty($_value['value'])) {
			if (sizeof($_value['value']) > 1)
				return t('%s uploaded files', sizeof($_value['value']));
			else {
				$f = File::getByID($_value['value'][0]['file_id']);
				if (is_object($f))
					return '<a href="'.$f->getForceDownloadURL().'">'.$_value['value'][0]['name'].'</a> ('.$this->convert_size($_value['value'][0]['size']).')';
			}
		}
		return '';	
	}
	
	public function callbackResultExport($value) 
	{
		$_result = '';
		
		$_value = unserialize($value);
		if (!empty($_value['value'])) {
			foreach ($_value['value'] as $_file) {
				$f = File::getByID($_file['file_id']);
				if (is_object($f))
					$_result .= '<a href="'.$f->getForceDownloadURL().'">'.$_file['name'].'</a><br />';
			}
		}
		return $_result;	
	}
		
	public function validate() 
	{
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidator($this->elementID, $this->label, $this->dependency_validation);
		
		if ($this->required)
			$validator->required($this->request($this->handle));
			
		if ($this->min_max)	
			$validator->min_max($this->request($this->handle), $this->min_value, $this->max_value, $this->min_max_type);
		
		if ($this->allowed_extensions)	
			$validator->allowed_extensions($this->request($this->handle), $this->allowed_extensions_value);

		return $validator->getList();
	}
	
	private function convert_size($size, $sep = ' ')
	{ 		
		$unit = null;
		$units = array('B', 'KB', 'MB', 'GB', 'TB');	 
		for($i = 0, $c = count($units); $i < $c; $i++) {
			if ($size > 1024)
				$size = $size / 1024;
			else {
				$unit = $units[$i];
				break;
			}
		}	 
		return round($size, 2).$sep.$unit;	
	}
	
}