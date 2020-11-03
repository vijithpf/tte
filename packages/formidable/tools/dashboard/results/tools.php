<?php  
	
	defined('C5_EXECUTE') or die("Access Denied.");
	
	$json = Loader::helper('json');
	$cnt = Loader::controller('/dashboard/formidable/results');			
	
	switch ($_REQUEST['action'])
	{	
		case 'delete':
		case 'delete_multiple':		
			$r = array('type' => 'error',
					   'message' => t('Error: Result can\'t be deleted'));
			$return = $cnt->delete();
			if ($return)
				$r = array('type' => 'info',
						   'message' => t('Result succesfully deleted'));			
			echo $json->encode($r);
		break;

		case 'update_result':
			$r = $cnt->saveElementResult();
			echo $json->encode($r);		
		break;

		case 'clear_result':
			echo $cnt->clearElementResult();
		break;

		/*
		case 'test':
			Loader::model('formidable/result', 'formidable');
			// FormID can be set now
			$list = new FormidableResultsList($_REQUEST['formID']);
			// FieldID = 3, Value = "Wim", Comparison = LIKE
			$list->filterByElementHandle('your-name_3', '%Wim%', 'LIKE');
			$list->debug();
			var_dump($list->get());
		break;
		*/
	}
?>