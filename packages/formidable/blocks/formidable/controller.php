<?php     
defined('C5_EXECUTE') or die(_("Access Denied."));	

class FormidableBlockController extends BlockController {

	public $helpers = array('form', 
							'text', 
							'concrete/interface');
	
	protected $btInterfaceWidth = 500;
	protected $btInterfaceHeight = 300;
	protected $btTable = 'btFormidable';
	
	protected $btCacheBlockRecord = false;
	protected $btCacheBlockOutput = false;
	protected $btCacheBlockOutputOnPost = false;
	protected $btCacheBlockOutputForRegisteredUsers = false;
	protected $btCacheBlockOutputLifetime = 300;
		
	protected $form = '';
	
	public $token = '';
	
	public function getBlockTypeDescription() {
		return t("Adds a Formidable Form to you page.");
	}
	
	public function getBlockTypeName() {
		return t("Formidable");
	}		
	
	public function getJavaScriptStrings() {
		return array(
			'form-required' => t('You must select a form.')
		);
	}
	
	function __construct($obj = null) {	
		parent::__construct($obj);				
	}
	
	function on_start() {	
		parent::on_start();	
		
		Loader::model('formidable', 'formidable');			
		$f = new Formidable();			
		$this->set('forms', $f->getAllForms());
	}		
	
	function add() {		
		global $c;
		$_SESSION['formidable_current_page_id'] = $c->getCollectionID();	
	}
	
	function edit() {		
		global $c;
		$_SESSION['formidable_current_page_id'] = $c->getCollectionID();
	}
	
	public function on_page_view() 
	{			
		
	}		
	
	public function view() {						
		
		$c = Page::getCurrentPage();
		
		$hh = Loader::helper('html');			
		$ch = Loader::helper('concrete/urls');
		$pkg = Package::getByHandle('formidable');
		$valt = Loader::helper('validation/token');
		$editor = Loader::helper('editor', 'formidable');
		$CDN_URL = 'https://static-tte.s3-accelerate.dualstack.amazonaws.com';

		if (!$this->formID)
			return false;
		
		Loader::model('formidable/form', 'formidable');	
		$ff = new FormidableForm($this->formID);		
		if (!$ff->formID)
			return false;
			
		// Load jQuery UI 	
		$this->addFooterItem($hh->javascript('jquery.ui.js'));
		$this->addFooterItem($hh->css('jquery.ui.css'));
		
		$script = "<script>
					var tools_url = '".$ch->getToolsURL('formidable', 'formidable')."';
					var package_url = '".$pkg->getRelativePath()."';
					var I18N_FF = {
						\"File size now allowed\": \"".t('File size not allowed')."\",
						\"Invalid file extension.\": \"".t('Invalid file extension')."\",
						\"Max files number reached\": \"".t('Max files number reached')."\",
						\"Extension not allowed\": \"".t('Extension \"%s\" not allowed')."\",
						\"Choose State/Province\": \"".t('Choose State/Province')."\",
						\"Please wait...\": \"".t('Please wait...')."\",
						\"Allowed extensions\": \"".t('Allowed extensions')."\",
						\"Removing tag\": \"".t('Removing tag')."\"
					}
				   </script>";
		$this->addFooterItem($script);

		//$this->addFooterItem($hh->javascript('formidable.js', 'formidable'));
		$this->addFooterItem($hh->javascript($CDN_URL . '/packages/formidable/js/formidable.js', 'formidable'));
					
		if ($ff->checkLimits()) {										
			if ($ff->limit_submissions_redirect) {
				$pg = Page::getByID($ff->limit_submissions_redirect_page);
				if (is_object($pg)) {
					$this->redirect($pg->getCollectionPath());
					exit();
				}
			}
			$this->set('limit_submission', $editor->translateFromEditMode($ff->limit_submissions_redirect_content));				
		}
			
		if ($ff->checkSchedule()) {										
			if ($ff->schedule_redirect) {
				$pg = Page::getByID($ff->schedule_redirect_page);
				if (is_object($pg)) {
					$this->redirect($pg->getCollectionPath());
					exit();
				}
			}
			$this->set('schedule', $editor->translateFromEditMode($ff->schedule_redirect_content));
		}
		
		$ff->bID = $this->bID;	

		$cID = $this->request('cID');
		if (is_object($c))
			$cID = $c->getCollectionID();
		
		$ff->cID = $cID;
		$ff->token = $valt->generate('formidable_form');		
		
		// Getting all javascript and bundle...		
		if ($this->request('action') == 'reviewed_back')
			$_javascript = 'ccmFormidableDependencyFirstLoad = false;';			
		if (sizeof($ff->elements) > 0)
			foreach ($ff->elements as $element)				
				if (sizeof($element->javascript) > 0)
					foreach ($element->javascript as $_js)	
						$_javascript .= $_js.PHP_EOL;
		if (!empty($_javascript))
			$ff->javascript = $_javascript;
			
		if (sizeof($ff->elements) > 0)
			foreach ($ff->elements as $element)				
				if (sizeof($element->jquery) > 0)
					foreach ($element->jquery as $_js)	
						$_jquery .= $_js.PHP_EOL;		
		if (!empty($_jquery))
			$ff->jquery = $_jquery;
		
		// Fire event
		Events::fire('on_formidable_load', $ff);
																		
		$this->set('ff', $ff);	
	}
	
