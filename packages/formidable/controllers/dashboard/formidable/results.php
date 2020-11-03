<?php   
defined('C5_EXECUTE') or die("Access Denied.");
class DashboardFormidableResultsController extends Controller {
	
	private $formID = '';
		
	public $columns = array();
	
	private $default_order_by = 'a_submitted';
	private $default_order_dir = 'desc';
	
	public function __construct() {
		
		parent::__construct();
		
		Loader::model('formidable/form', 'formidable');
		Loader::model('formidable/result', 'formidable');
		
		$this->formID = $_SESSION['formidable_form_id'];
		if (intval($this->request('formID')) != 0)
			$this->formID = intval($this->request('formID'));
	}

	public function view() {
						
		$html = Loader::helper('html');
		$form = Loader::helper('form');
		$concrete_urls = Loader::helper('concrete/urls');
		
		$this->addHeaderItem($html->css('dashboard/formidable.css', 'formidable'));
		$this->addHeaderItem($html->javascript('dashboard/common_functions.js', 'formidable'));
		$this->addHeaderItem($html->javascript('dashboard/results.js', 'formidable'));
		$script = "<script>
					var dialog_url = '".$concrete_urls->getToolsURL('dashboard/results/dialog', 'formidable')."';
					var tools_url = '".$concrete_urls->getToolsURL('dashboard/results/tools', 'formidable')."';
					var delete_all = '".t('Are you sure you want to delete all selected submissions?')."';
				   </script>";
		$this->addHeaderItem($script);
		
		$frl = $this->getRequestedSearchResults();
		if ($frl) {
			$fr = $frl->getPage();
					
			$this->set('resultsList', $frl);		
			$this->set('results', $fr);		
			$this->set('pagination', $frl->getPagination());
		}
				
		$this->set('savedSearches', FormidableResultsSearchSaved::getAll());
	}
	
	public function getRequestedSearchResults() 
	{		
		$f = new FormidableForm($this->formID);		
		if (!$f->formID)
			$f = $f->getFirstForm();
		
		if (!$f->formID)
			return false;
		
		$_SESSION['formidable_form_id'] = $f->formID;
				
		$this->set('f', $f);
		$this->set('forms', $f->getAllForms());

		$frl = new FormidableResultsList();
		$frl->enableStickySearchRequest();

		if ($_REQUEST['submit_search']) {
			$frl->resetSearchRequest();
		}
		$req = $frl->getSearchRequest();
				
		if (isset($_REQUEST['frssID'])) {
			$frs = FormidableResultsSearchSaved::getByID($_REQUEST['frssID']);			
			if ($frs['searchID']) {
				$req = $frs['searchRequest'];
				$columns = $frs['resultColumns'];
				$colsort = $columns->getDefaultSortColumn();
				$frl->addToSearchRequest('ccm_order_dir', $colsort->getColumnDefaultSortDirection());
				$frl->addToSearchRequest('ccm_order_by', $colsort->getColumnKey());
			}
		}
					
		$frl->filterByKeyword($req['keywords']);
		
		if (is_array($req['selectedSearchField'])) {
			foreach($req['selectedSearchField'] as $i => $item) {
				if ($item != '') {
					switch($item) {
						
						case "collectionID":
							if (!empty($req['collectionID']))
								$frl->filter('collectionID', $req['collectionID']);
						break;
						
						case "submitted":
							$dateFrom = $req['submitted_from'];
							$dateTo = $req['submitted_to'];
							if ($dateFrom != '') {
								$dateFrom = date('Y-m-d', strtotime($dateFrom));
								$frl->filter(false, 'submitted >= "'.$dateFrom.'"');
								$dateFrom .= ' 00:00:00';
							}
							if ($dateTo != '') {
								$dateTo = date('Y-m-d', strtotime($dateTo));
								$dateTo .= ' 23:59:59';								
								$frl->filter(false, 'submitted <= "'.$dateTo.'"');
							}
						break;
						
						case "userID":
							if (!empty($req['userID']))
								$frl->filter('userID', $req['userID']);
						break;
						
						case "ip":
							if (!empty($req['ip']))
								$frl->filter(false, 'ip LIKE "%'.$req['ip'].'%"');
						break;
						
						case "browser":
							if (!empty($req['browser']))
								$frl->filter(false, 'browser LIKE "%'.$req['browser'].'%"');	
						break;
						
						case "platform":
							if (!empty($req['platform']))
								$frl->filter(false, 'platform LIKE "%'.$req['platform'].'%"');
						break;
						
						case "resolution":
							$resolutionFrom = intval($req['resolution_from']);	
							$resolutionTo = intval($req['resolution_to']);						
							
							if ($resolutionFrom == 0) 
								$resolutionFrom = '.*';
							
							if ($resolutionTo == 0) 
								$resolutionTo = '.*';
							
							$frl->filter(false, 'resolution REGEXP "^'.$resolutionFrom.'x'.$resolutionTo.'$"');	
																			
						break;
						
						default:
							if (!empty($req[$item])) {
								$frl->filter(false, "fas.answerSetID = (SELECT fa_tmp.answerSetID 
																		FROM FormidableAnswers AS fa_tmp
															
																		WHERE fa_tmp.formID = ".$f->formID." 
																		AND fa_tmp.elementID = ".str_replace('element_', '', $item)." 
																		AND fa_tmp.answerSetID = fas.answerSetID 
																		AND fa_tmp.answer_unformated LIKE '%".$req[$item]."%')");	
							}
						break;
					}
				}
			}
		}
		
		$fdc = new FormidableResultsSearchColumnSet();
		$this->set('columns', $fdc->getCurrent());
		
		if ($req['numResults'] && Loader::helper('validation/numbers')->integer($req['numResults']))
			$frl->setItemsPerPage($req['numResults']);
		
		if ($req['ccm_order_by'])
			$this->default_order_by = $req['ccm_order_by'];
		if ($req['ccm_order_by'])
			$this->default_order_dir = $req['ccm_order_dir'];
				
		$frl->sortBy($this->default_order_by, $this->default_order_dir);
		
		$this->set('searchRequest', $req);
		
		return $frl;
	}
	
