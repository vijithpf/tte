<?php     defined('C5_EXECUTE') or die(_("Access Denied."));

class DashboardFormidableFormsElementsController extends Controller {

	public $helpers = array('html',
							'text', 
							'form', 
							'form/page_selector', 
							'concrete/interface', 
							'concrete/urls', 
							'concrete/dashboard'); 
							
	private $editor = '';
	
	private $element_data = '';
	private $element_params = '';
	private $element_error = false;
	
	function __construct() 
	{			
		parent::__construct();
		
		$html = Loader::helper('html');		
		$curl = Loader::helper('concrete/urls');
		
		Loader::model('formidable/form', 'formidable');
		Loader::model('formidable/layout', 'formidable');
		Loader::model('formidable/element', 'formidable');		
		
		$pkg = Package::getByHandle('formidable');
				
		$this->addHeaderItem($html->css('dashboard/formidable.css', 'formidable'));
		$this->addHeaderItem($html->javascript('dashboard/common_functions.js', 'formidable'));
		$this->addHeaderItem($html->javascript('dashboard/elements.js', 'formidable'));
		$this->addHeaderItem($html->javascript('dashboard/layouts.js', 'formidable'));
		
		// Load code elements
		$this->addHeaderItem($html->css($pkg->getRelativePath().'/libraries/3rdparty/codemirror/lib/codemirror.css'));
	    $this->addHeaderItem($html->css($pkg->getRelativePath().'/libraries/3rdparty/codemirror/theme/neat.css'));		
		$this->addHeaderItem($html->javascript($pkg->getRelativePath().'/libraries/3rdparty/codemirror/lib/codemirror.js')); 	    
	    $this->addHeaderItem($html->javascript($pkg->getRelativePath().'/libraries/3rdparty/codemirror/mode/xml/xml.js'));
	    $this->addHeaderItem($html->javascript($pkg->getRelativePath().'/libraries/3rdparty/codemirror/mode/css/css.js'));
	    $this->addHeaderItem($html->javascript($pkg->getRelativePath().'/libraries/3rdparty/codemirror/mode/htmlmixed/htmlmixed.js'));	
	    $this->addHeaderItem($html->javascript($pkg->getRelativePath().'/libraries/3rdparty/codemirror/mode/javascript/javascript.js'));
		
		$script = "<script>
					var option_counter = 10000;
					var dialog_url = '".$curl->getToolsURL('dashboard/elements/dialog', 'formidable')."';
					var tools_url = '".$curl->getToolsURL('dashboard/elements/tools', 'formidable')."';
					var list_url = '".$curl->getToolsURL('dashboard/forms/element_list', 'formidable')."';
					var layout_url = '".$curl->getToolsURL('dashboard/layouts/dialog', 'formidable')."';
					var layout_tools_url = '".$curl->getToolsURL('dashboard/layouts/tools', 'formidable')."';
					var placeholder_name = '".t('Name')."';
					var placeholder_email = '".t('E-mailaddress')."';
					var placeholder_option = '".t('Option')."';
					var element_message_add = '".t('Add element to Formidable Form')."';
					var element_message_edit = '".t('Edit element on Formidable Form')."';
					var layout_message_add = '".t('Add layout on Formidable Form')."';
					var layout_message_edit = '".t('Edit layout on Formidable Form')."';
					var message_save = '".t('Element successfully saved!')."';
					var message_duplicate = '".t('Element successfully duplicated!')."';
					var message_delete = '".t('Element successfully deleted!')."';
					var message_save_layout = '".t('Layout successfully saved!')."';
					var message_delete_layout = '".t('Layout successfully deleted!')."';
					var dependency_action_placeholder_class = '".t('Classname to toggle')."';
					var dependency_action_placeholder_value = '".t('Value to set')."';
					var dependency_action_placeholder_placeholder = '".t('Placeholder to set')."';
					var dependency_values = [['any_value', '".t('any value')."'], ['no_value', '".t('no value')."']];
					var dependency_condition_placeholder = '".t('Value')."';
					var dependency_confirm = '".t('Are you sure you want to delete this dependency rule?')."';
					var condition_values = [['empty', '".t('is empty')."'], ['not_empty', '".t('is not empty')."'], ['equals', '".t('equals')."'], ['not_equals', '".t('not equal to')."'], ['contains', '".t('contains')."'], ['not_contains', '".t('does not contain')."']];
					$(function() {
						ccmFormidableLoadElements();	
					});
				   </script>";
		$this->addHeaderItem($script);
	}
	