	public function reset() 
	{
		if (!$this->formID)
			return false;
			
		Loader::model('formidable/form', 'formidable');	
		$ff = new FormidableForm($this->formID);		
		if (!$ff->formID)
			return false;
		
		if (!$ff->results->delete())			
			return array('message' => t('Can\'t remove temporary data!'));
			
		unset($_SESSION['answerSetID']);
		
		return array('redirect' => View::url(Block::getByID($this->request('bID'))->getBlockCollectionObject()->getCollectionPath()));
	}
	
	public function submit() 
	{			
		$c = Page::getCurrentPage();

		if (!$this->formID)
			return false;
					
		Loader::model('formidable/form', 'formidable');	
		$ff = new FormidableForm($this->formID);		
		if (!$ff->formID)
			return false;
																	
		// Validate
		$errors = $ff->validate();
		if (count($errors['message'])) {
			if ($errors['clear'] === true)
				$this->reset_post_variables();
			return $errors;
		}
		
		// Submit
		$eh = Loader::helper('editor', 'formidable');
		
		$uID = 0;
		$u = new User();
		if ($u instanceof User) 
			$uID = $u->getUserID();
							
		$bi = $this->get_browser_info();
		$ip = $this->get_ip();
		
		// Load current page
		$cID = $this->request('cID');
		if (is_object($c))
			$cID = $c->getCollectionID();	
		
		$data = array('formID' => $ff->formID,
					  'userID' => intval($uID), 
				   	  'collectionID' => intval($cID),
					  'browser' => @implode(' ', array($bi['name'], $bi['version'])),
					  'platform' => ucfirst($bi['platform']),
					  'resolution' => $this->request('resolution'),
					  'submitted' => date("Y-m-d H:i:s"),
					  'ip' => $ip,
					  'temp' => false);
		
		if (sizeof($ff->elements) > 0) {
			foreach ($ff->elements as $element) {
				if ($element->is_layout) 
					continue;					 
				$data['elements'][$element->elementID] = array('formID' => $ff->formID,
															   'elementID' => $element->elementID,
															   'answer_formated' => $element->result,
															   'answer_unformated' => $element->serialized_value);
			}
		}
		
		if (intval($ff->review) == 1)
		{										
			if ($this->request('action') == 'reviewed_back')
				return;
				
			if ($this->request('action') == 'submit')
			{											
				$data['temp'] = true;
				if (!$ff->results->save($data))
					return array('message' => t('Can\'t save data! Please try again later'));
				
				$_SESSION['answerSetID'] = $ff->results->answerSetID;
				
				// Fire event
				Events::fire('on_formidable_review', $ff);	
				
				$this->set('review', $eh->translateFromEditMode($ff->review_content));				
				return;	
			}
		}
		
		// Move elements into a temp variable
		$all_elements = $ff->elements;
		
		// Filehandling	(move temp-files to filemanagers...)
		$ff->getElements('upload');
		$_uploads = $ff->elements;
		if (sizeof($_uploads) > 0)
		{
			Loader::library("file/importer");
			Loader::model('file_set');
			
			foreach ($_uploads as $_upload)
			{
				$_files = unserialize($data['elements'][$_upload->elementID]['answer_unformated']);
				if (is_array($_files['value']) && sizeof($_files['value']) > 0) 
				{
					$_new = '';
					foreach ($_files['value'] as $key => $file) 
					{
						$fi = new FileImporter();
						$fv = $fi->import(DIR_FILES_UPLOADED.'/tmp/formidable/'.$_upload->elementID.'/'.$file['name'], $file['name']);
						if (!$fv instanceof FileVersion) {
							$this->set('error', t('Can\'t move temporary file to filemanager'));
							return;
						}
						else
						{
							if (!empty($_upload->fileset) && !empty($_upload->fileset_value))
								$fs = FileSet::getByID(intval($_upload->fileset_value));
															
							if (!is_object($fs))
								$fs = FileSet::createAndGetSet(t('Uploaded Files'), FileSet::TYPE_PUBLIC, 1);
							$fs->addFileToSet($fv);	
							
							$file['file_id'] = $fv->getFileID();
							
							if (!@unlink(DIR_FILES_UPLOADED.'/tmp/formidable/'.$_upload->elementID.'/'.$file['name'])) {
								$this->set('error', t('Can\'t remove temporary file'));
								return;	
							}
							$_new['value'][] = $file;
						}
					}					
				}
				$files_to_mail[$_upload->elementID] = $_new['value'];
				$data['elements'][$_upload->elementID]['answer_unformated'] = serialize($_new);							
			}
		}
				
		if (!$ff->results->save($data))
			return array('message' => t('Can\'t save data! Please try again later'));
		
		// Update element submissions if there are any
		if (sizeof($all_elements) > 0) {
			foreach ($all_elements as $element) {
				$element->submissionUpdate($cID);		
			}
		}
		
		if (sizeof($ff->mailings) > 0)
		{			
			foreach ($ff->getAdvancedElements() as $_advanced)
			{
				switch ($_advanced['handle'])
				{
					case 'form_name':		$_advanced['value'] = $ff->label;									break;					
					case 'user_id':			$_advanced['value'] = $ff->callbackUser($data['userID']);			break;
					case 'collection_id':	$_advanced['value'] = $ff->callbackPage($data['collectionID']);		break;
					case 'answerset_id':	$_advanced['value'] = (string)$ff->results->answerSetID;			break;
					default:				$_advanced['value'] = (string)$data[$_advanced['handle']];			break;				
				}				
				$advanced[] = $_advanced;
			}			
			
			foreach ($ff->mailings as $mailing)
			{
				$mailing->setAttribute('advanced', $advanced);	
				$mailing->setAttribute('files_to_mail', $files_to_mail);	
				$mailing->send(true);
			}	
		}	
		
		// Fire event
		Events::fire('on_formidable_submit', $ff);
		
		unset($_SESSION['answerSetID']);
			
		// Redirect to page
		if ($ff->submission_redirect) 
		{
			$pg = Page::getByID($ff->submission_redirect_page);
			if (is_object($pg))
				return array('redirect' => View::url($pg->getCollectionPath()));
		}
				
		$this->set('submission', $eh->translateFromEditMode($ff->submission_redirect_content));			
		return; 
	}
	
