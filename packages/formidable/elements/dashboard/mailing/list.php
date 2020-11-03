<?php  
defined('C5_EXECUTE') or die(_("Access Denied."));
?>  
<div class="mailing_row_wrapper" style="display:none;">
    <table id="mailing_<?php   echo $mailing->mailingID ?>" class="mailing_row entry ccm-results-list">
        <tr class="ccm-list-record">
            <td class="mailing_subject show_menu" target="#options_<?php  echo $mailing->mailingID ?>" itemID="<?php  echo $mailing->mailingID ?>"><?php   echo $mailing->subject ?></td>	
            <td class="mailing_from show_menu" target="#options_<?php  echo $mailing->mailingID ?>" itemID="<?php  echo $mailing->mailingID ?>"><?php   echo $mailing->from ?></td>
        </tr>
    </table>
    <div id="options_<?php  echo $mailing->mailingID ?>" style="display:none;">
        <a href="javascript:ccmFormidableOpenMailingDialog(<?php  echo $mailing->mailingID ?>);" class="ccm-menu-icon ccm-icon-edit-menu"><?php  echo t('Edit') ?></a>
        <a href="javascript:ccmFormidableDuplicateMailing(<?php  echo $mailing->mailingID ?>);" class="ccm-menu-icon ccm-icon-copy-menu"><?php  echo t('Copy') ?></a>
        <a href="javascript:ccmFormidableDeleteMailing(<?php   echo $mailing->mailingID ?>);" onclick="return confirm('Are you sure you want to delete this element?');" class="ccm-menu-icon ccm-icon-delete-menu">Delete</a>
    </div>
</div>