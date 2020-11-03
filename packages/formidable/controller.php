<?php    

	defined('C5_EXECUTE') or die(_("Access Denied."));
	
	class FormidablePackage extends Package {
	
		protected $pkgHandle = 'formidable';
		protected $appVersionRequired = '5.6.0';
		protected $pkgVersion = '2.1.3';
		
		protected $elements = array();

		protected $singlePages = array(
			'/dashboard/formidable',
			array('/dashboard/formidable/forms', 'icon-tasks'),
			'/dashboard/formidable/forms/elements',
			'/dashboard/formidable/forms/mailings',
			array('/dashboard/formidable/results', 'icon-briefcase'),
			array('/dashboard/reports/formidable', 'icon-briefcase'),
			array('/dashboard/formidable/templates', 'icon-eye-open'),
		);

		protected $blocks = array(
			'formidable',
			'results'
		);

		
		public function getPackageDescription() {
			return t('Create awesome forms with a few clicks!');
		}
		
		public function getPackageName() {
			return t('Formidable');
		}
				
		public function install() {		
			$pkg = parent::install();			
			$this->setupPages();
            $this->setupBlocks();

            Loader::model("job");
			Job::installByPackage("remove_temp_submissions", $pkg);
			
			Loader::model('system/captcha/library');
			SystemCaptchaLibrary::add('calculation', t('Calculation Captcha'), $pkg);
            
            $this->set_indexes();
        }
		
		
		public function upgrade() {

			$db = Loader::db();
			$db->execute('DROP TABLE FormidableSavedSearches');

			$this->remove_fieldset_elements();
			
			$this->grab_element_parameters();
						
			$pkg = parent::upgrade();
			$this->element_to_layout();
			$this->convert_answers();
			
			$this->save_element_parameters();
            
            $this->set_indexes();

            $this->setupPages();
            $this->setupBlocks();
		}
			
		public function uninstall() {		
			parent::uninstall();
			
			$db = Loader::db();
				
			$db->execute('DROP TABLE FormidableForms');
			$db->execute('DROP TABLE FormidableFormElements');
			$db->execute('DROP TABLE FormidableFormMailings');
			$db->execute('DROP TABLE FormidableAnswerSets');
			$db->execute('DROP TABLE FormidableAnswers');
			$db->execute('DROP TABLE FormidableSavedSearches');
			$db->execute('DROP TABLE FormidableFormLayouts');
            
			$db->execute('DROP TABLE btFormidable');
			$db->execute('DROP TABLE btFormidableResults');

			//Uninstall Formidable Importer if exists...
			$fi = Package::getByHandle('formidable_importer');
			if (is_object($fi))
				$fi->unistall();
		}
		
		private function setupBlocks() {

			$pkg = Package::getByHandle($this->pkgHandle);
			
			foreach ((array)$this->blocks as $b) {				
				$block = BlockType::getByHandle($b);
				if (!$block) {
					BlockType::installBlockTypeFromPackage($b, $pkg);
				}
			}
		}

		private function setupPages() {

			$pkg = Package::getByHandle($this->pkgHandle);

			Loader::model('single_page');
			
			foreach ((array)$this->singlePages as $sp) {				
				$sp_path = $sp;
				if (is_array($sp))
					$sp_path = $sp[0];

				if(Page::getByPath($sp_path)->getCollectionID() <= 0) {			
					SinglePage::add($sp_path, $pkg);
					$spo = Page::getByPath($sp_path);
					if (!empty($sp[1]))
						$spo->setAttribute('icon_dashboard', $sp[1]);
				}
			}
		}
			
        private function set_indexes() {

			$db = Loader::db();

			$db->execute("ALTER TABLE FormidableForms ADD UNIQUE (formID)");
			$db->execute("ALTER TABLE FormidableForms ADD INDEX (sort)");
            
            $db->execute("ALTER TABLE FormidableFormElements ADD UNIQUE (elementID)");
			$db->execute("ALTER TABLE FormidableFormElements ADD INDEX (formID)");
            $db->execute("ALTER TABLE FormidableFormElements ADD INDEX (layoutID)");
            
            $db->execute("ALTER TABLE FormidableFormLayouts ADD UNIQUE (layoutID)");
            $db->execute("ALTER TABLE FormidableFormLayouts ADD INDEX (formID)");
            $db->execute("ALTER TABLE FormidableFormLayouts ADD INDEX (rowID)");
            
            $db->execute("ALTER TABLE FormidableFormMailings ADD UNIQUE (mailingID)");
            $db->execute("ALTER TABLE FormidableFormMailings ADD INDEX (formID)");
            
			$db->execute("ALTER TABLE FormidableAnswerSets ADD UNIQUE (answerSetID)");
            $db->execute("ALTER TABLE FormidableAnswerSets ADD INDEX (formID)");
            $db->execute("ALTER TABLE FormidableAnswerSets ADD INDEX (userID)");
            $db->execute("ALTER TABLE FormidableAnswerSets ADD INDEX (collectionID)");

			$db->execute("ALTER TABLE FormidableAnswers ADD INDEX (answerSetID)");
			$db->execute("ALTER TABLE FormidableAnswers ADD INDEX (formID)");
            $db->execute("ALTER TABLE FormidableAnswers ADD INDEX (elementID)");
            
            $db->execute("ALTER TABLE FormidableSavedSearches ADD INDEX (searchID)");
            $db->execute("ALTER TABLE FormidableSavedSearches ADD INDEX (uID)");
    	}

        
		private function element_to_layout() 
		{			
			$db = Loader::db();
			
			Loader::model('formidable', $this->pkgHandle);
			$f = new Formidable();
			
			$forms = $f->getAllForms();
			if (sizeof($forms) > 0) {
				foreach($forms as $formID => $label) {					
					$exists = $db->getOne("SELECT * 
										   FROM FormidableFormLayouts 
										   WHERE formID = ?", array($formID));
					if (!$exists){				
						$layout = $db->execute("INSERT INTO FormidableFormLayouts (formID, rowID, sort)
												VALUES (?,?,?)", array($formID, 0, 0));
						$layoutID = $db->Insert_ID();
					 
						$elements = $db->execute("UPDATE FormidableFormElements
											 	  SET layoutID = ?
											 	  WHERE formID = ?", array($layoutID, $formID));
					}
				}
			}
		}
		
		private function is_serialized($string) 
		{
			return @unserialize($string)!==false ? true : false;
		}
		
		private function convert_answers() 
		{
			$db = Loader::db();
			
			$answers = $db->getAll("SELECT *
									FROM FormidableAnswers
									WHERE answer_unformated != 'null' 
									AND answer_unformated != ''");
			if (sizeof($answers) > 0) {
				foreach($answers as $answer) {
					if (unserialize($answer['answer_unformated']) !== false) {
						$old_data = unserialize($answer['answer_unformated']);															
						$data = array_key_exists('value', (array)$old_data)?$old_data['value']:$old_data;						
						$db->query("UPDATE FormidableAnswers
									SET answer_unformated = ?
									WHERE elementID = ?
									AND formID = ?
									AND answerSetID = ?", array(serialize(array('value' => $data)), 
																$answer['elementID'],
																$answer['formID'],
																$answer['answerSetID']));
					}
					else {
						$old_data = json_decode($answer['answer_unformated'], true);
						$data = array_key_exists('value', (array)$old_data)?$old_data['value']:$old_data;						
						$db->query("UPDATE FormidableAnswers
									SET answer_unformated = ?
									WHERE elementID = ?
									AND formID = ?
									AND answerSetID = ?", array(serialize(array('value' => $data)), 
																$answer['elementID'],
																$answer['formID'],
																$answer['answerSetID']));
					}
				}
			}
		}
		
		private function grab_element_parameters() 
		{
			$db = Loader::db();
			$elements = $db->getAll("SELECT *
									 FROM FormidableFormElements");
			if (sizeof($elements) > 0) {
				foreach($elements as $element) {
					if (empty($element['params'])) {
						$params = array('placeholder' => intval($element['placeholder']),
										'placeholder_value' => $element['placeholder_value'],
										'default_value' => intval($element['default_value']),
										'default_value_value' => $element['default_value_value'],
										'tinymce_value' => $element['tinymce_value'],
										'html_code' => $element['html_code'],
										'content' => ($element['element_type']=='paragraph')?$element['default_value_value']:'',
										'required' => intval($element['required']),
										'min_max' => intval($element['min_max']),
										'min_value' => intval($element['min_value']),
										'max_value' => intval($element['max_value']),
										'min_max_type' => $element['min_max_type'],	
										'confirmation' => intval($element['confirmation']),	
										'chars_allowed' => intval($element['chars_allowed']),
										'chars_allowed_value' => $element['chars_allowed_value'],			   
										'mask' => intval($element['mask']),
										'mask_format' => $element['mask_format'],
										'tooltip' => intval($element['tooltip']),
										'tooltip_value' => $element['tooltip_value'],
										'options' => $element['options'],
										'option_other' => intval($element['option_other']),
										'option_other_value' => $element['option_other_value'],
										'option_other_type' => $element['option_other_type'],
										'multiple' => intval($element['multiple']),
										'format' => $element['format'],
										'format_other' => $element['format_other'],
										'appearance' => $element['appearance'],				   
										'advanced' => intval($element['advanced']),
										'advanced_value' => $element['advanced_value'],
										'file_handling' => $element['file_handling'],
										'allowed_extensions' => intval($element['allowed_extensions']),
										'allowed_extensions_value' => $element['allowed_extensions_value'],
										'fileset' => intval($element['fileset']),
										'fileset_value' => intval($element['fileset_value']),
										'css' => intval($element['css']),
										'css_value' => $element['css_value']);
					
						$this->elements[$element['elementID']] = Loader::helper('json')->encode($params);
					}
				}
			}
		}
		
		private function save_element_parameters() {
			
			$db = Loader::db();
			if (sizeof($this->elements) > 0) {
				foreach ($this->elements as $elementID => $params) {
					if (!empty($params)) {
						$db->execute("UPDATE FormidableFormElements
									  SET params = ?
									  WHERE elementID = ?", array($params, $elementID));	
					}
				}
			}
		}
		
		private function remove_fieldset_elements() 
		{			
			$db = Loader::db();			
			
			$db->execute("DELETE FROM FormidableFormElements
						  WHERE element_type = ? OR element_type = ?", array('fieldset', 'fieldset_end'));
		}
		
	}

?>
