<?php   
defined('C5_EXECUTE') or die(_("Access Denied.")); 
echo $concrete_dashboard->getDashboardPaneHeaderWrapper(t('Formidable Forms').' - '.t('Mailings'), t('List of mailing for this form'), false, false, array(Page::getByPath('/dashboard/formidable/forms/'), Page::getByPath('/dashboard/formidable/results/'), Page::getByPath('/dashboard/formidable/templates/')));
?>
<div class="ccm-pane-body"> 

	<?php   echo Loader::packageElement('dashboard/form/nav', 'formidable', array('f' => $f))?>
    
    <div style=" float: right; margin-bottom:8px; height:38px;">
		<?php   echo $concrete_interface->button(t('Create a new mail'), 'javascript:ccmFormidableOpenMailingDialog(0);', array(), 'success');?>
	</div> 
    <div style="clear:both;"></div>
	<form method="post" action="<?php  echo $this->action('save') ?>" id="ccm-form-record">
		<?php   echo ($f->formID?$form->hidden('formID', $f->formID):''); ?>
		<div style="clear:both;"></div>
		<table border="0" cellspacing="0" cellpadding="0" class="ccm-results-list no_bottom_margin">
			<tbody>
				<tr>
					<th class="mailing_subject"><?php   echo t('Subject'); ?></th>
					<th class="mailing_from"><?php   echo t('Mail from'); ?></th>
				</tr>
			</tbody>
		</table>
		<div id="ccm-mailing-list">
			<div class="placeholder large"><?php   echo t('You didn\'t create any mails yet.'); ?></div>
			<div class="loader"></div>
		</div>
	</form>
</div>
<div class="ccm-pane-footer">
</div>	
<script>
var formID = <?php  echo $f->formID ?>;
</script> 
<?php    echo $concrete_dashboard->getDashboardPaneFooterWrapper(false)?>

