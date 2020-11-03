<?php     

defined('C5_EXECUTE') or die(_("Access Denied."));	

class ResultsBlockController extends BlockController {

	public $helpers = array('form', 
							'text', 
							'concrete/interface');
	
	protected $btInterfaceWidth = 700;
	protected $btInterfaceHeight = 500;
	protected $btTable = 'btFormidableResults';
	
	protected $btCacheBlockRecord = false;
	protected $btCacheBlockOutput = false;
	protected $btCacheBlockOutputOnPost = false;
	protected $btCacheBlockOutputForRegisteredUsers = false;
	protected $btCacheBlockOutputLifetime = 300;
		
	protected $data;
	protected $search;
	protected $columns;

	public function getBlockTypeDescription() {
		return t("Adds a Formidable Form Results to you page.");
	}
	
	public function getBlockTypeName() {
		return t("Formidable Results");
	}		
	
	public function getJavaScriptStrings() {
		return array(
			'form-required' => t('You must select a form.')
		);
	}

	function on_start() {	
		parent::on_start();	
		
		Loader::model('formidable', 'formidable');			
		$f = new Formidable();			
		$this->set('forms', $f->getAllForms());
	}		
	
	public function add() {
		Loader::model('formidable/form', 'formidable');

		$f = new FormidableForm($this->data['params']['formID']);		
		if (!$f->formID)
			$f = $f->getFirstForm();
		
		$this->set('formID', $f->formID);	
	}

	public function edit() {			

		//Reload data
		$this->retrieveBlockData();
		$this->set('data', $this->data['params']);	
	}

	public function validate($args) {
        
        $e = Loader::helper('validation/error');
        
        if (empty($args['formID'])) {
            $e->add(t('You must select a form'));
        }

        $rule = 0;  
       	foreach ((array)$args['search'] as $searchrule) {     		
     		$rule++;
			$_elements = array();			
			foreach ((array)$searchrule['element'] as $element) {
				$_elements[] = array_filter(array('element' => $element['element'],
												  'element_value' => $element['element_value'],
												  'condition' => $element['condition'],
												  'condition_value' => $element['condition_value']));
			}									
			if (empty($_elements)) {
				$e->add(t('Search Rule #%s: no element selected', $rule));	
			} else {
				foreach ($_elements as $_element) {			
					if (empty($_element['element']))	
						$e->add(t('Search Rule #%s: no element selected', $rule));
					
					if ($_element['condition'] != 'enabled' && $_element['condition'] != 'disabled' &&
						$_element['condition'] != 'empty' && $_element['condition'] != 'not_empty' &&
						!empty($_element['condition']))	
						if (empty($_element['condition_value']))	
							$e->add(t('Search Rule #%s: condition value is invalid', $rule));
				}
			} 								
       	}    

       	if (empty($args['column'])) {
       		$e->add(t('You must select at least one column to show'));
       	}
       	if (empty($args['fSearchDefaultSort'])) {
            $e->add(t('You must select a default sort'));
        }
        if (empty($args['fSearchDefaultSortDirection'])) {
            $e->add(t('You must select a default sort direction'));
        }
        return $e;
    }

	public function save($args) {	

		Loader::model('formidable/form', 'formidable');
		Loader::model('formidable/result', 'formidable');

		$fdc = new FormidableResultsSearchColumnSet();
   		$fdc->setFormID($this->post('formID'));

   		$fldca = new FormidableResultsSearchAvailableColumnSet();
    	$fldca->setFormID($this->post('formID'));
    	$fldca->loadColumns();

		foreach($this->post('column') as $key) {
			$fdc->addColumn($fldca->getColumnByKey($key));
		}	

		$sortCol = $fldca->getColumnByKey(array_shift($this->post('column')));
		if ($this->post('fSearchDefaultSort') != 'rand') {
			$sortCol = $fldca->getColumnByKey($this->post('fSearchDefaultSort'));
		}
		$fdc->setDefaultSortColumn($sortCol, $this->post('fSearchDefaultSortDirection'));

		if (!empty($args['search'])) {
			foreach ((array)$args['search'] as $searchrule) {
				$_elements = array();
				foreach ((array)$searchrule['element'] as $element) {
					$_elements[] = array_filter(array('element' => $element['element'],
									     			  'element_value' => $element['element_value'],
									    			  'condition' => $element['condition'],
													  'condition_value' => $element['condition_value']));
				}
				if (!empty($_elements))
					$search[] = array('elements' => $_elements);	
			}
		}

		//Convert data
		$args = array(
			'params' => serialize(
				array(
					'formID' => $this->post('formID'),
					'limit' => $this->post('limit'),
					'limit_value' => $this->post('limit_value'),
					'pagination' => $this->post('pagination'),	
					'sortable' => $this->post('sortable'),
					'sort' => ($this->post('fSearchDefaultSort') == 'rand')?$this->post('fSearchDefaultSort'):''					
				)
			),
			'search' => serialize($search),
			'columns' => serialize($fdc)
		);
		parent::save($args);
	}	
	