	public function getResult() 
	{
		$f = new FormidableForm($this->formID, $this->request('answerSetID'));		
		if (!$f->formID)
			return false;		
		
		return $f;
	}
	
	public function saveElementResult() {

		Loader::model('formidable/form', 'formidable');
		Loader::model('formidable/element', 'formidable');

		$f = new FormidableForm($this->request('formID'), $this->request('answerSetID'));		
		if (!$f->formID)
			return false;	

		$element = $f->elements[$this->request('elementID')];
		if (!is_object($element))
			return false;
		
		$errors = $element->validate();
		if ($errors !== false) {
			return array('errors' => @implode(', ', $errors));
		}		
		$element->post();
		
		$answer = array(
			'answer_formated' => $element->result,
			'answer_unformated' => $element->serialized_value
		);

		if (!$f->results->updateAnswer($f->formID, $element->elementID, $answer)) {
			return array('errors' => t('Can\'t save data'));
		}
		return array('success' => $element->result);
	}

	public function clearElementResult() {

		Loader::model('formidable/form', 'formidable');
		Loader::model('formidable/element', 'formidable');

		$f = new FormidableForm($this->request('formID'), $this->request('answerSetID'));		
		if (!$f->formID)
			return false;	

		$element = $f->elements[$this->request('elementID')];
		if (!is_object($element))
			return false;
				
		if (!$f->results->clearAnswer($f->formID, $element->elementID)) {
			return t('Error: Can\'t clear result');
		}
		return '';
	}

	public function getElementResult($element, $answerSetID) {
		
		$lh = Loader::helper('link', 'formidable');
		$form = Loader::helper('form');

		if ($element->element_type == 'fileupload') {
			$html = '<tr class="ccm-attribute-editable-field ccm-list-record">
						<td>'.$element->label.'</td>
						<td>'.$lh->url_and_email_ify($element->result).'</td>
					 </tr>';
			return $html;		 
		}

		$html = '
			<tr class="ccm-attribute-editable-field ccm-list-record">
				<td>
					<a style="font-weight:bold; line-height:18px;" href="javascript:void(0)">'.$element->label.'</a>
				</td>
				<td>
					<div class="ccm-attribute-editable-field-text">'.$lh->url_and_email_ify($element->result).'</div>
					<form method="post" action="'.Loader::helper('concrete/urls')->getToolsURL('dashboard/results/tools', 'formidable').'">
						<input type="hidden" name="elementID" value="' . $element->elementID . '" />
						<input type="hidden" name="formID" value="' . $element->formID . '" />
						<input type="hidden" name="answerSetID" value="' . $answerSetID . '" />
						<input type="hidden" name="action" value="update_result" />
						<div class="ccm-attribute-editable-field-form">';
		
		if ($element->element_type == 'hidden') {
			$html .= $form->text($element->handle, $element->value);
		} else {
			$html .= $element->input;
		}

		if (intval($element->option_other)) {	
			$html .= '<div class="input option_other">'.$element->other.'</div>';
		}

		if (intval($element->min_max)) {
			$html .= '<div class="input">
                      	<div id="'.$element->handle.'_counter" class="counter" type="'.$element->min_max_type.'" min="'.$element->min_value.'" max="'.$element->max_value.'">';
            if ($element->max_value > 0) { 
                $html .= t('You have').' <span id="'.$element->handle.'_count">'.$element->max_value.'</span> '.($element->min_max_type!='value'?$element->min_max_type:t('characters')).' '.t('left');
            }
            $html .= '</div>
             		</div>';
        }

		$html .= '		</div>
					</form>
					<div class="ccm-attribute-editable-field-error"></div>
				</td>
				<td class="ccm-attribute-editable-field-save" style="vertical-align:middle; text-align:center;" width="30">
					<a href="javascript:void(0)"><img src="' . ASSETS_URL_IMAGES . '/icons/edit_small.png" width="16" height="16" class="ccm-attribute-editable-field-save-button" />
				</a>
				<a href="javascript:void(0)">
					<img src="' . ASSETS_URL_IMAGES . '/icons/close.png" width="16" height="16" class="ccm-attribute-editable-field-clear-button" />
				</a>
				<img src="' . ASSETS_URL_IMAGES . '/throbber_white_16.gif" width="16" height="16" class="ccm-attribute-editable-field-loading" />
				</td>
			</tr>';

		return $html;
	}

	public function delete() 
	{		
		$f = new FormidableForm($this->formID);		
		if (!$f->formID)
			return false;	
		
		$_answerSetIDs = @explode(',', $this->request('answerSetID'));
		if (sizeof($_answerSetIDs) > 0)
		{ 
			foreach ($_answerSetIDs as $_asID)
			{
				$fr = new FormidableResult($_asID);
				$fr->delete();
			}
		}		
		return true;
	}
	
	public function message($mode = 'deleted') 
	{
		switch($mode) 
		{
			case 'notfound':	$this->set('error', 	t('Submission can\'t be found!'));			break;
			case 'error':		$this->set('error', 	t('Oops, something went wrong!'));			break;
			case 'deleted':
			default:			$this->set('message', 	t('Submission deleted successfully'));		break;
		}
		$this->view();
	}	

}