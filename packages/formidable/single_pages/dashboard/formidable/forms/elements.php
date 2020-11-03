<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
echo $concrete_dashboard->getDashboardPaneHeaderWrapper(t('Formidable Forms').' - '.t('Layout and elements'), t('List of rows and colums containing elements of this form'), false, false, array(Page::getByPath('/dashboard/formidable/forms/'), Page::getByPath('/dashboard/formidable/results/'), Page::getByPath('/dashboard/formidable/templates/')));
?>
<div class="ccm-pane-body ccm-ui ccm-formidable">
	
    <?php   echo Loader::packageElement('dashboard/form/nav', 'formidable', array('f' => $f))?>
    
	<!--div class="ccm-dashboard-page-container">
		<div class="ccm-ui"></div>
	</div-->
	<form method="post" action="<?php   echo $this->action('save') ?>" id="ccm-form-record">
		<?php  echo $f->formID?$form->hidden('formID', $f->formID):''; ?>
		<div id="ccm-pane-body-left" style="display:none;">
			<?php   echo Loader::packageElement('dashboard/form/elements', 'formidable', array('elements' => $elements))?>
		</div>
		<fieldset>
			<div id="ccm-element-list">
				<div class="placeholder"><?php   echo t('Add row'); ?></div>
			</div>
		</fieldset>
		<div class="loader"></div>
		<div style="clear:both;"></div>
	</form>
</div>
<div class="ccm-pane-footer"></div>

<script>
var formID = <?php  echo $f->formID ?>;

$(function() {
	ccmFormidableLoadBackgroundImagesForLabels();
	ccmFormidableInitializeSortables();
});
</script>
<?php 
echo $concrete_dashboard->getDashboardPaneFooterWrapper(false);
