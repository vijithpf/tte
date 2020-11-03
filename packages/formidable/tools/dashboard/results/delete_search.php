<?php  
defined('C5_EXECUTE') or die("Access Denied.");
$u = new User();
$form = Loader::helper('form');
$vt = Loader::helper('validation/token');
$cu = Loader::helper('concrete/urls');
$ih = Loader::helper('concrete/interface');

Loader::model('formidable/result', 'formidable');
$fsa = FormidableResultsSearchSaved::getById($_REQUEST['frssID']);
if (!$fsa['searchID']) {
	die(t('Invalid Saved Search'));
}
if ($_POST['task'] == 'delete_search') {
	FormidableResultsSearchSaved::delete($_REQUEST['frssID']);
	echo 1;
}
?>

<div class="ccm-ui">
<p><?php  echo t('Are you sure you want to delete the following search?')?></p>
<p><strong><?php  echo $fsa['name']; ?></strong></p>
<form id="ccm-results-delete-search-form" method="post" action="<?php  echo $cu->getToolsURL('dashboard/results/delete_search', 'formidable'); ?>" onsubmit="return ccm_alDeleteSavedSearch(this)">
<?php  echo $form->hidden('task', 'delete_search')?>
<?php  echo $form->hidden('frssID', $_REQUEST['frssID']); ?>	
<div class="dialog-buttons">
<?php  echo $ih->button_js(t('Delete Search'), 'ccm_alDeleteSearch()', 'right', 'danger')?>	
<?php  echo $ih->button_js(t('Cancel'), 'jQuery.fn.dialog.closeTop()', 'left')?>	
</div>
</form>
</div>

<script type="text/javascript">
ccm_alDeleteSearch = function() {
	$("#ccm-results-delete-search-form").trigger("submit");
}

ccm_alDeleteSavedSearch = function(form) {
	jQuery.fn.dialog.showLoader();
	$(form).ajaxSubmit(function(r) { 
		jQuery.fn.dialog.hideLoader(); 
		jQuery.fn.dialog.closeTop();
		window.location.href = "<?php  echo View::url('/dashboard/formidable/results')?>";
	});
	return false;
}
</script>