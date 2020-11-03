<?php  defined('C5_EXECUTE') or die(_("Access Denied.")); ?>

<div id="ccm-results-search-results<?php  echo $bID; ?>">
<?php  	
if (!$results) {
	echo '<p>'.t('Can\'t find any results').'</p>';
} else { ?>
	
	<?php  echo $resultsList->displaySummary(); ?>
	
	<table border="0" cellspacing="0" cellpadding="0" id="ccm-result-list" class="ccm-results-list">
	<tr>
		<?php  foreach($columns->getColumns() as $col) { ?>
			<?php  if ($col->isColumnSortable() && $sortable) { ?>
				<th class="<?php  echo $resultsList->getSearchResultsClass($col->getColumnKey())?>"><a href="<?php   echo $resultsList->getSortByURL($col->getColumnKey(), $col->getColumnDefaultSortDirection(), '', array('bID' => $bID))?>"><?php   echo $col->getColumnName()?></a></th>
			<?php  } else { ?>
				<th><?php   echo $col->getColumnName()?></th>
			<?php  } ?>
		<?php  } ?>
	</tr>

	<?php  foreach($results as $result) { 	
		if (!isset($striped) || $striped == 'ccm-list-record-alt') {
			$striped = '';
		} else if ($striped == '') { 
			$striped = 'ccm-list-record-alt';
		} ?>
		<tr class="ccm-list-record <?php   echo $striped?>">
		<?php  foreach($columns->getColumns() as $col) {?>
			<td>
             <?php  if (!is_array($col->getColumnCallback()))
                    echo $text->shorten($result[$col->getColumnKey()], 100, '...');
                  else
                    echo $col->getColumnValue($result[$col->getColumnKey()]);
            ?>
            </td>
		<?php  } ?>        
		</tr>
		<?php  } ?>
	</table>

	<?php  if ($pagination) { ?>
		<?php  $pagination->setAdditionalQueryStringVariables(array('bID' => $bID)); ?>
		<div id="pagination" class="text-center">
			<ul class="ccm-pagination pagination">
				<li class="ccm-page-left"><?php  echo $pagination->getPrevious('&laquo; ' . t('Vorige')) ?></li>
				<?php  echo $pagination->getPages('li') ?>
				<li class="ccm-page-right"><?php  echo $pagination->getNext(t('Volgende') . ' &raquo;') ?></li>
			</ul>
		</div>

	<?php  } ?>

	<script>
		$(function() {
			$( document ).on( "click", "#ccm-results-search-results<?php  echo $bID; ?> a", function(e) {
				e.preventDefault();		
				$( "#ccm-results-search-results<?php  echo $bID; ?>" ).load($(this).attr('href')+" #ccm-results-search-results<?php  echo $bID; ?>");
			});	
		});
	</script>

<?php  } ?>

</div>