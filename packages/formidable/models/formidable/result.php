<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable', 'formidable');

class FormidableResult extends Formidable {
	
	public $answers = array();
	
	public function __construct($answerSetID = 0)
	{				
		if (intval($answerSetID) != 0) 
			$this->getById($answerSetID);	
	}
	
	public function getID() {
		return $this->answerSetID;
	}
	
	private function getById($answerSetID) 
	{		
		if (intval($answerSetID) == 0) 
			return false;
		
		$db = Loader::db();
					
		$answerset = $db->getRow("SELECT * 
						    	  FROM FormidableAnswerSets
						          WHERE answerSetID = ?", array($answerSetID));	
		if (!$answerset)
			return false;
		
		$this->setAttributes($answerset);
		
		if (!empty($this->submitted))
			$this->submitted = date(DATE_APP_GENERIC_MDYT, strtotime($this->submitted));	
		
		// Load answers
		$this->getAnswers();
	}
	
	private function getAnswers() 
	{
		$db = Loader::db();
		$answers = $db->getAll("SELECT *							 
								FROM FormidableAnswers
								WHERE answerSetID = ?", array($this->answerSetID));								 
		if (sizeof($answers) > 0)
			foreach ($answers as $answer)
				$this->answers[$answer['elementID']] = $answer;
	}
	
	public function save($params)
	{
		// Fetch answers from params...
		$answers = array_pop($params);
		
		if (!$this->answerSetID)	
			return $this->add($params, $answers);
		
		return $this->update($params, $answers);	 
	}
	
	private function add($params, $answers = array())
	{			
		$db = Loader::db();
		
		$db->query("INSERT INTO FormidableAnswerSets (`".@implode('`,`', array_keys($params))."`) 
			 		VALUES (".str_repeat('?,', sizeof($params)-1)."?)", $params); 
						
		$this->answerSetID = $db->Insert_ID();
		
		return $this->addAnswers($answers);
	}
	
	private function update($params, $answers = array())
	{					
		$db = Loader::db();
		
		$_params = array_slice($params, 1);
		
		foreach ($_params as $key => $value) {
			$update_string[] = '`'.$key.'` = ?';
			$update_data[] = $value;
		}		
		$update_data[] = $this->answerSetID;	 
		
		$db->query("UPDATE FormidableAnswerSets SET ".@implode(', ', $update_string)."
			  		WHERE answerSetID = ?", $update_data);
						 
		$db->query("DELETE FROM FormidableAnswers
					WHERE answerSetID = ?", array($this->answerSetID));
		
		return $this->addAnswers($answers);
	}
	
	private function addAnswers($answers)
	{
		$db = Loader::db();
		
		if (sizeof($answers) > 0) {
			foreach ($answers as $answer) {
				$q = "INSERT INTO FormidableAnswers (answerSetID, elementID, formID, answer_formated, answer_unformated) VALUES (?,?,?,?,?)";
				$d = array($this->answerSetID, $answer['elementID'], $answer['formID'], $answer['answer_formated'], $answer['answer_unformated']);
				$db->query($q, $d);
			}
		}		
		return true;		
	}

	public function updateAnswer($formID, $elementID, $answer = '') {
		$db = Loader::db();
		
		if (empty($formID) || empty($elementID))
			return false;

		$aID = $db->getOne("SELECT answerSetID FROM FormidableAnswers 
							WHERE answerSetID = ?
							AND elementID = ?
							AND formID = ?", array($this->answerSetID, $elementID, $formID));
		if (!empty($aID)) {
			$q = "UPDATE FormidableAnswers SET answer_formated = ?, answer_unformated = ? WHERE answerSetID = ? AND elementID = ? AND formID = ?";
			$d = array($answer['answer_formated'], $answer['answer_unformated'], $this->answerSetID, $elementID, $formID);	
			$db->query($q, $d);
			return true;
		} 
		
		$answers[] = array(
			'formID' => $formID,
			'elementID' => $elementID,
			'answer_formated' => $answer['answer_formated'],
			'answer_unformated' => $answer['answer_unformated']
		);
		return $this->addAnswers($answers);
	}	

	public function clearAnswer($formID, $elementID) {
		$db = Loader::db();
		
		if (empty($formID) || empty($elementID))
			return false;

		$q = "DELETE FROM FormidableAnswers 
			  WHERE answerSetID = ?
			  AND elementID = ?
			  AND formID = ?";

		$d = array($this->answerSetID, $elementID, $formID);
		
		$db->query($q, $d);
		
		return true;
	}	
	
	public function delete()
	{	
		$db = Loader::db();

		$db->query("DELETE FROM FormidableAnswers
				    WHERE answerSetID = ?", array($this->answerSetID));

		$db->query("DELETE FROM FormidableAnswerSets
					WHERE answerSetID = ?", array($this->answerSetID));
			
		return true;	
	}
}


class FormidableResultsList extends DatabaseItemList { 

	protected $autoSortColumns = array('a_submitted');
	protected $itemsPerPage = 10;	
	protected $queryCreated = 0;	
	
	private $form = '';	
	private $keyword = false;
		
	public function __construct($formID = false) {
				
		Loader::model('formidable/form', 'formidable');
		
		if (!$formID)
			$formID = $_SESSION['formidable_form_id'];

		$f = new FormidableForm($formID);		
		if (!$f->formID)
			return false;
		
		$f->getElements();		
		$this->form = $f;
		
		if (sizeof($this->form->elements) > 0) {
			foreach ($this->form->elements as $element) {
				if ($element->is_layout) {
					continue;
				}
				$this->autoSortColumns[] = 'element_'.$element->elementID;
			}
		}
		$this->filter('fas.formID', $this->form->formID, '=');
		$this->filter('temp', 1, '!=');	
	}	
	
	
	public function filterByKeyword($keyword) {
		$db = Loader::db();
		if (strlen($keyword) > 0)
			$this->keyword = $db->quote('%' . $keyword . '%');
	}
	
	public function filterByElementHandle($handle, $value, $comp = '=') {
		$db = Loader::db();
		if ($handle == false) {
  			$this->filter(false, $value);
		} else {
			$comp = (is_null($value) && stripos($comp, 'is') === false) ? (($comp == '!=' || $comp == '<>') ? 'IS NOT' : 'IS') : $comp; 
			$this->filter(false, "fas.answerSetID = (SELECT answerSetID 
													 FROM FormidableAnswers
													 LEFT JOIN FormidableFormElements ON FormidableAnswers.elementID = FormidableFormElements.elementID								
													 WHERE FormidableAnswers.formID = ".$this->form->formID." 
													 AND FormidableFormElements.label_import = '".$handle."'
													 AND FormidableAnswers.answerSetID = fas.answerSetID 
													 AND FormidableAnswers.answer_formated ".$comp." ".$db->quote($value).")");	
		}
	}

	public function filterByElementID($id, $value, $comp = '=') {
		$db = Loader::db();
		if ($id == false) {
  			$this->filter(false, $value);
		} else {
			$comp = (is_null($value) && stripos($comp, 'is') === false) ? (($comp == '!=' || $comp == '<>') ? 'IS NOT' : 'IS') : $comp; 			
			$this->filter(false, "fas.answerSetID = (SELECT answerSetID FROM FormidableAnswers 
													 WHERE formID = ".$this->form->formID." 
													 AND elementID = ".$id." 
													 AND answerSetID = fas.answerSetID 
													 AND answer_formated ".$comp." ".$db->quote($value).")");	
		}
	}

	public function get($itemsToGet = 100, $offset = 0) 
	{	
		$this->createQuery();	
		return parent::get( $itemsToGet, intval($offset));		
	}
	
	protected function setBaseQuery() {
		
		if (sizeof($this->form->elements) > 0) {
			foreach ($this->form->elements as $element) {
				if ($element->is_layout) {
					continue;
				}
				$add_select_query[] = "(SELECT answer_unformated 
										FROM FormidableAnswers
										WHERE formID = ".$this->form->formID."
										AND elementID = ".$element->elementID."
										AND answerSetID = fas.answerSetID) AS `element_".$element->elementID."` ";
				if ($this->keyword) {
					$add_search_query[] = "fas.answerSetID = (SELECT answerSetID FROM FormidableAnswers 
															  WHERE formID = ".$this->form->formID." 
															  AND elementID = ".$element->elementID." 
															  AND answerSetID = fas.answerSetID 
															  AND answer_unformated LIKE ".$this->keyword.")";
				}
			}
		}
		$this->setQuery('SELECT fas.answerSetID AS answerSetID,
								fas.submitted AS a_submitted,
								fas.ip AS a_ip,
								fas.collectionID AS a_collectionID,
								fas.answerSetID AS a_answerSetID,
								fas.userID AS a_userID,
								fas.browser AS a_browser,
								fas.platform AS a_platform,
								fas.resolution AS a_resolution
								'.($add_select_query?',':'').'
								'.@implode(', ', $add_select_query).'
						 FROM FormidableAnswerSets AS fas');	
		
		if ($this->keyword)				 
			$this->filter('', '('.@implode(' OR ', $add_search_query).')');	
	}


	public function getTotal(){ 
		$this->createQuery();
		return parent::getTotal();
	}		
	
	//this was added because calling both getTotal() and get() was duplicating some of the query components
	protected function createQuery(){
		if(!$this->queryCreated) {
			$this->setBaseQuery();
			$this->queryCreated=1;
		}
	}	
}


class FormidableDatabaseItemListColumnsSet extends DatabaseItemListColumnSet {

	public function contains($col) {
       foreach($this->columns as $_col) {
           if ($_col instanceof DatabaseItemListColumn && $col instanceof DatabaseItemListColumn) {
               if ($_col->getColumnKey() == $col->getColumnKey()) {
                   return true;
               }
           } else if (is_a($col, 'AttributeKey')) {
               if ($_col->getColumnKey() == 'ak_' . $col->getAttributeKeyHandle()) {
                   return true;
               }
           }
       }
       return false;
   }
}

class FormidableResultsSearch extends FormidableDatabaseItemListColumnsSet {

	protected $formID = false;

	public $counter = 5; // Show max columns on default	

	public function setFormID($formID) {
		Loader::model('formidable/form', 'formidable');
		$f = new FormidableForm($formID);		
		if (!$f->formID)
			return false;
		
		$this->formID = $f->formID;
	}

	public function getFormID() {
		if ($this->formID)
			return $this->formID;

		return $_SESSION['formidable_form_id'];
	}

	public function clearColumns() {
		$this->columns = array();
	}
}

class FormidableResultsSearchDefaultColumnSet extends FormidableResultsSearch {
		
	public function __construct($export = false) {
		$this->loadColumns($export);
	}
	
	public function loadColumns($export = false) {
		
		$count = $this->counter;

		$this->clearColumns();

		$callback = 'callbackResult';
		if ($export)
			$callback = 'callbackResultExport';
			
		Loader::model('formidable/form', 'formidable');
		
		$f = new FormidableForm($this->getFormID());
		if (!$f->formID)
			return false;	

		$f->getElements();
		
		if (sizeof($f->elements) > 0) {
			foreach ($f->elements as $element) {
				if ($element->is_layout || $count <= 0) 
					continue;
				$this->addColumn(new DatabaseItemListColumn('element_'.$element->elementID, $element->label, array($element, $callback)));	
				$count--;
			}
		}
		$this->addColumn(new DatabaseItemListColumn('a_submitted', t('Submitted'), array('FormidableResultsSearchDefaultColumnSet', 'callbackSubmitted')));	
		$this->setDefaultSortColumn($this->getColumnByKey('a_submitted'), 'desc');
	}

	public function callbackSubmitted($date) {
		if (!empty($date))
			return date(DATE_APP_GENERIC_MDYT, strtotime($date));	
		return '';	
	}
}

class FormidableResultsSearchAvailableColumnSet extends FormidableResultsSearch {
	
	public function __construct($export = false) {
		$this->loadColumns($export);
	}
	
	public function loadColumns($export = false) {
		
		$this->clearColumns();

		$callback = 'callbackResult';
		if ($export)
			$callback = 'callbackResultExport';
		
		Loader::model('formidable/form', 'formidable');
		
		$f = new FormidableForm($this->getFormID());	
		if (!$f->formID)
			return false;
		
		$f->getElements();		
		
		if (sizeof($f->elements) > 0) {
			foreach ($f->elements as $element) {
				if ($element->is_layout) 
					continue;
				$this->addColumn(new DatabaseItemListColumn('element_'.$element->elementID, $element->label, array($element, $callback)));	
			}
		}
		$this->addColumn(new DatabaseItemListColumn('a_ip', t('IP'), ''));
		$this->addColumn(new DatabaseItemListColumn('a_collectionID', t('Page'), array($f, 'callbackPage')));
		$this->addColumn(new DatabaseItemListColumn('a_userID', t('User'), array($f, 'callbackUser')));
		$this->addColumn(new DatabaseItemListColumn('a_answerSetID', t('Answerset ID'), ''));
		$this->addColumn(new DatabaseItemListColumn('a_submitted', t('Submitted'), array('FormidableResultsSearchDefaultColumnSet', 'callbackSubmitted')));
		$this->addColumn(new DatabaseItemListColumn('a_browser', t('Browser'), ''));
		$this->addColumn(new DatabaseItemListColumn('a_platform', t('Platform'), ''));
		$this->addColumn(new DatabaseItemListColumn('a_resolution', t('Resolution'), ''));	
		
		$this->setDefaultSortColumn($this->getColumnByKey('a_submitted'), 'desc');
	}
}

class FormidableResultsSearchColumnSet extends FormidableResultsSearch {
	
	protected $columnSet = false;

	public function getOtherColumns() 
	{			
		Loader::model('formidable/form', 'formidable');
		
		$f = new FormidableForm($this->getFormID());		
		if (!$f->formID)
			return false;
		
		$f->getElements();		
		
		if (sizeof($f->elements) > 0) {
			foreach ($f->elements as $element) {
				if ($element->is_layout) 
					continue;
				$columns[] = new DatabaseItemListColumn('element_'.$element->elementID, $element->label, array($element, 'callbackResult'));
			}
		}
		$columns[] = new DatabaseItemListColumn('a_ip', t('IP'), '');
		$columns[] = new DatabaseItemListColumn('a_collectionID', t('Page'), array($f, 'callbackPage'));
		$columns[] = new DatabaseItemListColumn('a_userID', t('User'), array($f, 'callbackUser'));
		$columns[] = new DatabaseItemListColumn('a_answerSetID', t('Answerset ID'), '');
		$columns[] = new DatabaseItemListColumn('a_browser', t('Browser'), '');
		$columns[] = new DatabaseItemListColumn('a_platform', t('Platform'), '');
		$columns[] = new DatabaseItemListColumn('a_resolution', t('Resolution'), '');
		
		return array_slice($columns, $this->counter);		
	}

	public function setCurrent($columnSet) {
		if (empty($columnSet))
			return false;

		if (!($columnSet instanceof DatabaseItemListColumnSet)) 
			return false;

		$this->columnSet = $columnSet;
	}

	public function getCurrent($dashboard = true) 
	{
		if ($this->columnSet instanceof DatabaseItemListColumnSet) 
			return $this->columnSet;

		$fldc = '';

		if ($dashboard) {
			$u = new User();
			
			$fldc = $u->config('FORMIDABLE_LIST_DEFAULT_COLUMNS_'.$this->getFormID());
			if ($fldc != '') 
				$fldc = @unserialize($fldc);
		}


		// Check to see if elements are still valid
		$use_default = true;		
		if ($fldc instanceof DatabaseItemListColumnSet) {		
			Loader::model('formidable/form', 'formidable');
			$f = new FormidableForm($this->getFormID());		
			if ($f->formID) {
				$use_default = false;
				foreach ($fldc->getColumns() as $col) {
					if (strpos($col->getColumnKey(), 'a_') !== false)
						continue;

					$not_found = true;
					foreach ($f->elements as $element) {
						if ($col->getColumnKey() == 'element_'.$element->getID()) {
							$not_found = false;
							break;
						}
					}

					if ($not_found) {
						$use_default = true;
						break;
					}
				}				
			}
		} 

		if ($use_default) {
			$fldc = new FormidableResultsSearchDefaultColumnSet();
			$u->saveConfig('FORMIDABLE_LIST_DEFAULT_COLUMNS_'.$this->getFormID(), '');
		}

		return $fldc;
	}
}

class FormidableResultsSearchSaved {
	
	public function getByID($id) {
		$db = Loader::db();
		
		$uID = 0;
		$u = new User();
		if ($u instanceof User) 
			$uID = $u->getUserID();
		
		$search = $db->getRow('SELECT * 
							   FROM FormidableSavedSearches 
							   WHERE searchID = ? 
							   AND uID = ?', array($id, $uID));
		if (!$search)
			return false;
					
		return array('name' => $search['name'],
					 'searchID' => $search['searchID'],
					 'searchRequest' => unserialize($search['searchRequest']),
					 'resultColumns' => unserialize($search['resultColumns']));
	}
	
	public function getAll() {
		$db = Loader::db();		
		$uID = 0;
		$u = new User();
		if ($u instanceof User) 
			$uID = $u->getUserID();
		$r = $db->query("SELECT * 
						 FROM FormidableSavedSearches 
						 WHERE uID = ?
						 ORDER BY name ASC", array($uID));
		while ($search = $r->fetchRow()) 									
			$searches[$search['searchID']] = $search['name'];
		
		return $searches;	
	}
	
	public function add($name, $searchRequest, $searchColumnsObject) {
		$db = Loader::db();
		$uID = 0;		
		$u = new User();		
		if ($u instanceof User) 
			$uID = $u->getUserID();			
		$v = array($uID, $name, serialize($searchRequest), serialize($searchColumnsObject));
		$db->Execute('INSERT INTO FormidableSavedSearches (uID, name, searchRequest, resultColumns) values (?, ?, ?, ?)', $v);
		return $db->Insert_ID();
	}
	
	public function delete($id) {
		$db = Loader::db();
		$u = new User();		
		if ($u instanceof User) { 
			$v = array($u->getUserID(), $id);
			$db->Execute('DELETE FROM FormidableSavedSearches WHERE uID=? AND searchID=?', $v);
		}
	}
}
