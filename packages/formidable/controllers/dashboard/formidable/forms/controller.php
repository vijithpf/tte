<?php     defined('C5_EXECUTE') or die(_("Access Denied."));

class DashboardFormidableFormsController extends Controller {

	public $helpers = array('html',
							'text', 
							'form', 
							'form/page_selector', 
							'validation/form', 
							'concrete/interface', 
							'concrete/urls', 
							'concrete/dashboard'); 

	private $_label = '';
	private $_required_fields_label = '';	
	private $_clear_button_label = '';
	private $_submit_button_label = '';	
	private $_review_label = '';
	private $_review_content = '';	
	private $_submission_redirect_content = '';	
	private $_mail_subject = '';
	private $_html5 = true;
	public $limit_submissions_types = '';
	private $_limit_submissions_redirect_content = '';
	private $_schedule_redirect_content = '';		
	private $_default_mailing = '';
	
	function __construct() 
	{	
		parent::__construct();
	
		$this->_label = t('My Formidable Form');
		$this->_required_fields_label = t('Required fields');
		$this->_clear_button_label = t('Clear form');
		$this->_submit_button_label = t('Submit now!');	
		$this->_review_label = t('Review your submission');
		$this->_review_content = t('Please check the information you want to send.');	
		$this->_submission_redirect_content = t('Thank you!');
		$this->_limit_submissions_redirect_content = t('You have reached the maximum submissions of the form.');
		$this->_schedule_redirect_content = t('The form is currently unavailable.');	
		$this->_mail_subject = t('Form succesfully submitted');
		
		$this->limit_submissions_types = array('' => '',
											   'total' => t('Total submissions'),
											   'ip' => t('Per IP-address'),
											   'user' => t('Per user (guest-visitors excluded)'));
		
		// Default mailing on creating ne form		
		$ui = UserInfo::getByID(USER_SUPER_ID);
				
		$this->_default_mailing = array('mailingID' => 0,
									    'from_type' => 'other',
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
				
		Loader::model('formidable/form', 'formidable');
				
		$html = Loader::helper('html');
		$curl = Loader::helper('concrete/urls');	

		$this->addHeaderItem($html->css('dashboard/formidable.css', 'formidable'));		
		$this->addHeaderItem($html->javascript('dashboard/common_functions.js', 'formidable'));
		$this->addHeaderItem($html->javascript('dashboard/forms.js', 'formidable'));
		$script = "<script>
					var editor_url = '".$curl->getToolsURL('dashboard/editor', 'formidable')."';
					var edit_content = '".t('Edit content')."';
					var add_content = '".t('Add content')."';
					var changed_values = '".t('You have made some changes to the Form Properties. Are you sure you want to discard these changes?')."';
				   </script>";
		$this->addHeaderItem($script);
	}
		
	public function view() 
	{	
		$fl = new FormidableFormList();
		$fl->setItemsPerPage(999);
		
		$offset = 0;
		$list = $fl->get(999, $offset);
		if (sizeof($list) > 0)
			for ($i=0; $i<sizeof($list); $i++)
				$forms[] = new FormidableForm($list[$i]['formID']);	

		$this->set('forms', $forms);
		$this->set('form_list', $fl);
	}	
				
	public function add() 
	{	
		$f = new FormidableForm();	
		
		$f->required_fields_label = $this->_required_fields_label;
		
		$f->clear_button_label = $this->_clear_button_label;
		$f->submit_button_label = $this->_submit_button_label;
		
		$f->review_label = $this->_review_label;
		$f->review_content = '<p>'.$this->_review_content.'</p>';
		
		$f->submission_redirect_content = '<p>'.$this->_submission_redirect_content.'</p>';
		
		$f->limit_submissions_redirect_content = '<p>'.$this->_limit_submissions_redirect_content.'</p>';
		
		$f->schedule_redirect_content = '<p>'.$this->_schedule_redirect_content.'</p>';
		
		$f->html5 = $this->_html5;
						
		$this->set('f', $f);
		$this->set('create_form', true);
	}
	
	public function edit($id, $form_new = false) 
	{			
		if ($form_new)
			$this->set('message', t('Form saved successfully'));
								
		$f = new FormidableForm($id);		
		if ($f->formID) {		
			$editor = Loader::helper('editor', 'formidable');
			
			$f->review_content = $editor->translateFromEditMode($f->review_content);
			$f->submission_redirect_content = $editor->translateFromEditMode($f->submission_redirect_content);
			
			$f->limit_submissions_redirect_content = $editor->translateFromEditMode($f->limit_submissions_redirect_content);			
			
			$f->schedule_redirect_content = $editor->translateFromEditMode($f->schedule_redirect_content);		
			
			$this->set('f', $f);		
			$this->set('create_form', true);
		}
		else {
			$this->message('notfound');
			$this->view();
		}
	}
	
	public function preview($id) 
	{			
		$f = new FormidableForm($id);		
		if ($f->formID) {
			$this->set('f', $f);
			$this->set('preview_form', true);
		}
		else {
			$this->message('notfound');
			$this->view();
		}
			
	}
	
	public function duplicate($id)
	{
		$f = new FormidableForm($id);
		$message = $f->duplicate();
		$this->redirect('/dashboard/formidable/forms', 'message', 'duplicated');	
	}
	
	public function delete($id) 
	{
		$f = new FormidableForm($id);
		$message = $f->delete();
		$this->redirect('/dashboard/formidable/forms', 'message', 'deleted');
	}

	public function save() 
	{		
		$val = new ValidationFormHelper();	
		$val->setData($this->post());
		
		$val->addRequired('label', t('Field "%s" is invalid', t('From name')));
						
		if ($this->post('captcha'))
			$val->addRequired('captcha_label', t('Field "%s" is invalid', t('Captcha label')));
			
		$val->addRequired('submit_button_label', t('Field "%s" is invalid', t('Submit button label')));
		
		if ($this->post('clear_button'))
			$val->addRequired('clear_button_label', t('Field "%s" is invalid', t('Clear button label')));

		if ($this->post('review'))
			$val->addRequired('review_content', t('Field "%s" is invalid', t('Description (review)')));
		
		if ($this->post('limit_submissions'))
		{
			$val->addInteger('limit_submissions_value', t('Field "%s" is invalid number', t('Limit (value)')));
			$val->addRequired('limit_submissions_type', t('Field "%s" is invalid', t('Limit (type)')));
			
			if (intval($this->post('limit_submissions_redirect')) == 0)
				$val->addRequired('limit_submissions_redirect_content', t('Field "%s" is invalid', t('Message (limit submissions)')));
			else
				$val->addRequired('limit_submissions_redirect_page', t('Field "%s" isn\'t selected', t('Page (limit submissions)')));
		}
		
		if ($this->post('schedule'))
		{			
			if ($this->post('schedule_start_activate'))
				$val->addRequired('schedule_start_dt', t('Field "%s" is invalid date', t('Start date (schedule)')));
			
			if ($this->post('schedule_end_activate'))
				$val->addRequired('schedule_end_dt', t('Field "%s" is invalid date', t('End date (schedule)')));
			
			if (intval($this->post('schedule_redirect')) == 0)
				$val->addRequired('schedule_redirect_content', t('Field "%s" is invalid', t('Message (schedule)')));
			else
				$val->addRequired('schedule_redirect_page', t('Field "%s" isn\'t selected', t('Page (schedule)')));
		}
		
		if ($this->post('css'))
			$val->addRequired('css_value', t('Field "%s" is invalid', t('CSS value')));
		
		if ($val->test()) 
		{			
			$editor = Loader::helper('editor', 'formidable');
			$date_time = Loader::helper('form/date_time');
			
			$v = array('formID' => $this->post('formID'),
					   'label' => $this->post('label'), 
					   'captcha' => intval($this->post('captcha')), 
					   'captcha_label' => $this->post('captcha_label'), 
					   'clear_button' => intval($this->post('clear_button')), 
					   'clear_button_label' => $this->post('clear_button_label'),
					   'submit_button_label' => $this->post('submit_button_label'),
					   'review' => intval($this->post('review')), 
					   'review_content' => $editor->translateTo($this->post('review_content')),
					   'submission_redirect' => intval($this->post('submission_redirect')), 
					   'submission_redirect_page' => intval($this->post('submission_redirect'))==1?intval($this->post('submission_redirect_page')):0, 
					   'submission_redirect_content' => $editor->translateTo($this->post('submission_redirect_content')),
					   'limit_submissions' => intval($this->post('limit_submissions')), 
					   'limit_submissions_value' => intval($this->post('limit_submissions_value')),
					   'limit_submissions_type' => $this->post('limit_submissions_type'),
					   'limit_submissions_redirect' => intval($this->post('limit_submissions_redirect')), 
					   'limit_submissions_redirect_page' => intval($this->post('limit_submissions_redirect'))==1?intval($this->post('limit_submissions_redirect_page')):0, 
					   'limit_submissions_redirect_content' => $editor->translateTo($this->post('limit_submissions_redirect_content')),
					   'schedule' => intval($this->post('schedule')), 
					   'schedule_start' => $this->post('schedule_start_activate')?$date_time->translate('schedule_start'):date("Y-m-d H:i:s"),
					   'schedule_end' => $this->post('schedule_end_activate')?$date_time->translate('schedule_end'):date("Y-m-d H:i:s"),
					   'schedule_redirect' => intval($this->post('schedule_redirect')), 
					   'schedule_redirect_page' => intval($this->post('schedule_redirect'))==1?intval($this->post('schedule_redirect_page')):0, 
					   'schedule_redirect_content' => $editor->translateTo($this->post('schedule_redirect_content')),
					   'html5' => intval($this->post('html5')), 
					   'css' => intval($this->post('css')), 
					   'css_value' => $this->post('css_value'));
			
			$f = new FormidableForm($this->post('formID'));						
			$f->save($v);

			if (intval($this->post('formID')) == 0)	
			{	
				// Add default mailing to Formidable
				$default_mailing = $this->_default_mailing;
				$default_mailing['formID'] = $f->formID;
				
				Loader::model('formidable/mailing', 'formidable');
				$fm = new FormidableMailing();								
				$fm->save($default_mailing);
								
				$this->redirect('/dashboard/formidable/forms/elements/'.$f->formID.'/true/');
			}
			$this->redirect('/dashboard/formidable/forms/edit/'.$f->formID.'/true/');
		}
		
		$data = $this->post();
		
		$this->set('data', $data);
		
		$this->set('error', $val->getError());
		$this->set('create_form', true);
	}
	
	public function message($mode = 'deleted') 
	{
		switch($mode) 
		{
			case 'notfound':		$this->set('error', 	t('Form can\'t be found!'));			break;
			case 'error':			$this->set('error', 	t('Oops, something went wrong!'));		break;
			case 'duplicated':		$this->set('message',	t('Form duplicated succesfully'));		break;
			case 'saved':			$this->set('message', 	t('Form saved successfully'));			break;
			case 'deleted':
			default:				$this->set('message', 	t('Form deleted successfully'));		break;
		}
		$this->view();
	}
}