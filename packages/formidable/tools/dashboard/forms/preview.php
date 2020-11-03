<?php 
defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('formidable/form', 'formidable');	
$ff = new FormidableForm($_REQUEST['formID']);		
if (!$ff->formID)
	return false;
			
$hh = Loader::helper('html');
$ch = Loader::helper('concrete/urls');
$pkg = Package::getByHandle('formidable');


echo $hh->css('jquery.ui.css');
echo $hh->css('../blocks/formidable/view.css', 'formidable');

echo $hh->javascript('jquery.js');
echo $hh->javascript('jquery.ui.js');
echo $hh->javascript($pkg->getRelativePath().'/libraries/3rdparty/ajaxupload/js/ajaxupload.js');

echo "<style>
body { font-family: Arial; font-size: 12px;}
</style>";

echo "<script>
var tools_url = '".$ch->getToolsURL('formidable', 'formidable')."';
var package_url = '".$pkg->getRelativePath()."';
var I18N_FF = {
	\"File size now allowed\": \"".t('File size not allowed')."\",
	\"Invalid file extension.\": \"".t('Invalid file extension')."\",
	\"Max files number reached\": \"".t('Max files number reached')."\",
	\"Extension not allowed\": \"".t('Extension \"%s\" not allowed')."\",
	\"Choose State/Province\": \"".t('Choose State/Province')."\",
	\"Please wait...\": \"".t('Please wait...')."\",
	\"Allowed extensions\": \"".t('Allowed extensions')."\"
}
$(function() { $('.buttons input').attr('disabled', true) });
</script>";	
	
$bt = BlockType::getByHandle('formidable');
$bt->controller->formID = $ff->formID;
$bt->render('view');


if (sizeof($ff->elements) > 0)
	foreach ($ff->elements as $element)				
		if (sizeof($element->javascript) > 0)
			foreach ($element->javascript as $_js)	
				$_javascript .= $_js.PHP_EOL;
	
if (sizeof($ff->elements) > 0)
	foreach ($ff->elements as $element)				
		if (sizeof($element->jquery) > 0)
			foreach ($element->jquery as $_js)	
				$_jquery .= $_js.PHP_EOL;		
echo "<script>
".$_javascript."
$(function() {	
".$_jquery."
}); 
</script>";		

echo $hh->javascript('formidable.js', 'formidable');


?>