	public function view() {	

		$html = Loader::helper('html');
		$form = Loader::helper('form');
		$concrete_urls = Loader::helper('concrete/urls');
		
		//Reload data
		$this->retrieveBlockData();

		Loader::model('formidable', 'formidable');			
		$f = new Formidable($data['formID']);

		$frl = $this->getRequestedSearchResults();
		if ($frl) {

			if ($this->data['params']['limit'] != 1 && intval($this->data['params']['limit_value']) <= 0) {
				$fr = $frl->get();
			} else {
				$fr = $frl->getPage();
			}
					
			$this->set('resultsList', $frl);		
			$this->set('results', $fr);	

			if ($this->data['params']['pagination'])
				$this->set('pagination', $frl->getPagination());

			if ($this->data['params']['sortable'])
				$this->set('sortable', true);
		}		
	}

	public function retrieveBlockData() {
		
		Loader::model('formidable/form', 'formidable');
		Loader::model('formidable/result', 'formidable');

		$this->data['params'] = unserialize($this->params);

		$f = new FormidableForm($formID);		
		if (!$f->formID)
			$f = $f->getFirstForm();
		
		if (!$f->formID)
			return false;

		// Make sure all elements are loaded before unserializing the params
		$available = $f->availableElements();

		$this->data['search'] = unserialize($this->search);
		$this->data['columns'] = unserialize($this->columns);
	}

	public function getResultsSearchColumnSet($formID) {
		
		Loader::model('formidable/form', 'formidable');
		Loader::model('formidable/result', 'formidable');

		$this->retrieveBlockData();

		$f = new FormidableForm($formID);		
		if (!$f->formID)
			$f = $f->getFirstForm();
		
		if (!$f->formID)
			return false;

		$fldcd = new FormidableResultsSearchDefaultColumnSet(); 
	    $fldcd->setFormID($f->formID);
	    $fldcd->loadColumns();

	    $fdc = new FormidableResultsSearchColumnSet();
		$fdc->setFormID($f->formID);

	    $fldc = $fldcd;
	    if ($formID == $this->data['params']['formID']) {	    
			$fdc->setCurrent($this->data['columns']);			
			$fldc = $fdc->getCurrent(false);
		}
		
		if (!($fldc instanceof FormidableResultsSearchColumnSet))
			$fldc->loadColumns();

	    return array(
	    	'fdc' => $fdc,
	    	'fldc' => $fldc,
	    	'fldcd' => $fldcd,
	    	'sort' => $this->data['params']['sort']
	    );
	}

	public function getResultsSearchRequest($formID) {

		Loader::model('formidable/form', 'formidable');

		$this->retrieveBlockData();

		$f = new FormidableForm($formID);		
		if (!$f->formID)
			$f = $f->getFirstForm();
		
		if (!$f->formID)
			return false;

		if ($formID == $this->data['params']['formID'])	  
			$search = $this->data['search'];

		$html = '<div id="searchrule" data-next_rule="100">';        
        if (!empty($search)) {
        	foreach ((array)$search as $rule => $searchrule) {
            	$html .= $this->searchrule($rule);
            	$html .= '<script>setTimeout(function() { ';
            	$html .= 'ccmFormidableInitSearch(\''.$rule.'\'); ';

			    foreach((array)$searchrule['elements'] as $element_rule => $e) {
			    	$html .= 'ccmFormidableInitSearchElement(\''.$rule.'\', \''.$element_rule.'\'); ';
			    }
    			$html .= '}, '.($rule * 200).');</script>';
            }
        }
      	$html .= '</div>';
		$html .= Loader::helper('concrete/interface')->button_js(t('Add search rule'), 'ccmFormidableAddSearch()', 'right');

		return $html;
	}

