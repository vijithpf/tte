<?php  
defined('C5_EXECUTE') or die("Access Denied.");
	
$json = Loader::helper('json');
$cnt = Loader::controller('/dashboard/formidable/forms/elements');			

switch ($_REQUEST['action']) 
{
	case 'save':
		$r = array('type' => 'error', 
				   'message' => t('Error: Layout can\'t be added or updated'));		
		if ($cnt->save_layout())
			$r = array('type' => 'info', 
					   'message' => t('Layout is succesfully added or updated'));		
		echo $json->encode($r);
	break;
	
	case 'sort':	
		return $cnt->orderLayout();
	break;

	case 'delete':	
		$r = array('type' => 'error', 
				   'message' => t('Error: Row or column must be empty before deletion'));		
		if ($cnt->delete_layout()) 
			$r = array('type' => 'info', 
					   'message' => t('Layout succesfully deleted'));		
		echo $json->encode($r);	
	break;
}
?>