	public function view($formID = '', $from_new = false) 
	{
		$f = new FormidableForm($formID);
		if (!$f->formID)
			$this->redirect('/dashboard/formidable/forms', 'message', 'notfound');
		
		if ($from_new)
			$this->set('message', t('Form created successfully! Please add layouts and elements to the form'));
								
		$this->set('f', $f);
		
		$this->set('elements', $f->availableElements());
	}
	
	public function get_layouts()
	{
		$f = new FormidableForm($this->request('formID'));		
		if (!$f->formID)
			return false;
				
		return $f->layouts;	
	}
	
	public function get_layout()
	{		
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return;
		
		if ($this->request('layoutID') == -1 && intval($this->request('rowID')) > -1)
		{
			$_layouts = $this->get_layouts();
			return $_layouts[intval($this->request('rowID'))];
		}
		
		$l = new FormidableLayout($this->request('layoutID'));
		if (!is_object($l))
			return false;
				
		return $l;
	}

	public function save_layout() {
				
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return false;
		
		$l = new FormidableLayout($this->request('layoutID'));
		
		if (!$l->layoutID)
		{
			if (intval($this->request('rowID')) < 0 && !$this->request('cols'))
				return false;
			
			if ($this->request('rowID') < 0) 
			{
				$_layouts = $this->get_layouts();
				$newRowID = max(array_keys($_layouts));     
	
				$v = array('layoutID' => 0, 
						   'formID' => intval($this->request('formID')), 
						   'rowID' => intval($newRowID)+1);		   
				for ($i=0; $i<$this->request('cols'); $i++) 
				{
					$_l = new FormidableLayout();
					$_l->save($v);
				}
				return true;
			}
			
			$_layouts = $this->get_layouts();
			$_row = $_layouts[intval($this->request('rowID'))];			
			$_row_columns = intval(count($_row));
						
			if ($_row_columns < intval($this->request('cols'))) 
			{
				$v = array('layoutID' => 0, 
						   'formID' => intval($this->request('formID')), 
						   'rowID' => intval($this->request('rowID')));
						   
				for ($i=0; $i<( intval($this->request('cols')) - $_row_columns); $i++) 
				{
					$_l = new FormidableLayout();
					$_l->save($v);
				}
			}
			elseif ($_row_columns > intval($this->request('cols'))) 
			{
				$_to_be_deleted = array_slice($_row, intval($this->request('cols')), NULL, true);		
				foreach ($_to_be_deleted as $_layout) 
					if (isset($_layout->elements) && count($_layout->elements))
						return false;

				foreach($_to_be_deleted as $_layout) 
				{
					$_l = new FormidableLayout($_layout->layoutID);
					$_l->delete();
				}
				return true;
			}
		}	
		
		if (intval($this->request('rowID')) < 0 && !$this->request('label'))
			return false;
		
		$v = array('rowID' => intval($this->request('rowID')),
				   'label' => $this->request('label'),
				   'appearance' => $this->request('appearance'),	
				   'css' => intval($this->request('css')), 
				   'css_value' => intval($this->request('css'))!=0?$this->request('css_value'):'');
		
		$l->save($v);
		
		return true;
	}
	
	
	public function delete_layout() 
	{
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return false;
		
		$l = new FormidableLayout($this->request('layoutID'));
		if (!$l->layoutID)
		{
			if (intval($this->request('rowID')) < 0 && !$this->request('cols'))
				return false;
			
			$_layouts = $this->get_layouts();
			$_row = $_layouts[intval($this->request('rowID'))];			
			
			foreach ($_row as $_layout) 
				if (isset($_layout->elements) && count($_layout->elements))
					return false;

			foreach($_row as $_layout) 
			{
				$_l = new FormidableLayout($_layout->layoutID);
				$_l->delete();
			}
			return true;
		}
		
		if (isset($l->elements) && count($l->elements))
			return false;
		
		$l->delete();
		
		return true;
	}

