<?php  
	defined('C5_EXECUTE') or die("Access Denied.");
 
	$txt = Loader::helper('text');
	$cu = Loader::helper('concrete/urls');
	$bu = $cu->getToolsURL('dashboard/results/search_results', 'formidable');	

	$currentURL = $_SERVER['REQUEST_URI'];
	if (strpos($currentURL, 'tools') === false) 
		$currentURL = $bu;

	echo Loader::helper('form')->hidden('currentURL', $bu); 
?>

<div id="ccm-results-search-results">

<div class="ccm-pane-body">

<div id="ccm-list-wrapper"><a name="ccm-results-list-wrapper-anchor"></a>
	
	<div style="overflow:hidden">
		<div style="margin-bottom:10px; float:left;">
			<?php  $form = Loader::helper('form'); ?>
			<select id="ccm-results-list-multiple-operations" class="span3" disabled>
				<option value="">** <?php   echo t('With Selected')?></option>
				<option value="delete"><?php   echo t('Delete')?></option>
			</select>
		</div>
		<div style="float:right;">
			<?php 			
			$properties_url = ($_SESSION['formidable_form_id']?View::url('/dashboard/formidable/forms/', 'edit', $_SESSION['formidable_form_id']):'javascript:;');	
			echo Loader::helper('concrete/interface')->button(t('Edit "%s"-form', $f->label), $properties_url, '', 'btn '.(!$_SESSION['formidable_form_id']?'disabled':''));
			?>
		</div>
	</div>
	<?php 
	$keywords = $_REQUEST['keywords'];	
	if (count($results) > 0) { ?>	
		<table border="0" cellspacing="0" cellpadding="0" id="ccm-result-list" class="ccm-results-list">
		<tr>
			<th width="1"><input id="ccm-result-list-cb-all" type="checkbox" /></th>
			<?php    foreach($columns->getColumns() as $col) { ?>
				<?php    if ($col->isColumnSortable()) { ?>
					<th class="<?php   echo $resultsList->getSearchResultsClass($col->getColumnKey())?>"><a href="<?php   echo $resultsList->getSortByURL($col->getColumnKey(), $col->getColumnDefaultSortDirection(), $bu)?>"><?php   echo $col->getColumnName()?></a></th>
				<?php    } else { ?>
					<th><?php   echo $col->getColumnName()?></th>
				<?php    } ?>
			<?php    } ?>
		</tr>
	<?php   
		foreach($results as $result) { 					
			if (!isset($striped) || $striped == 'ccm-list-record-alt') {
				$striped = '';
			} else if ($striped == '') { 
				$striped = 'ccm-list-record-alt';
			}
			?>
			<tr class="ccm-list-record <?php   echo $striped?>">
			<td class="ccm-result-list-cb" style="vertical-align: middle !important"><input type="checkbox" value="<?php   echo $result['answerSetID'] ?>" /></td>
			<?php  foreach($columns->getColumns() as $col) {?>
                <td class="result_<?php   echo $col->getColumnKey() ?> show_menu" target="#options_<?php  echo $result['answerSetID'] ?>" itemID="<?php  echo $result['answerSetID'] ?>">
                <?php  if (!is_array($col->getColumnCallback()))
                        echo $txt->shorten($result[$col->getColumnKey()], 100, '...');
                      else
                        echo $col->getColumnValue($result[$col->getColumnKey()]);
                ?>
                </td>
			<?php  } ?>
            <div id="options_<?php  echo $result['answerSetID'] ?>" style="display:none;">
                <a href="javascript:ccmFormidableOpenAnswerSetDialog(<?php  echo $result['answerSetID'] ?>);" class="ccm-menu-icon ccm-icon-view"><?php  echo t('View') ?></a>
                <a href="javascript:ccmFormidableDeleteAnswerSet(<?php   echo $result['answerSetID'] ?>);" onclick="return confirm('Are you sure you want to delete this submission?');" class="ccm-menu-icon ccm-icon-delete-menu"><?php  echo t('Delete') ?></a>
            </div>
			</tr>
			<?php   
		}
	?>
	</table>
	<?php  } else { ?>
		<div id="ccm-list-none"><?php   echo t('No results found.')?></div>
	<?php   }  ?>
</div>

<?php  if (count($results) > 0) { ?>
	<div id="ccm-export-results-wrapper">
		<a id="ccm-export-results" href="javascript:void(0)" onclick="$('#ccm-results-advanced-search').attr('action', '<?php   echo $cu->getToolsURL('dashboard/results/search_results_export', 'formidable'); ?>'); $('#ccm-results-advanced-search').get(0).submit(); $('#ccm-results-advanced-search').attr('action', '<?php   echo $cu->getToolsURL('dashboard/results/search_results', 'formidable') ?>');"><span></span><?php   echo t('Export')?></a>
	</div>

<?php   $resultsList->displaySummary(); ?>
<?php  } ?>

</div>

<div class="ccm-pane-footer">
	<?php   $resultsList->displayPagingV2($bu, false); ?>
</div>

</div>

<script type="text/javascript">
$(function() { 
	ccm_setupResultSearch('results'); 
});
</script>