	public function getRequestedSearchResults() {

		Loader::model('formidable/form', 'formidable');

		$this->retrieveBlockData();

		$f = new FormidableForm($this->data['params']['formID']);		
		if (!$f->formID)
			$f = $f->getFirstForm();
		
		if (!$f->formID)
			return false;
				
		$frl = new FormidableResultsList($f->formID);
		$frl->enableStickySearchRequest();
				
		$fdc = $this->data['columns'];
		if (!($fdc instanceof FormidableResultsSearchColumnSet)) {
			$fdc = new FormidableResultsSearchColumnSet();
			$fdc->setFormID($f->formID);			
		} else {
			$colsort = $fdc->getDefaultSortColumn();
			$frl->addToSearchRequest('ccm_order_dir', $colsort->getColumnDefaultSortDirection());
			$frl->addToSearchRequest('ccm_order_by', $colsort->getColumnKey());
		}

		$this->set('columns', $fdc);

		foreach ((array)$this->data['search'] as $searchrule) {
			foreach ($searchrule['elements'] as $element) {

				$el = new FormidableElement($element['element']);
				if (!$el->elementID)
					continue;

				$cond_value = $element['condition_value'];

				$options = @array_filter((array)unserialize($el->options));
				if (sizeof($options) > 0) {
					$cond_value = $element['element_value'];
					switch ($element['cond_value']) {
						
						case 'any_value':	
							$cond = '!=';
							$cond_value = '';
						break;

						case 'no_value':	
							$cond = '=';
							$cond_value = '';
						break;

						default:	
							$cond = '=';
						break;
					}
				} else {
					switch ($element['condition']) {						
						case 'empty':		
							$cond = '=';		
						break;
						case 'not_empty':	
							$cond = '!=';		
						break;
						case 'contains':
							$cond = 'LIKE';
							$cond_value = '%'.$cond_value.'%';
						break;
						case 'not_contains':
							$cond = 'NOT LIKE';
							$cond_value = '%'.$cond_value.'%';
						break;
						case 'start':
							$cond = 'LIKE';
							$cond_value = $cond_value.'%';
						break;
						case 'not_start':
							$cond = 'NOT LIKE';
							$cond_value = $cond_value.'%';
						break;
						case 'end':
							$cond = 'LIKE';
							$cond_value = '%'.$cond_value;
						break;
						case 'not_end':
							$cond = 'NOT LIKE';
							$cond_value = '%'.$cond_value;
						break;
						case 'regex':
							$cond = 'REGEXP';
						break;
						case 'not_equals':
							$cond = '!=';
						break;
						case 'equals':
						default:	
							$cond = '=';
						break;
					}
				}

				$_el = new FormidableElement($element['element']);
				if ($_el->elementID) {
					$frl->filter(false, "fas.answerSetID = (SELECT fa_tmp.answerSetID 
															FROM FormidableAnswers AS fa_tmp												
															WHERE fa_tmp.formID = ".$f->formID." 
															AND fa_tmp.elementID = ".$_el->elementID." 
															AND fa_tmp.answerSetID = fas.answerSetID 
															AND fa_tmp.answer_formated ".$cond." '".$cond_value."')");				
				} else {
					$frl->filter($element['element'], $cond_value, $cond);
				}
			}
		}
		
		if ($_REQUEST['debug'])
			$frl->debug();

		if ($this->data['params']['limit'] == 1 && intval($this->data['params']['limit_value']) > 0)
			$frl->setItemsPerPage($this->data['params']['limit_value']);
		
		if ($this->request('ccm_order_by') && $req['ccm_order_by'])				
			$frl->sortBy($this->request('ccm_order_by'), $this->request('ccm_order_dir'));

		return $frl;
	}



