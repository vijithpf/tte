<?php 
	defined('C5_EXECUTE') or die(_("Access Denied."));
	
	$concrete_interface = Loader::helper('concrete/interface');
	$navigation = Loader::helper('navigation');
	
	global $c;
	
	$list = Page::getByPath('/dashboard/formidable/forms/');
	//$elem = Page::getByPath('/dashboard/formidable/forms/elements');
	//$mail = Page::getByPath('/dashboard/formidable/forms/mailings');
?>
    <div class="alert" style="overflow:hidden;">
        <strong><?php  echo t('Warning!'); ?></strong> <?php  echo t('This is a preview! You can\'t submit. Styles and theme aren\'t loaded, just a preview with the default view and css.'); ?>
        <?php  echo $concrete_interface->button(t('Back to form'), $navigation->getLinkToCollection($list).'edit/'.$f->formID, '', 'pull-right ccm-button-right margin-right');	?>
    </div>
<?php 	
	
?>
