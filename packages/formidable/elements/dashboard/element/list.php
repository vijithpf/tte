<?php  
defined('C5_EXECUTE') or die(_("Access Denied."));

$delete_url = 'javascript:ccmFormidableDeleteElement('.$element->elementID.');';
$duplicate_url = 'javascript:ccmFormidableDuplicateElement('.$element->elementID.');';

$edit_disabled = false;
if ($element->element_type == 'line' || $element->element_type == 'hr')
	$edit_disabled = true;

?>  
<div class="element_row_wrapper" style="display: none;" data-element_id="<?php  echo intval($element->elementID) ?>" data-combined_element_id="<?php  echo intval($element->combined_elementID) ?>">
    <table id="element_<?php  echo $element->elementID ?>" class="element_row entry ccm-results-list">
		<tr class="ccm-list-record">
			<td class="element_mover"><a href="javascript:;" class="mover ccm-menu-icon ccm-icon-move"></a></td>
			<td class="element_label show_menu" target="#options_<?php  echo $element->elementID ?>" itemID="<?php  echo $element->elementID ?>">
				<span><?php   echo $element->label ?></span>
				<br /><small><?php   echo $element->element_text ?></small>
			</td>
		</tr>
    </table>
    <div id="options_<?php  echo $element->elementID ?>" style="display:none;">
		<?php   if (!$edit_disabled) { ?>
        <a href="javascript:ccmFormidableOpenElementDialog('<?php  echo $element->element_type ?>','<?php  echo $element->element_text ?>', <?php  echo $element->layoutID ?>, <?php  echo $element->elementID ?>);" class="ccm-menu-icon ccm-icon-edit-menu"><?php   echo t('Edit') ?></a>        
        <?php   } ?>
        <?php   if (!$duplicate_disabled) { ?>
        <a href="<?php   echo $duplicate_url; ?>" class="ccm-menu-icon ccm-icon-copy-menu"><?php   echo t('Copy') ?></a>
        <?php   } ?>
        <?php   if (!$delete_disabled) { ?>
        <a href="<?php   echo $delete_url; ?>" onclick="return confirm('<?php   echo t('Are you sure you want to delete this element?'); ?>');" class="ccm-menu-icon ccm-icon-delete-menu"><?php   echo t('Delete') ?></a>
        <?php   } ?>
	</div>
</div>
