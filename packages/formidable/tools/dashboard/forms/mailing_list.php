<?php   
defined('C5_EXECUTE') or die("Access Denied.");

$cnt = Loader::controller('/dashboard/formidable/forms/mailings');

$mailings = $cnt->get_mailings();
	
if (sizeof($mailings) > 0)
	foreach($mailings as $mailing) 
		echo Loader::packageElement('dashboard/mailing/list', 'formidable', array('mailing' => $mailing));