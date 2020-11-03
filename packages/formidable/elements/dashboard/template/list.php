<?php  
	
	defined('C5_EXECUTE') or die(_("Access Denied."));
	
	$duplicate_url = View::url('/dashboard/formidable/templates/', 'duplicate', $template->templateID);
	$edit_url = View::url('/dashboard/formidable/templates/', 'edit', $template->templateID);
	$preview_url = View::url('/dashboard/formidable/templates/', 'preview', $template->templateID);
	$delete_url = View::url('/dashboard/formidable/templates/', 'delete', $template->templateID);

?>    
    
    <table width="100%" class="entry ccm-results-list">
     <tr class="ccm-list-record">
      <td class="template_label show_menu" target="#options_<?php  echo $template->templateID ?>" itemID="<?php  echo $template->templateID ?>"><?php   echo $template->label; ?></td>
      </tr>
    </table>
    
    <div id="options_<?php  echo $template->templateID ?>" style="display:none;">
        <a href="<?php   echo $preview_url; ?>" class="ccm-menu-icon ccm-icon-view"><?php   echo t('Preview') ?></a>
        <li class="ccm-menu-separator"></li>
        <a href="<?php   echo $edit_url; ?>" class="ccm-menu-icon ccm-icon-properties-menu"><?php  echo t('Edit') ?></a>
        <li class="ccm-menu-separator"></li>
        <a href="<?php   echo $duplicate_url; ?>" class="ccm-menu-icon ccm-icon-copy-menu"><?php  echo t('Copy') ?></a>
        <a href="<?php   echo $delete_url; ?>" onclick="return confirm('<?php   echo t('Are you sure you want to delete this template?'); ?>');" class="ccm-menu-icon ccm-icon-delete-menu"><?php  echo t('Delete'); ?></a>
    </div>
    