	public function get_element()
	{		
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return;
			
		$el = $f->loadElement($this->request('element_type'), $this->request('elementID'));	
		if (!is_object($el))
			return;
		
		if (!$el->formID)
			$el->formID = $this->request('formID');
		
		if (!$el->layoutID)
			$el->layoutID = $this->request('layoutID');
		
		$el->chars_allowed_value = @explode(',', $el->chars_allowed_value);					
		$el->options = unserialize($el->options);
		
		if (sizeof($el->options) < 1)
			$el->options = array(array('selected' => 0,
									   'name' => ''));			
		return $el;
	}
		
	public function validate() 
	{	
		$f = new FormidableForm();					
		
		$el = $f->loadElement($this->request('element_type'), $this->request('elementID'), $f->html5);	
		if (!is_object($el))
			return false;
		
		$prop = $el->validateProperties();
		if ($prop === false)
			$prop = array();
			
		$depe = $el->validateDependencies();
		if ($depe === false)
			$depe = array();
						
		$errors = array_merge($prop, $depe);
		if (sizeof($errors) > 0) 
			return $errors;
		
		return false;	
	}
		
	public function save() 
	{							
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			$this->redirect('/dashboard/formidable/forms');
			
		$el = $f->loadElement($this->request('element_type'), $this->request('elementID'), $f->html5);	
		if (!is_object($el))
			return false;
		
		if (!empty($el->handle))
			$old_handle = $el->handle;
					
		$data = $this->request();
		
		if (sizeof($data['options_name']) > 0)
			foreach($data['options_name'] as $key => $value)
				if (trim($value) != '')
					$data['options'][] = array('selected' => @in_array($key, $data['options_selected']),
											   'name' => $value,
											   'value' => $data['options_value'][$key]); 	
				
		$params = array('placeholder' => intval($data['placeholder']),
					    'placeholder_value' => $data['placeholder_value'],
					    'default_value' => intval($data['default_value']),
						'default_value_type' => $data['default_value_type'],
					    'default_value_value' => $data['default_value_'.$data['default_value_type']],
					    'tinymce_value' => $data['tinymce_value'],
					    'html_code' => $data['html_code'],
						'content' => $data['content'],
					    'required' => intval($data['required']),
					    'min_max' => intval($data['min_max']),
					    'min_value' => intval($data['min_value']),
					    'max_value' => intval($data['max_value']),
					    'min_max_type' => $data['min_max_type'],	
					    'confirmation' => intval($data['confirmation']),	
					    'chars_allowed' => intval($data['chars_allowed']),
					    'chars_allowed_value' => @implode(',',$data['chars_allowed_value']),			   
					    'mask' => intval($data['mask']),
					    'mask_format' => $data['mask_format'],
					    'tooltip' => intval($data['tooltip']),
					    'tooltip_value' => $data['tooltip_value'],
					    'options' => sizeof($data['options'])>0?serialize($data['options']):'',
					    'option_other' => intval($data['option_other']),
					    'option_other_value' => $data['option_other_value'],
					    'option_other_type' => $data['option_other_type'],
					    'multiple' => intval($data['multiple']),
					    'format' => $data['format'],
					    'format_other' => $data['format_other'],
					    'appearance' => $data['appearance'],				   
					    'advanced' => intval($data['advanced']),
					    'advanced_value' => $data['advanced_value'],
					    'file_handling' => $data['file_handling'],
					    'allowed_extensions' => intval($data['allowed_extensions']),
					    'allowed_extensions_value' => $data['allowed_extensions_value'],
					    'fileset' => intval($data['fileset']),
					    'fileset_value' => intval($data['fileset_value']),
					    'css' => intval($data['css']),
					    'css_value' => $data['css_value'],
						'submission_update' => intval($data['submission_update']),
						'submission_update_type' => $data['submission_update_type'],
					    'submission_update_value' => $data['submission_update_'.$data['submission_update_type']],
						'submission_update_empty' => intval($data['submission_update_empty']),);
		
		if (!empty($data['dependency'])) {
			foreach ((array)$data['dependency'] as $dependency) {
				$_actions = $_elements = array();
				foreach ((array)$dependency['action'] as $action) {
					$_actions[] = array_filter(array('action' => $action['action'],
											         'action_value' => $action['action_value'],
									 			     'action_select' => $action['action_select']));
				}
				foreach ((array)$dependency['element'] as $element) {
					$_elements[] = array_filter(array('element' => $element['element'],
									     			  'element_value' => $element['element_value'],
									    			  'condition' => $element['condition'],
													  'condition_value' => $element['condition_value']));
				}
				if (!empty($_actions) && !empty($_elements))
					$dependencies[] = array('actions' => $_actions,
											'elements' => $_elements);	
			}
		}
											   
		$v = array('formID' => $f->formID,
				   'layoutID' => intval($data['layoutID']),
				   'element_type' => $data['element_type'],
				   'element_text' => $data['element_text'],
				   'label' => $data['label'].$data['label_sufix'],
				   'label_hide' => intval($data['label_hide']),
				   'params' => Loader::helper('json')->encode($params),
				   'dependencies' => Loader::helper('json')->encode($dependencies));
	
		$el->save($v);
		
		//Convert new element in mailing		
		if (sizeof($f->mailings) > 0)
			foreach ($f->mailings as $mailing)
				$mailing->update_element_handle($old_handle, $el->handle);
		
		return true;
	}
	
