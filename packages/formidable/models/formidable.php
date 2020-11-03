<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

class Formidable {
	
	public static $html5 = false;
	
	public $javascript = array();
	public $jquery = array();
	public $css = array();
	
	public function __construct() {
	}
		
	public function setHTML5($html5 = false) 
	{
		if ($html5)
			Formidable::$html5 = true;
	}
		
	public function setAttribute($key, $value, $add = false) 
	{
		if ($key == 'label_import')
			$key = 'handle';
		
		if (!is_array($value))
			$value = stripslashes($value);
			
		if ($add)
			$this->{$key}[] = $value;
		else
			$this->{$key} = $value;
	}	
	
	public function setAttributes($attributes) 
	{
		if (sizeof($attributes) > 0) 
			foreach ($attributes as $key => $value) 
				$this->setAttribute($key, $value);
	}		
	
	public function addJavascript($script, $jquery = true)
	{
		$_javascript = $this->jquery;
		if (!$jquery)
			$_javascript = $this->javascript;
		
		// Block double javascript content...		
		foreach ((array)$_javascript as $_js) {
			if (md5($_js) == md5($script))
				return false;	
		}
			
		if (!$jquery)
			$this->javascript[] = $script;
		else
			$this->jquery[] = $script;	
	}
	
	public function addCss($css)
	{		
		$_css = $this->css;
			
		// Block double css content...		
		foreach ((array)$_css as $_cs) {
			if (md5($_cs) == md5($css))
				return false;	
		}	
		$this->css[] = $css;	
	}
	
