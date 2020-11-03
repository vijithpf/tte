<?php    
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('formidable', 'formidable');

class FormidableLayout extends Formidable {
	
	public function __construct($layoutID = 0)
	{		
		if (intval($layoutID) != 0)
			$this->getById($layoutID);	
	}
		
	private function getById($layoutID) 
	{
		if(!is_numeric($layoutID) || intval($layoutID) == 0) 
			return false;
		
		$db = Loader::db();
					
		$layout = $db->getRow("SELECT * 
							   FROM FormidableFormLayouts
							   WHERE layoutID = ?", array($layoutID));			
		if (!$layout)
			return false;
		
		$this->setAttributes(array_filter($layout));
				
		// Load elements
		$this->getElements();

		// Generate layout
		$this->generate();
	}
	
	public function getElements()
	{
		$db = Loader::db();
		$elements = $db->getAll("SELECT elementID,
										element_type							 
								 FROM FormidableFormElements
								 WHERE layoutID = ?
								 ORDER BY sort ASC", array($this->layoutID));								 
		if (sizeof($elements) > 0)
			foreach ($elements as $element)
				$this->elements[$element['elementID']] = $this->loadElement($element['element_type'], $element['elementID']);	
	}
	
	public function generate() 
	{
		$this->setDefaultAttributes();
		if (sizeof($this->attributes) > 0) 
			foreach ($this->attributes as $_name => $_value)
				$_attributes .= $_name.'="'.$_value.'" ';
		
		$this->container_start = '<div '.$_attributes.'>';
		$this->container_stop = '</div>';	
		
		if ($this->appearance == 'fieldset') {
			$this->container_start = '<fieldset '.$_attributes.'>';
			if (!empty($this->label))
				$this->container_start .= '<legend>'.$this->label.'</legend>';
			
			$this->container_stop = '</fieldset>';	
		}			
	}
		
	public function save($data)
	{
		if (!$this->layoutID)	
			$this->add($data);
		else
			$this->update($data);	 
	}
	
	private function add($data)
	{					
		$db = Loader::db();
		
		if (!$data['sort'])
			$data['sort'] = $this->getNextSort(intval($data['formID']));
			
		$q = "INSERT INTO FormidableFormLayouts (`".@implode('`,`', array_keys($data))."`) 
			  VALUES (".str_repeat('?,', sizeof($data)-1)."?)";
		
		$db->query($q, $data);		
		$this->layoutID = $db->Insert_ID();		
	}
	
	private function update($data)
	{					
		$db = Loader::db();
		
		foreach ($data as $key => $value) {
			$update_string[] = '`'.$key.'` = ?';
			$update_data[] = $value;
		}

		$q = "UPDATE FormidableFormLayouts SET ".@implode(', ', $update_string)."
			  WHERE layoutID = '".$this->layoutID."'";
		
		$db->query($q, $update_data);
	}
	
	public function duplicate($formID = 0)
	{		
		$_params_l = get_object_vars($this);
		
		// Set new formID there...
		if (intval($formID) != 0)
			$_params_l['formID'] = $formID;
		
		if (!empty($_params_l['label']) && intval($formID) == 0)			
			$_params_l['label'] .= ' ('.t('copy').')';
			
		$_params_l['sort'] = $this->getNextSort(intval($_params_e['formID']));
		
		$unset = array('layoutID','html5','elements','attributes','container_start','container_stop','javascript','jquery','css');
		foreach ($unset as $u) {
			unset($_params_l[$u]);
		}
	
		$nfl = new FormidableLayout();			
		$nfl->add($_params_l);	
		
		return $nfl;
	}
	
	public function delete()
	{
		$db = Loader::db();
		
		$r = $db->query("DELETE FROM FormidableFormLayouts 
					     WHERE layoutID = ?
					     AND formID = ?", array($this->layoutID, $this->formID));
				
		$this->orderLayout($this->formID);
	}
		
	public function validateProperties() 
	{
		Loader::model('formidable/validator', 'formidable');
		$validator = new FormidableValidatorProperty();
				
		if ($this->properties['label'])				
			$validator->label($this->request('label'));
				
		if ($this->properties['css'])
			$validator->css($this->request('css'), $this->request('css_value'));
											
		return $validator->getList();	
	}
	
	public function label($format = '', $values = array()) 
	{
		# hebben we een label?
	}
	
	public function layout($format = '', $values = array()) 
	{
		if (!$this->layout)
			return '';
			
		if ($format != '' && sizeof($values) > 0)
		{
			foreach ($values as $value)
				$v[] = $this->{$value};
				
			return vsprintf($format, $v);
		}	
		return $this->layout;
	}
			
	public function getNextSort($formID) 
	{			
		return parent::getNextSort('layout', $formID);
	}
}