	public function duplicate()
	{			
		$el = new FormidableElement($this->request('elementID'));			
		if (!is_object($el))
			return false;
		
		return $el->duplicate();
	}
	
	
	public function delete() 
	{
		$el = new FormidableElement($this->request('elementID'));					
		if (!is_object($el))
			return false;
			
		return $el->delete();
	}
	
	public function message($mode = 'deleted', $formID) 
	{
		switch($mode) 
		{
			case 'notfound':	$this->set('error', 	t('Form or element can\'t be found!'));		break;
			case 'error':		$this->set('error', 	t('Oops, something went wrong!'));			break;
			case 'saved':		$this->set('message', 	t('Element saved successfully'));			break;
			case 'deleted':
			default:			$this->set('message', 	t('Element deleted successfully'));			break;
		}
		$this->view($formID);
	}	
	
	public function order() 
	{
		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return false;
		
		return $f->orderElement($f->formID, $this->request('elements'), $this->request('layout'));
	}

	public function orderLayout() 
	{
		$db = Loader::db();

		$f = new FormidableForm($this->request('formID'));
		if (!$f->formID)
			return false;
		
		if (count($this->request('rows'))) {
			$i = 0;
			foreach ($this->request('rows') AS $key => $row) {
				$layouts = $db->getAll("SELECT layoutID 
										FROM FormidableFormLayouts
										WHERE rowID = ?
										AND formID = ?
										ORDER BY rowID ASC, sort ASC", array($row, $f->formID));
				if (count($layouts)) {
					foreach ($layouts as $layoutID) {
						$row_query .= ' WHEN layoutID = '.$layoutID['layoutID'].' THEN '.$key.' ';
						$sort_query .= ' WHEN layoutID = '.$layoutID['layoutID'].' THEN '.$i.' ';
						$i++;
					}
				}
			}
			$db->execute("UPDATE FormidableFormLayouts SET rowID = CASE ".$row_query. "END, sort = CASE ".$sort_query. "END WHERE formID = ".$f->formID);
		}

		if (count($this->request('cols'))) {
			$i = 0;
			foreach ($this->request('cols') AS $key => $col) {		
				$sort_query .= ' WHEN layoutID = '.$col.' THEN '.$i.' ';
				$i++;
			}
			$db->execute("UPDATE FormidableFormLayouts SET sort = CASE ".$sort_query. "END WHERE rowID = ".$this->request('rowID')." AND formID = ".$f->formID);
		}
		
		return true;
	}
	
	public function dependency($current_element, $rule = '') {
		
		$form = Loader::helper('form');
		
		$el = new FormidableElement($current_element);			
		if (!is_object($el))
			return false;
												 			
		$rule = intval($rule);
				
		$dependency = '';
		if (!empty($el->dependencies[$rule]))
			$dependency = $el->dependencies[$rule];
							
		$html .= '<div class="dependency" id="dependency_rule_'.$rule.'" data-rule="'.$rule.'" data-elementID="'.$el->elementID.'">';
		$html .= '<div class="clearfix">';
		$html .= '<div class="input operator">';
		$html .= t('OR');
		$html .= '</div>';	
		$html .= '</div>';	
		$html .= '<div class="clearfix">';
		$html .= '<label style="line-height:25px;">';
		$html .= '<a href="javascript:ccmFormidableDeleteDependency('.$rule.');" class="btn error option_button" title="'.t('Delete this rule').'">-</a>';
		$html .= '<a href="javascript:;" class="mover ccm-menu-icon ccm-icon-move" title="'.t('Move this rule').'"></a>';
		$html .= t('Rule');
		$html .= ' #<span class="rule">'.($rule + 1).'</span>';
		$html .= '</label>';
		$html .= '<div class="dependency_actions input" data-next_rule="100">';
		
		if (!empty($dependency->actions)) {
			foreach($dependency->actions as $action_rule => $action) {
				$html .= $this->dependency_action($current_element, $rule, $action_rule);
			}
		} 
		
		$html .= '</div>';
		$html .= '<div class="dependency_elements input" data-next_rule="100">';
		
		if (!empty($dependency->elements)) {
			foreach($dependency->elements as $element_rule => $element) {
				$html .= $this->dependency_element($current_element, $rule, $element_rule);
			}
		}
		
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	public function dependency_action($current_element, $dependency_rule, $rule = '') {
		
		$form = Loader::helper('form');
		
		$el = new FormidableElement($current_element);			
		if (!is_object($el))
			return false;
				
		$f = new FormidableForm($el->formID);
		if (!$f->formID)
			return false;
		
		$el = Formidable::loadElement($el->element_type, $el->elementID);
		
		$rule = intval($rule);
				
		$dependency = '';
		if (!empty($el->dependencies[$dependency_rule]))
			$dependency = $el->dependencies[$dependency_rule];
			
		$dependency_action = '';
		if (!empty($dependency->actions[$rule]))
			$dependency_action = $dependency->actions[$rule];	
		
		$_actions = array('' => t('Select behaviour'),
						  'show' => t('Show'),
						  'enable' => t('Enable'),
						  'class' => t('Toggle classname'));
						  			
		if ($el->dependency['has_placeholder_change'] === true) 
			$_actions['placeholder'] = t('Change placeholder to');
		
		if ($el->dependency['has_value_change'] === true) 
			$_actions['value'] = t('Change value to');
		
		$_options = unserialize($el->options);						
		if (sizeof($_options) > 1) {		
			for ($i=0; $i<sizeof($_options); $i++) {							
				if (!$_options[$i]['value'])
					$_options[$i]['value'] = $_options[$i]['name'];
				
				$_values[$_options[$i]['value']] = $_options[$i]['name'];
			}
		}
								
		$html  = '<div class="dependency_action" id="action_'.$rule.'">';
		$html .= '<a href="javascript:ccmFormidableDeleteDependencyAction('.$dependency_rule.', '.$rule.');" class="btn error option_button" style="float:right !important;" title="'.t('Delete this action').'">-</a>';
		$html .= '<a href="javascript:ccmFormidableAddDependencyAction('.$el->elementID.', '.$dependency_rule.');" class="btn success option_button" style="float:right !important;" title="'.t('Add an action to this rule').'">+</a> ';
		$html .= '<div class="action" style="margin-top: 5px;">';
		$html .= '<span class="action_label">'.t('and').'</span> ';
		$html .= $form->select('dependency['.$dependency_rule.'][action]['.$rule.'][action]', (array)$_actions, $dependency_action->action, array('style' => 'width: 150px;', 'class' => 'action'));
		$html .= $form->text('dependency['.$dependency_rule.'][action]['.$rule.'][action_value]', $dependency_action->action_value, array('style' => 'width: 280px; margin-left: 5px;', 'class' => 'action_value'));
		$html .= $form->select('dependency['.$dependency_rule.'][action]['.$rule.'][action_select]', (array)$_values, $dependency_action->action_select, array('style' => 'width: 290px; margin-left: 5px; margin-right: 5px;', 'class' => 'action_select'));
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	public function dependency_element($current_element, $dependency_rule, $rule = '') {
	
		$form = Loader::helper('form');
		
		$el = new FormidableElement($current_element);			
		if (!is_object($el))
			return false;
		
		$f = new FormidableForm($el->formID);
		if (!$f->formID)
			return false;
		
		$el = Formidable::loadElement($el->element_type, $el->elementID);
		
		$rule = intval($rule);
				
		$dependency = '';
		if (!empty($el->dependencies[$dependency_rule]))
			$dependency = $el->dependencies[$dependency_rule];
			
		$dependency_element = '';
		if (!empty($dependency->elements[$rule]))
			$dependency_element = $dependency->elements[$rule];	
		
		$_conditions = array('enabled' => t('is enabled'),
							 'disabled' => t('is disabled'),
							 'empty' => t('is empty'),
							 'not_empty' => t('is not empty'));				
		
		$_elements = array('' => t('Select an element'));		
		$elements = $f->elements;
		if (sizeof($elements) > 0) {
			foreach($elements as $element) {
				
				if ($element->is_layout || $element->elementID == $el->elementID)
					continue;

				$_elements[$element->elementID] = $element->label;
				
				if ($element->elementID == $dependency_element->element) {
					$_options = unserialize($element->options);						
					if (sizeof($_options) > 1) {
						
						// unset empty conditions
						unset($_conditions['empty'], $_conditions['not_empty']);

						$_element_values['any_value'] = t('any value');
						$_element_values['no_value'] = t('no value');
						for ($i=0; $i<sizeof($_options); $i++) {							
							if (!$_options[$i]['value'])
								$_options[$i]['value'] = $_options[$i]['name'];
							
							$_element_values[$_options[$i]['value']] = $_options[$i]['name'];
						}
					} else {
						$_conditions = array_merge($_conditions, array('equals' => t('equals'),
																	   'not_equals' => t('not equal to'),
																	   'contains' => t('contains'),
																	   'not_contains' => t('does not contain')));
					}	
				}				
			}
		}
		$html  = '<div class="dependency_element" id="element_'.$rule.'">';
		$html .= '<a href="javascript:ccmFormidableDeleteDependencyElement('.$dependency_rule.', '.$rule.');" class="btn error option_button" style="float:right !important;" title="'.t('Delete this element').'">-</a>';
		$html .= '<a href="javascript:ccmFormidableAddDependencyElement('.$el->elementID.', '.$dependency_rule.');" class="btn success option_button" style="float:right !important;" title="'.t('Add an element to this rule').'">+</a> ';
		$html .= '<div class="element" style="margin-top: 5px;">';
		$html .= '<span class="element_label">'.t('and').'</span> ';
		$html .= t('if');
		$html .= $form->select('dependency['.$dependency_rule.'][element]['.$rule.'][element]', (array)$_elements, $dependency_element->element, array('style' => 'width: 433px; margin-left: 5px;', 'class' => 'element'));
		$html .= '</div>';
        $html .= '<div class="element_value" style="margin-top: 5px;">';
        $html .= t('has');
		$html .= $form->select('dependency['.$dependency_rule.'][element]['.$rule.'][element_value]', (array)$_element_values, $dependency_element->element_value, array('style' => 'width: 307px; margin-left: 5px; margin-right: 5px;', 'class' => 'element_value'));
		$html .= t('selected/checked');
		$html .= '</div>';
        $html .= '<div class="condition" style="margin-top: 5px;">';
		$html .= $form->select('dependency['.$dependency_rule.'][element]['.$rule.'][condition]', (array)$_conditions, $dependency_element->condition, array('style' => 'width: 150px;', 'class' => 'condition'));
        $html .= $form->text('dependency['.$dependency_rule.'][element]['.$rule.'][condition_value]', $dependency_element->condition_value, array('style' => 'width: 279px; margin-left: 5px;', 'class' => 'condition_value'));
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;	
	}
}
