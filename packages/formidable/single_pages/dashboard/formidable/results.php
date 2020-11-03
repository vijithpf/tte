<?php   
defined('C5_EXECUTE') or die("Access Denied.");

echo Loader::helper('concrete/dashboard')->getDashboardPaneHeaderWrapper(t('Search Formidable Results'), t('Search the results of Formidable'), false, false);?>

<?php   if (sizeof($forms) > 0) { ?>
<div class="ccm-pane-options" id="ccm-results-pane-options">
<?php    Loader::packageElement('dashboard/results/search_form_advanced', 'formidable', array('f' => $f, 'forms' => $forms, 'columns' => $columns, 'searchRequest' => $searchRequest, 'savedSearches' => $savedSearches)); ?>
</div>
<?php    Loader::packageElement('dashboard/results/search_results', 'formidable', array('f' => $f, 'columns' => $columns, 'results' => $results, 'resultsList' => $resultsList, 'pagination' => $pagination)); ?>
<?php   } else { ?>
<div class="ccm-pane-body"> 
	<div style=" float: right; margin-bottom:8px; height:38px;">
		<?php   echo Loader::helper('concrete/interface')->button(t('Create a new FormidableForm'), View::url('/dashboard/formidable/forms/add'), array(), 'success');?>
	</div>
	<p><?php   echo t('You have not created any forms.'); ?></p>
</div>
<?php   } ?>

<?php   echo Loader::helper('concrete/dashboard')->getDashboardPaneFooterWrapper(false); ?>