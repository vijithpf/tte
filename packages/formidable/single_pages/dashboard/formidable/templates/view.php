<?php   
defined('C5_EXECUTE') or die(_("Access Denied."));
echo $concrete_dashboard->getDashboardPaneHeaderWrapper(t('Formidable Templates').($create_form?' - '.t('Properties'):''), t('List of Formidable Templates. If the list is empty, please add one.'), false, false);

if ($create_template) { ?>

<div class="ccm-pane-body"> 
    <?php  echo Loader::packageElement('dashboard/template/edit', 'formidable', array('template' => $template))?>
</div>
<?php  } elseif ($preview_template) { ?>
    
<div class="ccm-pane-body"> 
    <?php  echo Loader::packageElement('dashboard/template/preview_nav', 'formidable', array('template' => $template))?>
    <?php  echo Loader::packageElement('dashboard/template/preview', 'formidable', array('template' => $template))?>
</div>    

<?php  } else { ?>
<div class="ccm-pane-body">    
    <div style=" float: right; margin-bottom:8px; height:38px;">
        <?php   echo $concrete_interface->button(t('Add new'), View::url('/dashboard/formidable/templates/add'), array(), 'success');?>
    </div>
<?php   if (sizeof($templates) > 0) { ?>
<div style="clear:both;"></div>
<table border="0" cellspacing="0" cellpadding="0" class="ccm-results-list no_bottom_margin">
    <tbody>
        <tr>
            <th class="form_label"><?php   echo t('Template Title'); ?></th>
        </tr>
    </tbody>
</table>
<div class="ccm-form-list" id="ccm-form-list">
<?php   
foreach($templates as $template) { 
    echo Loader::packageElement('dashboard/template/list', 'formidable', array('template' => $template));
}
echo $template_list->displayPagingV2(); 
?>
</div>
<?php  } else { ?>
    <p><?php   echo t('You have not created any templates.'); ?></p>
<?php  } ?>
</div>
<?php  } ?>
<div class="ccm-pane-footer">
</div>  
<?php  echo $concrete_dashboard->getDashboardPaneFooterWrapper(false)?>

<script>
$(function() {    
    ccmFormidableCreateMenu();
});
</script>