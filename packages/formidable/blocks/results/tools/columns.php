<?php 
    
    defined('C5_EXECUTE') or die(_("Access Denied."));

    Loader::model('block_types');
    
    $bt = BlockType::getByHandle('results');
    if (intval($_REQUEST['bID']) != 0)
        $bt = Block::getByID(intval($_REQUEST['bID']));
        
    $cnt = $bt->getController();

    $page_selector = Loader::helper("form/page_selector");
    $user_selector = Loader::helper("form/user_selector");
    $form = Loader::helper('form');

    $columnSets = $cnt->getResultsSearchColumnSet($_REQUEST['formID']);

    $fdc = $columnSets['fdc'];
    $fldc = $columnSets['fldc'];
    $fldcd = $columnSets['fldcd'];
    $fldca = $columnSets['fldca'];

    $sort = $columnSets['sort'];
    
    ?>

    <fieldset>
        <legend><?php   echo t('Choose Headers')?></legend>

        <div class="clearfix">
            <label><?php   echo t('Standard Properties')?></label>
            <div class="input">
                <ul class="inputs-list">    
                <?php      
                    foreach($fldcd->getColumns() as $col) { ?>
                        <li><label><?php  echo $form->checkbox($col->getColumnKey(), 1, '', $fldc->contains($col)?array('checked' => 'checked'):'')?> <span><?php   echo $col->getColumnName()?></span></label></li>
                 <?php  } ?>            
                </ul>
            </div>
        </div>

        <div class="clearfix">
            <label><?php   echo t('Additional Columns')?></label>
            <div class="input">
                <ul class="inputs-list">    
                <?php  foreach($fdc->getOtherColumns() as $col) { ?>
                    <li><label><?php   echo $form->checkbox($col->getColumnKey(), 1, '', $fldc->contains($col)?array('checked' => 'checked'):'')?> <span><?php   echo $col->getColumnName()?></span></label></li>
                <?php   } ?>
                </ul>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend><?php   echo t('Column Order')?></legend>
        <p><?php   echo t('Click and drag to change column order.')?></p>  
                      
        <ul class="ccm-search-sortable-column-wrapper" id="ccm-sortable-column-wrapper">
            <?php  foreach($fldc->getColumns() as $col) { ?>
                <li id="field_<?php   echo $col->getColumnKey()?>"><input type="hidden" name="column[]" value="<?php   echo $col->getColumnKey()?>" /><?php   echo $col->getColumnName()?></li>    
            <?php  } ?>  
        </ul>
                        
        <legend><?php   echo t('Sort By')?></legend>

        <div class="ccm-sortable-column-sort-controls">
            <?php  $ds = $fldc->getDefaultSortColumn(); ?>  
            <select <?php  if (count($fldc->getSortableColumns()) == 0) { ?>disabled="true"<?php  } ?> id="ccm-sortable-column-default" name="fSearchDefaultSort">
            <?php  foreach($fldc->getSortableColumns() as $col) { ?>
                <option id="opt_<?php   echo $col->getColumnKey()?>" value="<?php  echo $col->getColumnKey()?>" <?php   if ($col->getColumnKey() == $ds->getColumnKey()) { ?> selected="true" <?php    } ?>><?php   echo $col->getColumnName()?></option>
            <?php    } ?> 
                <option></option>
                <option value="rand" <?php  if ($sort == 'rand') { ?> selected="true" <?php    } ?> ><?php  echo t('Random'); ?></option>   
            </select>
            <select <?php    if (count($fldc->getSortableColumns()) == 0) { ?>disabled="true"<?php  } ?> id="ccm-sortable-column-default-direction" name="fSearchDefaultSortDirection">
                <option value="asc" <?php  if ($ds->getColumnDefaultSortDirection() == 'asc') { ?> selected="true" <?php  } ?>><?php   echo t('Ascending')?></option>
                <option value="desc" <?php  if ($ds->getColumnDefaultSortDirection() == 'desc') { ?> selected="true" <?php  } ?>><?php   echo t('Descending')?></option>   
            </select>   
        </div>
    </fieldset>

    <br><br>