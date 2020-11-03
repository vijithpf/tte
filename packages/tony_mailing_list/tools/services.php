<?php  

$packageHandle='tony_mailing_list'; 
$pkg = Package::getByHandle($packageHandle); 
//$urlHelper=Loader::helper('concrete/urls'); 
//require($pkg->getPackagePath().'/blocks/tony_popup/tools/services.php'); 

Loader::model( 'tony_mailing_list', $packageHandle);  

if( $_REQUEST['mode']=='send' ){

	if( TonyMailingList::getServicesAuthKey()==$_REQUEST['auth'] ){
		
		if( intval($_REQUEST['mlid']) ){
			
			echo t('sending mailing');
			
			$mailing = TonyMailingListMailing::getById(intval($_REQUEST['mlid']));
			if($mailing){
				$mailing->stillRunningCheck(); 
				$results = $mailing->send(); 
			}
		}else{
			echo t('sending all mailings');
			
			//run all pending  
			$results = TonyMailingList::sendAllMailings(); 
			//var_dump($results);
		} 
		
	}else{
		echo t('Invalid Auth Key');
	}
	
	
	
}elseif($_REQUEST['mode']=='link'){ 

	Loader::model( 'tony_mailing_list_stats', $packageHandle);
	
	$stats = new TonyMailingListStats(); 
	$stats->trackHit($_REQUEST);
	
	$url = $_REQUEST['url']; 
	
	if( !defined('MAILING_LIST_DISABLE_LINK_ENCODING') || !MAILING_LIST_DISABLE_LINK_ENCODING )
		$url = html_entity_decode($url);  
	
	header('location: '.$url, true, 302 );
	
}elseif($_REQUEST['mode']=='stats'){ 

	Loader::model( 'tony_mailing_list_stats', $packageHandle);
	
	$stats = new TonyMailingListStats(); 
	$stats->trackHit($_REQUEST);
	
	if($_REQUEST['debug']){ 
		echo $stats->errorMsg;
		die;
	}

	TonyMailingListStats::printSpacerImg(); 
	
}elseif( $_REQUEST['mode']=='curlCheck' ){

	echo 'connected';

}else echo T('invalid mode');

?>