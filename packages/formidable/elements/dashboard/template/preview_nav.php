<?php 
	defined('C5_EXECUTE') or die(_("Access Denied."));
	
	$concrete_interface = Loader::helper('concrete/interface');
	$navigation = Loader::helper('navigation');
	
	global $c;
	
	$list = Page::getByPath('/dashboard/formidable/templates/');
?>
    <div class="alert" style="overflow:hidden;">
        <strong><?php  echo t('Warning!'); ?></strong> <?php  echo t('This is a preview! Just a preview without any results'); ?>
        <?php  echo $concrete_interface->button(t('Back to template'), $navigation->getLinkToCollection($list).'edit/'.$template->templateID, '', 'pull-right ccm-button-right margin-right');	?>
    </div>
<?php 	
	
?>