	public function upload_file()
	{
		$cf = Loader::helper("file");
		$valt = Loader::helper('validation/token');
		
		Loader::library("formidable_file_importer", "formidable");
		$fi = new FormidableFileImporterLibrary();	
			
		$error = "";
		$errorCode = -1;
									
		if ($valt->validate('formidable_uploader_'.$this->request('eID'))) 
		{
			if (isset($_FILES['ax_file_input']) && (is_uploaded_file($_FILES['ax_file_input']['tmp_name']))) 
			{
				if ($this->request('ax-file-name') == 'error_doc.doc')
					$errorCode = UPLOAD_ERR_PARTIAL;
				else {
					$dir = '/tmp/formidable/'.$this->request('eID');						
					
					$i = 1;
					$file = pathinfo($cf->sanitize($this->request('ax-file-name')));				
					while(file_exists(DIR_FILES_UPLOADED.$dir.'/'.$file['filename'].'-part'.$i.'.'.$file['extension']))
						$i++;
					
					$resp = $fi->import($_FILES['ax_file_input']['tmp_name'], $file['filename'].'-part'.$i.'.'.$file['extension'], DIR_FILES_UPLOADED.$dir);
			
					if ($resp['error']!==false)
						$errorCode = $resp;
				}
			} 
			else 
				$errorCode = $_FILES['ax_file_input']['error'];
		} 
		else if (isset($_FILES['ax_file_input']))
			$error = $valt->getErrorMessage();
		else
			$errorCode = $fi->E_PHP_FILE_ERROR_DEFAULT;
		
		if ($errorCode > -1 && $error == '')
			$error = $fi->getErrorMessage($errorCode);
		
		$info = array();		
		if (strlen($error) > 0) {
			$info = array('error' => true,
						  'info' => $error,
						  'name' => basename($this->request('ax-file-name')),
						  'status' => -1);
		} else {			
			
			if ($this->request('ax-last-chunk') == 'true') {				
				$final_file_path = fopen(DIR_FILES_UPLOADED.$dir.'/'.$file['filename'].'.'.$file['extension'], "w+");			
				if ($final_file_path) {
					for ($j=1; $j<=$i; $j++) {					
						$file_chunk = DIR_FILES_UPLOADED.$dir.'/'.$file['filename'].'-part'.$j.'.'.$file['extension'];
						$in = fopen($file_chunk, "rb");
						if ($in) {
							while ($buff = fread($in, 1043)) {
								fwrite($final_file_path, $buff);
							}   
						}
						if (fclose($in)) {
							unlink($file_chunk);
						}    
					}
					fclose($final_file_path); 
				} else {
					$info = array('error' => true,
								  'info' => t('Can\'t merge chuncked files...'),
								  'name' => basename($this->request('ax-file-name')),
								  'status' => -1);	
				}
			}	
					
			$info = array('error' => false,
						  'info' => t('File succesfully uploaded'),
						  'name' => basename($file['filename'].'.'.$file['extension']),
						  'status' => 1);
			
		}	
		return $info;
	}
	
