<?php    defined('C5_EXECUTE') or die("Access Denied."); ?> 
<?php   
	$searchFields = array(
		'' 				=> '** ' . t('Fields'),
		'collectionID' 	=> t('On page'), 
		'ip'			=> t('From IP'), 
		'submitted'		=> t('Submitted between'), 
		'userID'		=> t('From userID'), 
		'browser'		=> t('Browser used'), 
		'resolution'	=> t('Resolution'), 
		'platform'		=> t('Platform / OS')
	);
	foreach ((array)$f->elements as $element) {
		if (!$element->is_layout) {
			$searchElements['element_'.$element->elementID] = $element->label;	
			$searchFields['element_'.$element->elementID] = $element->label;	
		}
	}
	
	$form = Loader::helper('form'); 
	$cu = Loader::helper('concrete/urls'); 
	
?>   

	<div id="ccm-results-search-field-base-elements" style="display: none">
		
        <span class="ccm-search-option" search-field="collectionID">
		<div style="width: 100px">
		<?php  print Loader::helper("form/page_selector")->selectPage('collectionID');
		?>
		</div>
		</span>
        
		<span class="ccm-search-option" search-field="ip">
		<?php  echo $form->text('ip')?>
		</span>
        
        <span class="ccm-search-option ccm-search-option-type-date_time"  search-field="submitted">
		<?php  echo $form->text('submitted_from', array('style' => 'width: 86px'))?>
		<?php  echo t('to')?>
		<?php  echo $form->text('submitted_to', array('style' => 'width: 86px'))?>
		</span>
        
        <span class="ccm-search-option" search-field="userID">
		<div style="width: 100px">
		<?php  print Loader::helper("form/user_selector")->selectUser('userID');
		?>
		</div>
		</span>
        
        <span class="ccm-search-option" search-field="browser">
		<?php  echo $form->text('browser')?>
		</span>
        
        <span class="ccm-search-option" search-field="resolution">
		<?php  echo $form->text('resolution_from', '', array('style' => 'width: 30px'))?>
		<?php  echo "x"; ?>
		<?php  echo $form->text('resolution_to', '', array('style' => 'width: 30px'))?>
		<?php  echo t('px'); ?>
		</span>
        
        <span class="ccm-search-option" search-field="platform">
		<?php  echo $form->text('platform')?>
		</span>
        
        <?php  foreach ((array)$f->elements as $element) { ?>
		<span class="ccm-search-option" search-field="<?php  echo 'element_'.$element->elementID ?>">
		<?php  echo $form->text('element_'.$element->elementID)?>
		</span>
		<?php  }	?>
        
	</div>
     
	
	<form class="form-horizontal" method="get" id="ccm-results-advanced-search" action="<?php   echo $cu->getToolsURL('dashboard/results/search_results', 'formidable') ?>">
	<input type="hidden" name="search" value="1" />
	
	<div class="ccm-pane-options-permanent-search">
		
        <?php  
		if (count($savedSearches) > 0) { 
			if ($_REQUEST['frssID'] < 1) {
				$_savedSearches = array('' => t('** Select a saved search.'));
			} else {
				$_savedSearches = array('' => t('** None (Exit Saved Search)'));
			}
			
			foreach($savedSearches as $key => $name) {
				$_savedSearches[$key] = $name;
			}
		?>
			<div class="control-group">
			<?php  echo $form->label('frssID', t('Saved Search'))?>
			<div class="controls">
				<?php  echo $form->select('frssID', $_savedSearches, $frssID, array('class' => 'span3', 'style' => 'vertical-align: middle'))?>
				<?php   if ($_REQUEST['frssID'] != 0) { ?>
					<a href="<?php  echo $cu->getToolsURL('dashboard/results/delete_search/?frssID='.$_REQUEST['frssID'], 'formidable'); ?>" class="ccm-results-delete-saved-search" dialog-append-buttons="true" dialog-title="<?php  echo t('Delete Saved Search')?>" dialog-width="320" dialog-height="110" dialog-modal="false" style="vertical-align: middle"><img src="<?php  echo ASSETS_URL_IMAGES?>/icons/delete_small.png" style="vertical-align: middle" width="16" height="16" border="0" /></a>
				<?php   } ?>
			</div>
			</div>
			
		<?php   } ?>
        	
		<div class="span4">
			<?php   echo $form->label('formID', t('Form(s)'))?>
			<div class="input">
				<select name="formID" id="search_formID" style="width: 225px">
				<?php   foreach((array)$forms as $fID => $label) { ?>
					<option value="<?php   echo $fID ?>" <?php   if ($fID == $_SESSION['formidable_form_id']) { ?> selected="selected" <?php   } ?>><?php   echo $label ?></option>
				<?php    } ?>
			</select>
			</div>
		</div>
        
		<div class="span3">
		<?php   echo $form->label('keywords', t('Keywords'))?>
		<div class="controls">
			<?php   echo $form->text('keywords', $_REQUEST['keywords'], array('placeholder' => t('Name or Email Address'), 'style'=> 'width: 140px')); ?>
		</div>
		</div>
		
		<div class="span3" style="width: 300px; white-space: nowrap">
		<?php   echo $form->label('numResults', t('# Per Page'))?>
		<div class="controls">
			<?php   echo $form->select('numResults', array(
				'10' => '10',
				'25' => '25',
				'50' => '50',
				'100' => '100',
				'500' => '500'
			), $_REQUEST['numResults'], array('style' => 'width:65px'))?>
		</div>

		<?php   echo $form->submit('submit_search', t('Search'), array('style' => 'margin-left: 10px'))?>

		</div>
		
	</div>

	<a href="javascript:void(0)" onclick="ccm_paneToggleOptions(this)" class="ccm-icon-option-<?php   if (is_array($searchRequest['selectedSearchField']) && count($searchRequest['selectedSearchField']) > 1) { ?>open<?php   } else { ?>closed<?php   } ?>"><?php   echo t('Advanced')?></a>
	<div class="control-group ccm-pane-options-content" <?php   if (is_array($searchRequest['selectedSearchField']) && count($searchRequest['selectedSearchField']) > 1) { ?>style="display: block" <?php   } ?>>
		<br/>
		<table class="table table-striped ccm-search-advanced-fields" id="ccm-results-search-advanced-fields">
		<?php   if ($_REQUEST['frssID'] < 1) { ?>
		<tr>
			<th colspan="2" width="100%"><?php  echo t('Additional Filters')?></th>
			<th style="text-align: right; white-space: nowrap"><a href="javascript:void(0)" id="ccm-results-search-add-option" class="ccm-advanced-search-add-field"><span class="ccm-menu-icon ccm-icon-view"></span><?php  echo t('Add')?></a></th>
		</tr>
		<?php   } ?>
		<tr id="ccm-search-field-base">
			<td><?php  echo $form->select('searchField', $searchFields);?></td>
			<td width="100%">
			<input type="hidden" value="" class="ccm-results-selected-field" name="selectedSearchField[]" />
			<div class="ccm-selected-field-content">
				<?php  echo t('Select Search Field.')?>				
			</div></td>
			<?php   if ($_REQUEST['fssID'] < 1) { ?><td><a href="javascript:void(0)" class="ccm-search-remove-option"><img src="<?php  echo ASSETS_URL_IMAGES?>/icons/remove_minus.png" width="16" height="16" /></a></td><?php   } ?>
		</tr>
		<?php   
		$i = 1;
		if (is_array($searchRequest['selectedSearchField'])) { 
			foreach($searchRequest['selectedSearchField'] as $req) { 
				if ($req == '') {
					continue;
				}
				?>				
				<tr class="ccm-search-field ccm-search-request-field-set" ccm-search-type="<?php  echo $req?>" id="ccm-results-search-field-set<?php  echo $i?>">
				<td><?php  echo $form->select('searchField' . $i, $searchFields, $req); ?></td>
				<td width="100%"><input type="hidden" value="<?php  echo $req?>" class="ccm-results-selected-field" name="selectedSearchField[]" />
					<div class="ccm-selected-field-content">
					
                    <?php   if ($req == 'collectionID') { ?>
                        <span class="ccm-search-option" search-field="collectionID">
                        	<div style="width: 100px">
                        		<?php  print Loader::helper("form/page_selector")->selectPage('collectionID', $searchRequest['collectionID']); ?>
                        	</div>
                        </span>
					<?php   } ?>
                    
                    <?php   if ($req == 'ip') { ?>
						<span class="ccm-search-option"  search-field="ip">
							<?php  echo $form->text('ip', $searchRequest['ip'])?>
						</span>
					<?php   } ?>
                    				
					<?php   if ($req == 'submitted') { ?>
						<span class="ccm-search-option ccm-search-option-type-date_time"  search-field="submitted">
							<?php  echo $form->text('submitted_from', $searchRequest['submitted_from'], array('style' => 'width: 86px'))?>
							<?php  echo t('to')?>
							<?php  echo $form->text('submitted_to', $searchRequest['submitted_to'], array('style' => 'width: 86px'))?>
						</span>
					<?php   } ?>
					
					<?php   if ($req == 'userID') { ?>
                        <span class="ccm-search-option" search-field="collectionID">
                        	<div style="width: 100px">
                        		<?php  print Loader::helper("form/page_selector")->selectPage('userID', $searchRequest['userID']); ?>
                        	</div>
                        </span>
					<?php   } ?>
					
                    <?php   if ($req == 'browser') { ?>
						<span class="ccm-search-option"  search-field="ip">
							<?php  echo $form->text('browser', $searchRequest['browser'])?>
						</span>
					<?php   } ?>
					
					<?php   if ($req == 'resolution') { ?>
                        <span class="ccm-search-option" search-field="resolution">
                        <?php  echo $form->text('resolution_from', $searchRequest['resolution_from'], array('style' => 'width: 30px'))?>
                        <?php  echo "x"; ?>
                        <?php  echo $form->text('resolution_to', $searchRequest['resolution_to'], array('style' => 'width: 30px'))?>
                        <?php  echo t('px'); ?>
                        </span>
                    <?php   } ?>
        
        			<?php   if ($req == 'platform') { ?>
						<span class="ccm-search-option"  search-field="platform">
							<?php  echo $form->text('platform', $searchRequest['platform'])?>
						</span>
					<?php   } ?>
                                                            
                    <?php  foreach ($searchElements as $key => $sf) { ?>
                    <?php  if ($key == $req) { ?>
						<span class="ccm-search-option"  search-field="<?php  echo $key; ?>">
							<?php  echo $form->text($key, $searchRequest[$key])?>
						</span>
					<?php  } ?>
                    <?php  } ?>
					</div>
					</td>
					<?php   if ($_REQUEST['frssID'] < 1) { ?>
                    	<td><a href="javascript:void(0)" class="ccm-search-remove-option"><img src="<?php  echo ASSETS_URL_IMAGES?>/icons/remove_minus.png" width="16" height="16" /></a></td>
					<?php   } ?>
					</tr>
				<?php   
					$i++;
				} 
				
				} ?>       		
		</table>
        
		<div id="ccm-search-fields-submit">
			<a href="<?php  echo $cu->getToolsURL('dashboard/results/customize_search_columns', 'formidable'); ?>" id="ccm-list-view-customize"><span class="ccm-menu-icon ccm-icon-properties"></span><?php   echo t('Customize Results')?></a>
            <a class="ccm-search-save" href="<?php  echo $cu->getToolsURL('dashboard/results/save_search', 'formidable'); ?>" id="ccm-results-launch-save-search" dialog-title="<?php  echo t('Save Search')?>" dialog-width="320" dialog-height="200" dialog-modal="false"><span class="ccm-menu-icon ccm-icon-search-pages"></span><?php  echo t('Save Search')?></a>
		</div>

	</div>	

</form>	

<script type="text/javascript">
$(function() { 
	ccm_setupResultSearch('results');
	ccm_setupAdvancedSearch('results');
	
	$('a#ccm-results-launch-save-search').dialog();
	$('a.ccm-results-delete-saved-search').dialog(); 
	
	<?php   if ($_REQUEST['frssID'] > 0) { ?>
	$('#ccm-results-advanced-search input, #ccm-results-advanced-search select, #ccm-results-advanced-search textarea').attr('disabled',true);
	$('#ccm-results-advanced-search select[name=frssID]').attr('disabled', false);
	<?php   } ?>
	
	$("form#ccm-results-advanced-search select[name=frssID]").change(function() {
		window.location.href = '?frssID=' + $(this).val();
	});
	
	$("form#ccm-results-advanced-search select[name=formID]").change(function() {
		window.location.href = '?formID=' + $(this).val();
	});
});
</script>
