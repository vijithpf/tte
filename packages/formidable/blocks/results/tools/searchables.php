<?php 
    
    defined('C5_EXECUTE') or die(_("Access Denied."));

    Loader::model('block_types');
    
    $bt = BlockType::getByHandle('results');
    if (intval($_REQUEST['bID']) != 0)
        $bt = Block::getByID(intval($_REQUEST['bID']));
        
    $cnt = $bt->getController();

    $search = $cnt->getResultsSearchRequest($_REQUEST['formID']);

    echo $search;