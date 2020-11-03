<?php  
	
	defined('C5_EXECUTE') or die("Access Denied.");

	$json = Loader::helper('json');
	$cnt = Loader::block('formidable');		
	
	switch ($_REQUEST['action'])
	{		
		case 'validate':	
			$return = $cnt->validate_form();
			echo $json->encode($return);
		break;	
	}
?>