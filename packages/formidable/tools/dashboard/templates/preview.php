<?php 
defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('formidable/template', 'formidable');	
$template = new FormidableTemplate($_REQUEST['templateID']);		
if (!$template->templateID)
	return false;

echo $template->template;

?>