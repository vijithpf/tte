<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable', 'formidable');

class FormidableTemplate extends Formidable {
	
	public function __construct($templateID = 0)
	{		
		if (!empty($templateID))
			$this->getById($templateID);
	}
	
	public function getID() {
		return $this->templateID;
	}
		
	private function getById($templateID) 
	{		
		$db = Loader::db();
					
		$t = $db->getRow("SELECT * 
						  FROM FormidableTemplates 
						  WHERE templateID = ?", array($templateID));	
		if (empty($t))
			return false;

		$this->setAttributes(array_filter($t));
	}
	
	public function save($params)
	{
		if (!$this->templateID)	
			$this->add($params);
		else
			$this->update($params);	 
	}
	
	private function add($params)
	{			
		$db = Loader::db();
		
		$q = "INSERT INTO FormidableTemplates (`".@implode('`,`', array_keys($params))."`) 
			  VALUES (".str_repeat('?,', sizeof($params)-1)."?)";
					  				
		$db->query($q, $params);	
		$this->templateID = $db->Insert_ID();		
	}
	
	private function update($params)
	{					
		$db = Loader::db();
		
		$_params = array_slice($params, 1);
		
		foreach ($_params as $key => $value) {
			$_string[] = '`'.$key.'`=?';
			$_data[] = $value;
		}
			
		$q = "UPDATE FormidableTemplates SET ".@implode(',', $_string)."
			  WHERE templateID = ".intval($this->templateID);
		
		$db->query($q, $_data);
	}
	
	public function duplicate()
	{		
		$_params_f = get_object_vars($this);
		$_params_f['label'] .= ' ('.t('copy').')';	
		
		$unset = array('templateID','javascript','jquery','css');
		foreach ($unset as $u) {
			unset($_params_f[$u]);
		}

		$nf = new FormidableTemplate();			
		$nf->add($_params_f);
					
		return true;
	}
	
	public function delete()
	{		
		$db = Loader::db();		
		$db->query("DELETE FROM FormidableTemplates 
					WHERE templateID = ?", array($this->templateID));
		
		return true;
	}
}

class FormidableTemplateList extends DatabaseItemList {
	
	public function __construct() 
	{
		$this->setBaseQuery();
	}
	
	protected function setBaseQuery() 
	{
		$q = "SELECT *
			  FROM FormidableTemplates AS t";	
		$this->setQuery($q);		
	}
}