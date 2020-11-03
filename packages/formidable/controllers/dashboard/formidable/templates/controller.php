<?php     defined('C5_EXECUTE') or die(_("Access Denied."));

class DashboardFormidableTemplatesController extends Controller {

	public $helpers = array('html',
							'text', 
							'form', 
							'validation/form', 
							'concrete/interface', 
							'concrete/urls', 
							'concrete/dashboard'); 

	private $_label = '';
	private $_template = '';	
	
	function __construct() 
	{	
		parent::__construct();
	
		$this->_label = t('My Formidable Template');		
		$this->_template = $this->_label;
				
		Loader::model('formidable/template', 'formidable');
				
		$html = Loader::helper('html');
		$curl = Loader::helper('concrete/urls');	

		$this->addHeaderItem($html->css('dashboard/formidable.css', 'formidable'));		
		$this->addHeaderItem($html->javascript('dashboard/common_functions.js', 'formidable'));
		$this->addHeaderItem($html->javascript('dashboard/templates.js', 'formidable'));
	}
		
	public function view() 
	{	
		$fl = new FormidableTemplateList();
		$fl->setItemsPerPage(999);
		
		$offset = 0;
		$list = $fl->get(999, $offset);
		if (sizeof($list) > 0)
			for ($i=0; $i<sizeof($list); $i++)
				$templates[] = new FormidableTemplate($list[$i]['templateID']);	

		$this->set('templates', $templates);
		$this->set('template_list', $fl);
	}	
				
	public function add() 
	{	
		$template = new FormidableTemplate();		
		$template->label = $this->_label;
		$template->template = '<p>'.$this->_template.'</p><p>{%FORMIDABLE_MAILING%}</p>';

		$this->set('template', $template);
		$this->set('create_template', true);
	}
	
	public function edit($id, $template_new = false) 
	{			
		if ($template_new)
			$this->set('message', t('Template saved successfully'));
								
		$template = new FormidableTemplate($id);		
		if ($template->templateID) {		
			$editor = Loader::helper('editor', 'formidable');			
			$template->header = $editor->translateFromEditMode($template->header);
			$template->footer = $editor->translateFromEditMode($template->footer);			
			$this->set('template', $template);		
			$this->set('create_template', true);
		}
		else {
			$this->message('notfound');
			$this->view();
		}
	}
	
	public function preview($id) 
	{			
		$template = new FormidableTemplate($id);		
		if ($template->templateID) {		
			$this->set('template', $template);	
			$this->set('preview_template', true);
		}
		else {
			$this->message('notfound');
			$this->view();
		}			
	}
	
	public function duplicate($id)
	{
		$template = new FormidableTemplate($id);
		$message = $template->duplicate();
		$this->redirect('/dashboard/formidable/templates', 'message', 'duplicated');	
	}
	
	public function delete($id) 
	{
		$template = new FormidableTemplate($id);
		$message = $template->delete();
		$this->redirect('/dashboard/formidable/templates', 'message', 'deleted');
	}

	public function save() 
	{		
		$val = new ValidationFormHelper();	
		$val->setData($this->post());
		
		$val->addRequired('label', t('Field "%s" is invalid', t('Label')));
		$val->addRequired('template', t('Field "%s" is invalid', t('Template')));
		
		if ($val->test()) 
		{			
			$editor = Loader::helper('editor', 'formidable');			
			$v = array(
				'templateID' => $this->post('templateID'),
				'label' => $this->post('label'), 					   
				'template' => $editor->translateTo($this->post('template'))
			);
			
			$template = new FormidableTemplate($this->post('templateID'));						
			$template->save($v);

			$this->redirect('/dashboard/formidable/templates/edit/'.$template->templateID.'/true/');
		}
		
		$data = $this->post();
		
		$this->set('data', $data);
		
		$this->set('error', $val->getError());
		$this->set('create_template', true);
	}
	
	public function message($mode = 'deleted') 
	{
		switch($mode) 
		{
			case 'notfound':		$this->set('error', 	t('Template can\'t be found!'));			break;
			case 'error':			$this->set('error', 	t('Oops, something went wrong!'));			break;
			case 'duplicated':		$this->set('message',	t('Template duplicated succesfully'));		break;
			case 'saved':			$this->set('message', 	t('Template saved successfully'));			break;
			case 'deleted':
			default:				$this->set('message', 	t('Template deleted successfully'));		break;
		}
		$this->view();
	}
}