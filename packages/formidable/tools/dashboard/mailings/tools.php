<?php  
	
	defined('C5_EXECUTE') or die("Access Denied.");
	
	$json = Loader::helper('json');
	$cnt = Loader::controller('/dashboard/formidable/forms/mailings');			
	
	switch ($_REQUEST['action'])
	{
		case 'save':	
			$r = array('type' => 'error',
					   'message' => t('Error: Mailing can\'t be added or updated'));
			$return = $cnt->save();
			if ($return)
				$r = array('type' => 'info',
						   'message' => t('Mailing is succesfully added or updated'));
			echo $json->encode($r);
		break;
		
		case 'delete':	
			$r = array('type' => 'error',
					   'message' => t('Error: Mailing can\'t be deleted'));
			$return = $cnt->delete();
			if ($return)
				$r = array('type' => 'info',
						   'message' => t('Mailing succesfully deleted'));
			echo $json->encode($r);
		break;
	
		case 'validate':	
			$return = $cnt->validate();
			echo $json->encode($return);
		break;	
		
		case 'duplicate':	
			$r = array('type' => 'error',
					   'message' => t('Error: Mailing can\'t be duplicated'));
			$return = $cnt->duplicate();
			if ($return)
				$r = array('type' => 'info',
						   'message' => t('Mailing succesfully duplicated'));			
			echo $json->encode($r);
		break;
		
		case 'select': 
			$elements = $cnt->get_elements();
			$advanced = $cnt->get_advanced();
			
			$concrete_interface = Loader::helper('concrete/interface');
			$tabs = array( array('tab-1', t('Elements'), true), 
				           array('tab-2', t('Advanced')) );
				   
			Loader::packageElement('dashboard/mailing/elements', 'formidable', array('elements' => $elements, 'advanced' => $advanced, 'tabs' => $tabs, 'concrete_interface' => $concrete_interface));
		break;
	}
?>