	public function searchrule($rule = 0) {
		
		$form = Loader::helper('form');
														 			
		$rule = intval($rule);

		// First should be empty
		$search = '';

		$formID = $this->data['params']['formID'];
		if ($this->post('formID'))	
			$formID	= $this->post('formID');
		
		Loader::model('formidable/form', 'formidable');
		$f = new FormidableForm($formID);				
		if (!$f->formID)
			return false;

		if ($formID == $this->data['params']['formID']) {
			if (!empty($this->data['search'][$rule]))
				$search = $this->data['search'][$rule];
		}
		
		$html .= '<div class="searchrule" id="search_rule_'.$rule.'" data-rule="'.$rule.'"  data-next_rule="100">';
		$html .= '<div class="clearfix">';
		$html .= '<div class="input operator">';
		$html .= t('OR');
		$html .= '</div>';	
		$html .= '</div>';	
		$html .= '<div class="clearfix">';
		$html .= '<label style="line-height:25px;">';
		$html .= '<a href="javascript:ccmFormidableDeleteSearch('.$rule.');" class="btn error option_button" title="'.t('Delete this rule').'">-</a>';
		$html .= '<a href="javascript:;" class="mover ccm-menu-icon ccm-icon-move" title="'.t('Move this rule').'"></a>';
		$html .= t('Rule');
		$html .= ' #<span class="rule">'.($rule + 1).'</span>';
		$html .= '</label>';
		$html .= '<div class="searchelements input" data-next_rule="100">';

		if (!empty($search['elements'])) {
			foreach($search['elements'] as $element_rule => $element) {
				$html .= $this->searchelement($rule, $element_rule);
			}
		} else {
			$html .= $this->searchelement($rule);
		}
		
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	public function searchelement($search_rule, $rule = '') {
		
		$form = Loader::helper('form');
		
		$rule = intval($rule);
				
		$search = '';

		$formID = $this->data['params']['formID'];
		if ($this->post('formID'))	
			$formID	= $this->post('formID');
		
		Loader::model('formidable/form', 'formidable');
		$f = new FormidableForm($formID);				
		if (!$f->formID)
			return false;

		if ($formID == $this->data['params']['formID']) {
			if (!empty($this->data['search'][$search_rule]))
				$search = $this->data['search'][$search_rule];
		}

		$search_element = '';
		if (!empty($search['elements'][$rule]))
			$search_element = $search['elements'][$rule];	
		
		$_conditions = array('empty' => t('is empty'),
							 'not_empty' => t('is not empty'));				
		
		$_elements = array('' => t('Select an element'));		
		
		$elements = $f->elements;
		if (sizeof($elements) > 0) {
			foreach($elements as $element) {

				if ($element->is_layout || $element->elementID == $el->elementID)
					continue;

				$_elements[$element->elementID] = $element->label;
				
				if ($element->elementID == $search_element['element']) {
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
																	   'not_contains' => t('does not contain'),
																	   'start' => t('starts with'),
																	   'not_start' => t('does not start with'),
																	   'end' => t('ends with'),
																	   'not_end' => t('does not end with'),																	
																	   'regex' => t('match regular expression')));
					}	
				}				
			}
		}
		$html  = '<div class="searchelement" id="element_'.$rule.'">';
		$html .= '<a href="javascript:ccmFormidableDeleteSearchElement('.$search_rule.', '.$rule.');" class="btn error option_button" style="float:right !important;" title="'.t('Delete this element').'">-</a>';
		$html .= '<a href="javascript:ccmFormidableAddSearchElement('.$search_rule.');" class="btn success option_button" style="float:right !important;" title="'.t('Add an element to this rule').'">+</a> ';
		$html .= '<div class="element" style="margin-top: 5px;">';
		$html .= '<span class="element_label">'.t('and').'</span> ';
		$html .= t('where');
		$html .= $form->select('search['.$search_rule.'][element]['.$rule.'][element]', (array)$_elements, $search_element['element'], array('style' => 'width: 433px; margin-left: 5px;', 'class' => 'element'));
		$html .= '</div>';
        $html .= '<div class="element_value" style="margin-top: 5px;">';
        $html .= t('has');
		$html .= $form->select('search['.$search_rule.'][element]['.$rule.'][element_value]', (array)$_element_values, $search_element['element_value'], array('style' => 'width: 307px; margin-left: 5px; margin-right: 5px;', 'class' => 'element_value'));
		$html .= t('selected/checked');
		$html .= '</div>';
        $html .= '<div class="condition" style="margin-top: 5px;">';
		$html .= $form->select('search['.$search_rule.'][element]['.$rule.'][condition]', (array)$_conditions, $search_element['condition'], array('style' => 'width: 150px;', 'class' => 'condition'));
        $html .= $form->text('search['.$search_rule.'][element]['.$rule.'][condition_value]', $search_element['condition_value'], array('style' => 'width: 279px; margin-left: 5px;', 'class' => 'condition_value'));
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;	
	}
	
	
}
?>