<?php     defined('C5_EXECUTE') or die(_("Access Denied."));

class DashboardFormidableFormsMailingsController extends Controller {

	public $helpers = array('html',
							'text', 
							'form', 
							'form/page_selector', 
							'concrete/interface', 
							'concrete/urls', 
							'concrete/dashboard'); 
	
	public $db = '';
	
	public $mailing_data = '';
	public $mailing_error = false;
	
	public $send_to_elements = array();
	public $uploader_elements = array();
	
	private $_default_mailing = array();
		
	function __construct() 
	{			
		parent::__construct();
		
		$html = Loader::helper('html');
		$concrete_urls = Loader::helper('concrete/urls');
		$assets = Loader::helper('concrete/asset_library');	
				
		Loader::model('formidable/form', 'formidable');
		Loader::model('formidable/layout', 'formidable');
		Loader::model('formidable/element', 'formidable');
		Loader::model('formidable/mailing', 'formidable');
		
		$this->addHeaderItem($html->css('dashboard/formidable.css', 'formidable'));		
		$this->addHeaderItem($html->javascript('dashboard/common_functions.js', 'formidable'));
		$this->addHeaderItem($html->javascript('dashboard/mailings.js', 'formidable'));
		$this->addHeaderItem($html->javascript('dashboard/tinymce_integration.js', 'formidable'));
		$script = "<script>
					var attachment_counter = 10000;
					var dialog_url = '".$concrete_urls->getToolsURL('dashboard/mailings/dialog', 'formidable')."';
					var tools_url = '".$concrete_urls->getToolsURL('dashboard/mailings/tools', 'formidable')."';
					var list_url = '".$concrete_urls->getToolsURL('dashboard/forms/mailing_list', 'formidable')."';
					var title_element_overlay = '".t('Choose an element')."';
					var title_sitemap_overlay = '".t('Choose a page')."';
					var title_message_add = '".t('Add mailing to Formidable Form')."';
					var title_message_edit = '".t('Edit mailing from Formidable Form')."';
					var message_save = '".t('Mailing successfully saved!')."';
					var message_duplicate = '".t('Mailing successfully duplicated!')."';
					var message_delete = '".t('Mailing successfully deleted!')."';
					var attachment_default = \"".str_replace('"', '\"', $assets->file('attachment_counter_tmp', 'attachment[counter_tmp]', t('Choose file')))."\";
					$(function() {
						ccmFormidableLoadMailings();	
					});
				   </script>";
		$this->addHeaderItem($script);
	
		$ui = UserInfo::getByID(USER_SUPER_ID);
		$this->_default_mailing = array('from_type' => 'other',
									    'from_name' => SITE,
									    'from_email' => $ui->getUserEmail(),
									    'reply_email' => $ui->getUserEmail(),
									    'send_custom' => 1,
									    'send_custom_value' => $ui->getUserEmail(),
									    'subject' => t('%s submission', SITE),
									    'message' => sprintf('<p>%s<br />%s</p><p>%s</p><p>%s</p><p>%s</p><p>%s</p>',
															 t('You succesfully sent our %s on our Concrete5 website.', SITE),
															 t('The following information was sent to us:'),
															 '{%all_elements%}',
															 t('Thank you!'),
															 t('Regards,'),
															 SITE));		
	}
		
	public function view($formID = '') 
	{		
		$f = new FormidableForm($formID);
		if (!$f->formID)
			$this->redirect('/dashboard/formidable/forms', 'message', 'notfound');
								
		$this->set('f', $f);		
	}	
	
	public function get_mailings()
	{
		$f = new FormidableForm($this->request('formID'));		
		if (!$f->formID)
			return false;
				
		return $f->mailings;	
	}

	public function get_templates() {
		$templates = array();

		$db = Loader::db();
		$results = $db->getAll("SELECT * FROM FormidableTemplates");
		if (count($results)) {
			foreach ($results as $r) {
				$templates[$r['templateID']] = $r['label'];
			}
		}
		return $templates;
	}
	
