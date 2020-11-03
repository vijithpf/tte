<?php   defined('C5_EXECUTE') or die(_("Access Denied.")); ?>
<style>
.ccm-note { color: #999; font-size: 11px; font-style: italic; line-height: 14px; margin-bottom: 10px; }
.input { margin-left: 150px; } 
</style>

<div class="ccm-ui">
    
    <?php   if (sizeof($forms) > 0) { ?>
    
    <fieldset>
    <legend><?php   echo t('Select a Formidable Form')?></legend>
    <div class="clearfix">
        <?php   echo $form->label('form', t('Form Name').' <span class="ccm-required">*</span>')?>
        <div class="input">
        	<?php   echo $form->select('formID', $forms, $controller->formID);?>
        </div>
    </div>
	<div class="clearfix">
        <div class="input">
        	<a href="<?php  echo View::url('/dashboard/formidable/forms/'); ?>" class="btn"><?php   echo t('Edit forms') ?></a>
			<div class="ccm-note"><?php   echo t('Clicking will take you back to the dashboard.'); ?><br />
<?php  echo t('The current page will remain in Edit Mode!') ?></div>
        </div>
    </div>
    </fieldset>
        
    <?php   } else { ?>
    
    <strong><?php   echo t('There are no Formidable forms!') ?></strong>
    <div class="ccm-note"><?php   echo t('Go to dashboard and create a Formidable Form') ?></div>
    <div><a href="<?php   echo View::url('/dashboard/formidable/forms/'); ?>" class="btn success"><?php   echo t('Create a new FormidableForm') ?></a></div>
    
    <?php   } ?>
</div>