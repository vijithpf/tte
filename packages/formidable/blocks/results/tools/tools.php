<?php  
	
	defined('C5_EXECUTE') or die("Access Denied.");
	
	$json = Loader::helper('json');
	Loader::model('block_types');
    
    $bt = BlockType::getByHandle('results');
    if (intval($_REQUEST['bID']) != 0)
        $bt = Block::getByID(intval($_REQUEST['bID']));
        
    $cnt = $bt->getController();			
	
	switch ($_REQUEST['action'])
	{
		case 'add_search':					
			echo $cnt->searchrule($_REQUEST['rule']);
		break;
				
		case 'add_search_element':					
			echo $cnt->searchelement($_REQUEST['search_rule'], $_REQUEST['rule']);
		break;
		
		case 'search_load_element':								
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
