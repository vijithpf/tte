<?php  
	
	defined('C5_EXECUTE') or die(_("Access Denied."));
	
?>

<div class="ccm-ui element-overlay-body">

<?php  echo $concrete_interface->tabs($tabs); ?>

<div id="ccm-tab-content-tab-1" class="ccm-tab-content">

<?php   if (sizeof($elements) > 0) { ?>	
	 <table border="0" cellspacing="0" cellpadding="0" class="ccm-results-list" style="margin: 0px;">
      <tbody>
       <tr>
        <th class="element_label"><?php   echo t('Label / Name'); ?></th>
        <th class="element_type"><?php   echo t('Type'); ?></th>
        <th class="element_options"><?php   echo t('Option'); ?></th>
       </tr>
      </tbody>
     </table>
     
     <div class="element_row_wrapper">
     <table id="element_all" class="element_row entry ccm-results-list">
      <tr class="ccm-list-record">
       <td class="element_label"><?php   echo t('All elements in the form') ?></td>	
       <td class="element_type"><?php   echo t('All') ?></td>
       <td class="element_options">
        <a href="javascript:ccm_editorSelectFormidableElement('all_elements', '');" class="btn"><?php   echo t('Add all elements') ?></a>        
       </td>
      </tr>
     </table>
    </div>
    
<?php   foreach($elements as $element) {  ?>
	<div class="element_row_wrapper">
     <table id="element_<?php   echo $element->elementID ?>" class="element_row entry ccm-results-list">
      <tr class="ccm-list-record">
       <td class="element_label"><?php   echo $element->label ?></td>	
       <td class="element_type"><?php   echo $element->element_text ?></td>
       <td class="element_options">	          
        <?php   if ($element->is_layout) { ?>
		<a href="javascript:ccm_editorSelectFormidableElement('<?php   echo $element->handle; ?>.label', '');" class="btn"><?php   echo t('Label') ?></a>
		<?php  } else { ?>
		<a href="javascript:ccm_editorSelectFormidableElement('<?php   echo $element->handle; ?>.label', '');" class="btn"><?php   echo t('Label') ?></a>
		<a href="javascript:ccm_editorSelectFormidableElement('<?php   echo $element->handle; ?>.value', '');" class="btn"><?php   echo t('Value') ?></a>
        <a href="javascript:ccm_editorSelectFormidableElement('<?php   echo $element->handle; ?>.label', '<?php   echo $element->handle; ?>.value');" class="btn"><?php   echo t('Both') ?></a> 
		<?php  }	?>
       </td>
      </tr>
     </table>
    </div>
<?php   } ?>
	
<?php   } else { ?>
    <p>
	 <?php  echo t('No elements created yet.'); ?><br />
     <?php  echo t('Go to "Layout and Elements"-tab in this form, and add some form elements.'); ?>
    </p>
    
<?php   } ?>					

</div>

<div id="ccm-tab-content-tab-2" class="ccm-tab-content">

	<table border="0" cellspacing="0" cellpadding="0" class="ccm-results-list" style="margin: 0px;">
      <tbody>
       <tr>
        <th class="element_label"><?php   echo t('Label / Name'); ?></th>
        <th class="element_type"><?php   echo t('Type'); ?></th>
        <th class="element_options"><?php   echo t('Option'); ?></th>
       </tr>
      </tbody>
     </table>
     
     <div class="element_row_wrapper">
     <table id="advanced_all" class="element_row entry ccm-results-list">
      <tr class="ccm-list-record">
       <td class="element_label"><?php   echo t('All advanced data of the form') ?></td>	
       <td class="element_type"><?php   echo t('All') ?></td>
       <td class="element_options">
        <a href="javascript:ccm_editorSelectFormidableElement('all_advanced_data', '');" class="btn"><?php   echo t('Add all advanced data') ?></a>        
       </td>
      </tr>
     </table>
    </div>
	
<?php   foreach($advanced as $key => $advance) {  ?>
	<div class="element_row_wrapper">
     <table id="element_<?php  echo $key ; ?>" class="element_row entry ccm-results-list">
      <tr class="ccm-list-record">
       <td class="element_label"><?php   echo $advance['label'] ?> <?php   echo $advance['comment'] ?></td>	
       <td class="element_type"><?php   echo $advance['type'] ?></td>
       <td class="element_options">	          
		<a href="javascript:ccm_editorSelectFormidableElement('<?php   echo $advance['handle']; ?>.label', '');" class="btn"><?php   echo t('Label') ?></a>
		<a href="javascript:ccm_editorSelectFormidableElement('<?php   echo $advance['handle']; ?>.value', '');" class="btn"><?php   echo t('Value') ?></a>
        <a href="javascript:ccm_editorSelectFormidableElement('<?php   echo $advance['handle']; ?>.label', '<?php   echo $advance['handle']; ?>.value');" class="btn"><?php   echo t('Both') ?></a> 
       </td>
      </tr>
     </table>
    </div>
<?php   } ?>

</div>

</div>

</div>

<div class="dialog-buttons">
<a href="javascript:void(0)" class="btn" onclick="ccm_blockWindowClose()"><?php   echo t('Cancel') ?></a>
</div>
