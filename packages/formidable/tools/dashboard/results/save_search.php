<?php  
defined('C5_EXECUTE') or die("Access Denied.");
$u = new User();
$form = Loader::helper('form');
$vt = Loader::helper('validation/token');
$cu = Loader::helper('concrete/urls');
$ih = Loader::helper('concrete/interface');

if ($_POST['task'] == 'save_search') {

	$cnt = Loader::controller('/dashboard/formidable/results');			
	$frl = $cnt->getRequestedSearchResults();
	$req = $frl->getSearchRequest();
	$fdc = new FormidableResultsSearchColumnSet();
	$colset = $fdc->getCurrent();
	
	if ($req['ccm_order_by'] != '' && $req['ccm_order_dir'] != '') {
		$colset->setDefaultSortColumn($colset->getColumnByKey($req['ccm_order_by']), $req['ccm_order_dir']);
	}
	$fsa = FormidableResultsSearchSaved::add(Loader::helper('text')->entities($_POST['frsName']), $req, $colset);
	print $fsa;
	exit;
}

?>

<div class="ccm-ui">
<form id="ccm-results-save-search-form" method="post" action="<?php  echo $cu->getToolsURL('dashboard/results/save_search', 'formidable'); ?>" onsubmit="return ccm_alSaveSearch(this)">
<?php  echo $form->hidden('task', 'save_search')?>
<p><?php  echo t('Enter a name for this saved search.')?></p>
<?php  echo $form->text('frsName', array('style' => 'width: 250px'))?>
<div class="dialog-buttons">
<?php  echo $ih->button_js(t('Save Search'), 'ccm_alSubmitSearch()', 'right', 'primary')?>	
<?php  echo $ih->button_js(t('Cancel'), 'jQuery.fn.dialog.closeTop()', 'left')?>	
</div>
</form>
</div>
	
<script type="text/javascript">
ccm_alSubmitSearch = function() {
	$("#ccm-results-save-search-form").trigger("submit");
}
ccm_alSaveSearch = function(form) {
	if ($("input[name=frsName]").val() == '') {
		alert('<?php  echo t("You must enter a valid name")?>');
	} else {
		jQuery.fn.dialog.showLoader();
		$(form).ajaxSubmit(function(r) { 
			jQuery.fn.dialog.hideLoader(); 
			jQuery.fn.dialog.closeTop();			
			window.location.href = "<?php  echo View::url('dashboard/formidable/results')?>?frssID=" + r;			
		});
	}
	return false;
}
</script>