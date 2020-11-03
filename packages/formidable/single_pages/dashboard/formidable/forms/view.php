<?php   
defined('C5_EXECUTE') or die(_("Access Denied."));
echo $concrete_dashboard->getDashboardPaneHeaderWrapper(t('Formidable Forms').($create_form?' - '.t('Properties'):''), t('List of Formidable Forms. If the list is empty, please add one.'), false, false);

if ($create_form) { ?>

<div class="ccm-pane-body"> 
	<?php  echo Loader::packageElement('dashboard/form/nav', 'formidable', array('f' => $f))?>
	<?php  echo Loader::packageElement('dashboard/form/edit', 'formidable', array('f' => $f, 'limit_submissions_types' => $this->controller->limit_submissions_types))?>
</div>
<?php  } elseif ($preview_form) { ?>
	
<div class="ccm-pane-body"> 
	<?php  echo Loader::packageElement('dashboard/form/preview_nav', 'formidable', array('f' => $f))?>
	<?php  echo Loader::packageElement('dashboard/form/preview', 'formidable', array('f' => $f))?>
</div>    

<?php  } else { ?>
<div class="ccm-pane-body">    
<?php  
	$page = Page::getById(intval($_SESSION['formidable_current_page_id']));
	if (is_object($page) && intval($_SESSION['formidable_current_page_id'])!=0) {
		$clear = 'style="clear:both;"';
?>
	<div style=" float: left; margin-bottom:8px; height:38px;">
		<?php  echo $concrete_interface->button(t('Back to "%s"-page', $page->getCollectionName()), View::url($page->getCollectionPath())); ?>
	</div>
<?php  } ?>
	<div style=" float: right; margin-bottom:8px; height:38px;">
		<?php   echo $concrete_interface->button(t('Add new'), View::url('/dashboard/formidable/forms/add'), array(), 'success');?>
	</div>
<?php   if (sizeof($forms) > 0) { ?>
<div style="clear:both;"></div>
<table border="0" cellspacing="0" cellpadding="0" class="ccm-results-list no_bottom_margin">
	<tbody>
		<tr>
			<th class="form_label"><?php   echo t('Form Title'); ?></th>
			<th class="form_last_submission"><?php   echo t('Last Submission'); ?></th>
            <th class="form_submissions"><?php   echo t('Submissions'); ?></th>
		</tr>
	</tbody>
</table>
<div class="ccm-form-list" id="ccm-form-list">
<?php   
foreach($forms as $f) { 
	echo Loader::packageElement('dashboard/form/list', 'formidable', array('form' => $f));
}
echo $form_list->displayPagingV2(); 
?>
</div>
<?php  } else { ?>
<p <?php  echo $clear; ?>><?php   echo t('You have not created any forms.'); ?></p>
<?php  } ?>
</div>
<?php  } ?>
<div class="ccm-pane-footer">
</div>	
<?php  echo $concrete_dashboard->getDashboardPaneFooterWrapper(false)?>

<script>
$(function() {
	
	ccmFormidableCreateMenu();
	
	ccmFormidableFormCheckSelectors();
	
	$("input[name=captcha]").click(function() {
		ccmFormidableFormCheckSelectors($(this));
	});
	$("input[name=clear_button]").click(function() {
		ccmFormidableFormCheckSelectors($(this));
	});
	$("input[name=review]").click(function() {
		ccmFormidableFormCheckSelectors($(this));
	});
	$("select[name=submission_redirect]").change(function() {
		ccmFormidableFormCheckSelectors($(this));
	});
	$("input[name=limit_submissions]").change(function() {
		ccmFormidableFormCheckSelectors($(this));
	});
	$("select[name=limit_submissions_redirect]").change(function() {
		ccmFormidableFormCheckSelectors($(this));
	});
	$("input[name=schedule]").change(function() {
		ccmFormidableFormCheckSelectors($(this));
	});
	$("select[name=schedule_redirect]").change(function() {
		ccmFormidableFormCheckSelectors($(this));
	});
	$("input[name=css]").click(function() {
		ccmFormidableFormCheckSelectors($(this));
	});
	
	$('input[id=schedule_start_activate], input[id=schedule_end_activate]').wrap('<label class="add-on"></label>').parents('div.input').wrap('<div class="input-prepend"></div>');
	
});
</script>