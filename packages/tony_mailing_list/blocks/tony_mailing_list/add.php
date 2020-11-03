<?php 
defined('C5_EXECUTE') or die(_("Access Denied.")); 
 
$mailingListGroupName=t('Mailing List');
$mailingListGroup = Group::getByName(  $mailingListGroupName  ); 
if( is_object($mailingListGroup) ){ 
	$mailingGID=$mailingListGroup->getGroupID();  
	$controller->gIDs=$mailingGID.'';
}else{
	$controller->gIDs='';
}

$controller->signupTitle = t('Join Mailing List');
$controller->signupText = t('Subscribe to be updated on announcements, news, and info.');
$controller->subscribedMsg = t('Thanks for subscribing!');

$controller->allowUnregistered=1;
$controller->showCheckboxes=1;

$bt->inc('form_setup_html.php', array('controller' => $controller ));
?>