	public function get_mailing()
	{		
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return;
		
		$mailing = new stdClass();		
		if (intval($this->request('mailingID')) != 0)
			$mailing = $f->mailings[$this->request('mailingID')];	
		
		// Load default mailing if non is selected
		if (!$mailing->mailingID) {
			$mailing->formID = $f->formID;
			foreach ((array)$this->_default_mailing as $key => $value)
				$mailing->{$key} = $value;
		}
		
		if (sizeof($mailing->attachments) < 1)
			$mailing->attachments = array(array('fileID' => ''));
		
		$mailing->send_to = (array)$this->get_send_to_elements();
		$mailing->from = $mailing->send_to + array('other' => t('Send from custom sender:'));		
		$mailing->reply_to = array('from' => t('Use the "From"-details')) + $mailing->send_to + array('other' => t('Use custom "Reply to"-details:'));		
		$mailing->uploader_elements = $this->get_uploader_elements();
				
		return $mailing;
	}
	
	
	public function get_send_to_elements() 
	{			
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return;
			
		$f->getElements('send_to');
		if (sizeof($f->elements) > 0)
			foreach ($f->elements as $elementID => $element)
				$el[$elementID] = $element->label.' ('.$element->element_text.')';
		
		return $el;
	}
	
	public function get_uploader_elements() 
	{			
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return;
			
		$f->getElements('upload');		
		if (sizeof($f->elements) > 0)
			foreach ($f->elements as $elementID => $element)
				$el[$elementID] = $element->label.' ('.$element->element_text.')';
		
		return $el;
	}
	
	public function get_elements()
	{
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return;
			
		$rows = $f->layouts;		
		if (sizeof($rows) > 0) 
			foreach ($rows as $layouts) 						
				if (sizeof($layouts) > 0) 
					foreach ($layouts as $layout) 
						if (sizeof($layout->elements) > 0) 
							foreach ($layout->elements as $element) 
								$_elements[] = $element;	
		
		return $_elements;	
	}	
	
	public function get_advanced()
	{
		$f = new Formidable();
		return $f->getAdvancedElements();
	}
	
	public function validate() 
	{	
		$m = new FormidableMailing();								
		return $m->validateProperties();
	}
		
	public function save() 
	{			
		$text = Loader::helper('text');
		$editor = Loader::helper('editor', 'formidable');
		
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return;
		
		$m = new FormidableMailing($this->request('mailingID'));
						
		$data = $this->request();
		
		if (sizeof($data['attachment']) > 0)
			foreach($data['attachment'] as $attachment)
				if (intval($attachment) != 0)
					$data['attachments'][] = $attachment; 	
				
		$v = array('formID' => $f->formID,
				   'mailingID' => intval($data['mailingID']),
				   'from_type' => $data['from_type'],
				   'from_name' => $data['from_name'],
				   'from_email' => $data['from_email'],
				   'reply_type' => $data['reply_type'],
				   'reply_name' => $data['reply_name'],
				   'reply_email' => $data['reply_email'],
				   'send' => @implode(',', $data['send']),
				   'send_custom' => intval($data['send_custom']),
				   'send_custom_value' => $data['send_custom_value'],
				   'send_cc' => intval($data['send_cc']),
				   'subject' => $data['subject'],
				   'template' => intval($data['template']),
				   'templateID' => intval($data['templateID']),
				   'message' => $editor->translateTo($data['message']),
				   'discard_empty' => intval($data['discard_empty']),
				   'discard_layout' => intval($data['discard_layout']),
				   'attachments' => @implode(',',$data['attachments']),
				   'attachments_element' => intval($data['attachments_element']),
				   'attachments_element_value' => @implode(',', $data['attachments_element_value']));
						
		$m->save($v);
		return true;
	}
	
	public function duplicate()
	{
		$m = new FormidableMailing($this->request('mailingID'));			
		if (!is_object($m))
			return false;
		
		return $m->duplicate();
	}
	
	public function delete() 
	{
		$m = new FormidableMailing($this->request('mailingID'));			
		if (!is_object($m))
			return false;
		
		return $m->delete();
	}
	
	public function message($mode = 'error', $formID) 
	{
		switch($mode) 
		{
			case 'notfound':	$this->set('error', 	t('Form or mailing can\'t be found!'));		break;
			case 'error':		$this->set('error', 	t('Oops, something went wrong!'));			break;
			case 'saved':		$this->set('message', 	t('Mailing saved successfully'));			break;
			case 'deleted':
			default:			$this->set('message', 	t('Mailing deleted successfully'));			break;
		}
		$this->view($formID);
	}
}