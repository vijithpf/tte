<?php  
	
	defined('C5_EXECUTE') or die("Access Denied.");
	
	if (Package::getByHandle('multilingual')) {
		$ms = MultilingualSection::getByLocale($_SESSION['LOCALE']);		
		if (is_object($ms)) {
			$_SESSION['DEFAULT_LOCALE'] = $ms->getLocale();
			Loader::helper('default_language', 'multilingual')->setupSiteInterfaceLocalization();
		}	
	}
	
	$json = Loader::helper('json');
	
	$bt = BlockType::getByHandle('formidable');
	if (intval($_REQUEST['bID']) != 0)
		$bt = Block::getByID(intval($_REQUEST['bID']));
		
	$cnt = $bt->getController();
		
	switch ($_REQUEST['action'])
	{				
		case 'submit':		
		case 'reviewed_back':	
		case 'reviewed_submit':	
			$r = $cnt->submit();
		break;
		
		case 'reset':
			$r = $cnt->reset();
		break;
			
		case 'upload':	
			$r = $cnt->upload_file();
		break;		
	}
	
	if (!is_array($r)) {
		header('Content-type: text/html');
		echo $bt->display();
	} else {
		header('Content-type: application/json');
		echo $json->encode($r);	
	}
	
?>