	public function getFirstForm() 
	{
		$db = Loader::db();
		$data = $db->getOne("SELECT formID
						     FROM FormidableForms 
						     ORDER BY label ASC");
		if ($data)									
			return new FormidableForm($data);

		return false;
	}
	
	public function getAllForms()
	{
		$db = Loader::db();
		$r = $db->query("SELECT formID,
							    label 
					     FROM FormidableForms 
					     ORDER BY label ASC");
		while ($form = $r->fetchRow()) 									
			$forms[$form['formID']] = $form['label'];
		
		return $forms;	
	}
	
	public function getAdvancedElements() 
	{
		$advanced = array ( 
						array('handle' => 'form_name',
							  'label' => 'Form name',
							  'type' => 'Text',
							  'callback' => ''), 
						
						array('handle' => 'answerset_id',
							  'label' => 'AnswersetID',
							  'comment' => '(unique ID)',
							  'type' => 'Integer',
							  'callback' => ''), 
							  	  
						array('handle' => 'user_id',
							  'label' => 'Username',
							  'comment' => '(link)',
							  'type' => 'URL',
							  'callback' => 'callbackUser'), 
						
						array('handle' => 'collection_id',
							  'label' => 'Page',
							  'comment' => '(link)',
							  'type' => 'URL',
							  'callback' => 'callbackPage'),
						
						array('handle' => 'ip',
							  'label' => 'IP Address',
							  'type' => 'Text',
							  'callback' => ''),
						
						array('handle' => 'browser',
							  'label' => 'Browser',
							  'type' => 'Text',
							  'callback' => ''),	
						
						array('handle' => 'platform',
							  'label' => 'Platform',
							  'type' => 'Text',
							  'callback' => ''),	
						
						array('handle' => 'resolution',
							  'label' => 'Screen resolution',
							  'type' => 'Text',
							  'callback' => ''),
							  	  	  							    
						array('handle' => 'submitted',
							  'label' => 'Submitted on',
							  'comment' => '(mm/dd/yyyy hh:mm:ss)',
							  'type' => 'Date/Time',
							  'callback' => '')		
					);
		
		return $advanced;			
	}
	
	public function getElementIdByHandle($handle) {
		$db = Loader::db();
		$data = $db->getOne("SELECT elementID
						     FROM FormidableFormElements
							 WHERE label_import = ?", array($handle));
		if ($data)									
			return $data;

		return false;
	}
	
	public function callbackPage($value) 
	{
		$page = t('Unknown');
		$p = Page::getById($value);
     	if (intval($p->getCollectionID()) != 0) 
	  		$page = '<a href="'.BASE_URL.DIR_REL.View::url($p->getCollectionPath()).'" target="_blank">'.$p->getCollectionName().'</a>';			
		return $page;
	}
	
	public function callbackUser($value)
	{	
		$user = t('Guest');
		$u = User::getByUserID($value);
		if ($u instanceof User) 
			$user = '<a href="'.BASE_URL.DIR_REL.View::url('/dashboard/users/search?uID='.$u->getUserID()).'" target="_blank">'.$u->getUserName().'</a>';
		return $user;
	}
		
	public function orderElement($formID, $e = array(), $l = array()) 
	{
		$db = Loader::db();
			
		if (count($e) && count($l)) 
			for ($i=0; $i<sizeof($e); $i++) 
				$elements[$i] = array('elementID' => $e[$i],
									  'layoutID' => $l[$i]);
		else
			$elements = $db->getAll("SELECT elementID, layoutID
								     FROM FormidableFormElements
								     WHERE formID = ?
								     ORDER BY sort ASC", array($formID));
	
		if (sizeof($elements) > 0) 
		{
			for ($i=0; $i<sizeof($elements); $i++) 
			{
				if (intval($elements[$i]['layoutID']) != 0 && intval($elements[$i]['elementID']) != 0) 
				{
					$r = $db->query("UPDATE FormidableFormElements
									 SET sort = ?, layoutID = ?
									 WHERE elementID = ?
									 AND formID = ?", array($i, $elements[$i]['layoutID'], $elements[$i]['elementID'], $formID));
					if (!$r)
						return false;
				}
			}
		}
		
		return true;
	}
	
	public function orderLayout($formID, $l = array()) 
	{
		$db = Loader::db();
		
		if (count($l)) 
			for ($i=0; $i<sizeof($l); $i++) 
				$layouts[$i] = array('layoutID' => $l[$i]);
		else
			$layouts = $db->getAll("SELECT layoutID
								     FROM FormidableFormLayouts
								     WHERE formID = ?
								     ORDER BY sort ASC", array($formID));
	
		if (sizeof($layouts) > 0) 
		{
			for ($i=0; $i<sizeof($layouts); $i++) 
			{
				if (intval($layouts[$i]['layoutID']) != 0) 
				{
					$r = $db->query("UPDATE FormidableFormLayouts
						  		     SET sort = ?
									 WHERE layoutID = ?
									 AND formID = ?", array($i, $layouts[$i]['layoutID'], $formID));
					if (!$r)
						return false;
				}
			}
		}
		return true;
	}	
	
	public function setDefaultAttributes() 
	{
		if (!empty($this->placeholder))
			$_attribs['attributes']['placeholder'] = $this->placeholder_value;
		
		if (!empty($this->tooltip))
			$_attribs['attributes']['rel'] = 'tooltip_'.$this->elementID;
		
		if (!empty($this->css))
			$_attribs['attributes']['class'] = $this->css_value;	
					
		$this->setAttributes($_attribs);
	}
	
	public function request($key = null, $defaultValue = null) {
		if ($key == null) 
			return $_REQUEST;
		
		if(isset($_REQUEST[$key]))
			return (is_string($_REQUEST[$key])) ? trim($_REQUEST[$key]) : $_REQUEST[$key];
		
		return $defaultValue;
	}
	
	public function deleteCustomColumnSet($formID) 
	{
		$u = new User();
		$fldc = $u->config('FORMIDABLE_LIST_DEFAULT_COLUMNS_'.$formID);
		if ($fldc != '')
			$u->saveConfig('FORMIDABLE_LIST_DEFAULT_COLUMNS_'.$formID, '');	
	}
	
	public function getNextSort($type, $formID)
	{		
		switch ($type)
		{
			case 'layout': 	$table = 'FormidableFormLayouts'; 	break;
			case 'element': $table = 'FormidableFormElements'; 	break;	
			default: 		$table = false; 					break;
		}
		
		if (!$table)
			return 0;
				
		$db = Loader::db();	
		
		$sort = 0;	
		$r = $db->getRow("SELECT MAX(sort) AS sort
					  	  FROM `".$table."`								  
						  WHERE formID = ?", array($formID));
		if ($r) 
			if ($r['sort'] != NULL)
				$sort = $r['sort'] + 1;
				
		return $sort;	
	}
			
	public function availableElements()
	{
		$file = Loader::helper('file');
		
		$pkg = Package::getByHandle('formidable');	
		
		$_elements = $file->getDirectoryContents($pkg->getPackagePath().'/'.DIRNAME_MODELS.'/formidable/element/');
		if (sizeof($_elements) > 0)
		{
			foreach ($_elements as $element)
			{
				$_element = $this->loadElement(pathinfo($element, PATHINFO_FILENAME));
				if (is_object($_element))
				{
					$group = $_element->element_group;
					if (!$_element->element_group)
						$group = 'Custom Elements';
						
					$elements[$group][$_element->element_text] = $_element;
				}
			}
		}		
		return $elements;	
	}
	
	public function loadElement($type, $id = 0)
	{
		$txt = Loader::helper('text');
		$pkg = Package::getByHandle('formidable');	
				
		$file = str_replace('-', '_', $txt->sanitizeFileSystem($type));
		$class = 'FormidableElement'.$txt->camelcase($type);
				
		if (!file_exists($pkg->getPackagePath().'/'.DIRNAME_MODELS.'/formidable/element/'.$file.'.php')) 
			return t('Type of element not supported');	
	
		Loader::model('formidable/element/'.$file, 'formidable');
		if (!class_exists($class)) 
			return t('Class "%s" unknown!', $class);		
				
		return new $class($id);			
	}	
}