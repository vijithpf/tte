<?php  
	
	defined('C5_EXECUTE') or die(_("Access Denied."));
	
	$results_url = View::url('/dashboard/formidable/results/?formID='.$form->formID);
	$duplicate_url = View::url('/dashboard/formidable/forms/', 'duplicate', $form->formID);
	$edit_url = View::url('/dashboard/formidable/forms/', 'edit', $form->formID);
	$preview_url = View::url('/dashboard/formidable/forms/', 'preview', $form->formID);
	$elements_url = View::url('/dashboard/formidable/forms/', 'elements', $form->formID);
	$mailings_url = View::url('/dashboard/formidable/forms/', 'mailings', $form->formID);
	$delete_url = View::url('/dashboard/formidable/forms/', 'delete', $form->formID);

?>    
    
    <table width="100%" class="entry ccm-results-list">
     <tr class="ccm-list-record">
      <td class="form_label show_menu" target="#options_<?php  echo $form->formID ?>" itemID="<?php  echo $form->formID ?>"><?php   echo $form->label; ?></td>
      <td class="form_last_submission show_menu" align="center" target="#options_<?php  echo $form->formID ?>" itemID="<?php  echo $form->formID ?>"><?php   echo $form->last_submission; ?></td> 
      <td class="form_submissions show_menu" align="center" target="#options_<?php  echo $form->formID ?>" itemID="<?php  echo $form->formID ?>"><?php   echo $form->submissions; ?></td>      
     </tr>
    </table>
    
    <div id="options_<?php  echo $form->formID ?>" style="display:none;">
    	<a href="<?php   echo $results_url; ?>" class="ccm-menu-icon ccm-icon-sets"><?php   echo t('Results') ?></a>
        <a href="<?php   echo $preview_url; ?>" class="ccm-menu-icon ccm-icon-view"><?php   echo t('Preview') ?></a>
        <li class="ccm-menu-separator"></li>
        <a href="<?php   echo $edit_url; ?>" class="ccm-menu-icon ccm-icon-properties-menu"><?php  echo t('Form Properties') ?></a>
        <a href="<?php   echo $elements_url; ?>" class="ccm-menu-icon ccm-icon-add-layout-menu"><?php  echo t('Layout and elements') ?></a>
        <a href="<?php   echo $mailings_url; ?>" class="ccm-menu-icon ccm-icon-edit-menu"><?php  echo t('Emails') ?></a> 
        <li class="ccm-menu-separator"></li>
        <a href="<?php   echo $duplicate_url; ?>" class="ccm-menu-icon ccm-icon-copy-menu"><?php  echo t('Copy') ?></a>
        <a href="<?php   echo $delete_url; ?>" onclick="return confirm('<?php   echo t('Are you sure you want to delete this form?'); ?>');" class="ccm-menu-icon ccm-icon-delete-menu"><?php  echo t('Delete'); ?></a>
    </div>
    