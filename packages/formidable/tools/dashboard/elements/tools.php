<?php  
	
	defined('C5_EXECUTE') or die("Access Denied.");
	
	$json = Loader::helper('json');
	$cnt = Loader::controller('/dashboard/formidable/forms/elements');			
	
	switch ($_REQUEST['action'])
	{
		case 'save':	
			$r = array('type' => 'error',
					   'message' => t('Error: "%s" can\'t be added or updated', $_REQUEST['label']));
			if ($cnt->save())
				$r = array('type' => 'info',
						   'message' => t('Field "%s" is succesfully added or updated', $_REQUEST['label']));
			echo $json->encode($r);
		break;
		
		case 'delete':	
			$r = array('type' => 'error',
					   'message' => t('Error: Field can\'t be deleted'));
			if ($cnt->delete())
				$r = array('type' => 'info',
						   'message' => t('Field succesfully deleted'));
			echo $json->encode($r);
		break;
		
		case 'duplicate':	
			$r = array('type' => 'error',
					   'message' => t('Error: Field can\'t be duplicated'));
			if ($cnt->duplicate())
				$r = array('type' => 'info',
						   'message' => t('Field succesfully duplicated'));
			echo $json->encode($r);
		break;
		
		case 'sort':	
			return $cnt->order();
		break;	
		
		case 'validate':	
			echo $json->encode($cnt->validate());
		break;	
		
		case 'add_dependency':					
			echo $cnt->dependency($_REQUEST['elementID'], $_REQUEST['rule']);
		break;
		
		case 'add_dependency_action':					
			echo $cnt->dependency_action($_REQUEST['elementID'], $_REQUEST['dependency_rule'], $_REQUEST['rule']);
		break;
		
		case 'add_dependency_element':					
			echo $cnt->dependency_element($_REQUEST['elementID'], $_REQUEST['dependency_rule'], $_REQUEST['rule']);
		break;
		
		case 'dependency_load_element':								
			Loader::model('formidable/element', 'formidable');
			$element = new FormidableElement($_REQUEST['elementID']);
			if (!$element->elementID)
				return '';
						
			$_options = unserialize($element->options);						
			if (sizeof($_options) > 0) {		
				for ($i=0; $i<sizeof($_options); $i++) {							
					if (!$_options[$i]['value'])
						$_options[$i]['value'] = $_options[$i]['name'];
					
					$_values[] = array('value' => $_options[$i]['value'],
									   'name' => $_options[$i]['name']);
				}
				echo $json->encode($_values);	
			}
			echo '';
					
		break;
	}
?>