	public function save($args) {	
		unset($_SESSION['formidable_current_page_id']);
		parent::save($args);
	}
	
	private function reset_post_variables() {
		unset($_POST); 
		unset($_SESSION['answerSetID']);
	}
	
	private function get_browser_info()
	{ 
		$u_agent	= $_SERVER['HTTP_USER_AGENT']; 
		$bname		= t('Unknown');
		$platform 	= t('Unknown');
		$version	= "";
	
		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) 
			$platform = 'Linux';
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) 
			$platform = 'Mac';
		elseif (preg_match('/windows|win32/i', $u_agent)) 
			$platform = 'Windows';
		
		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
		{ 
			$bname = 'Internet Explorer'; 
			$ub = "MSIE"; 
		} 
		elseif(preg_match('/Firefox/i',$u_agent)) 
		{ 
			$bname = 'Mozilla Firefox'; 
			$ub = "Firefox"; 
		} 
		elseif(preg_match('/Chrome/i',$u_agent)) 
		{ 
			$bname = 'Google Chrome'; 
			$ub = "Chrome"; 
		} 
		elseif(preg_match('/Safari/i',$u_agent)) 
		{ 
			$bname = 'Apple Safari'; 
			$ub = "Safari"; 
		} 
		elseif(preg_match('/Opera/i',$u_agent)) 
		{ 
			$bname = 'Opera'; 
			$ub = "Opera"; 
		} 
		elseif(preg_match('/Netscape/i',$u_agent)) 
		{ 
			$bname = 'Netscape'; 
			$ub = "Netscape"; 
		} 
		
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!@preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
		
		$i = count($matches['browser']);
		if ($i != 1) {
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub))
				$version= $matches['version'][0];
			else 
				$version= $matches['version'][1];
		}
		else 
			$version= $matches['version'][0];
		
		// check if we have a number
		if ($version==null || $version=="") 
			$version="?";
		
		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern
		);
	} 
	
	private function get_ip() {
		$ip = $_SERVER['REMOTE_ADDR'];	 
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}	 
		return $ip;
	}
}
?>