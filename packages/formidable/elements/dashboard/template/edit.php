<?php  
	defined('C5_EXECUTE') or die(_("Access Denied."));
	 
	$form = Loader::helper('form');
	$text = Loader::helper('text');
	
?>
    <form method="post" action="<?php   echo $this->action('save')?>" id="ccm-form-record" name="formidable_form_edit">
     <fieldset>
      
	  <?php   echo $form->hidden('templateID', intval($template->templateID)); ?>
      <p><?php   echo t('Set the properties for your template.'); ?></p>
      <div class="clearfix">
       <?php   echo $form->label('label', t('Template name').' <span class="ccm-required">*</span>') ?>
       <div class="input">
        <?php   echo $form->text('label', $template->label, array('style' => 'width: 670px', 'placeholder' => t('My Formidable Template')))?>
       </div>
      </div>
                
      <div class="clearfix">
       <?php   echo $form->label('template', t('Template').' <span class="ccm-required">*</span>') ?>
       <div class="input">
        <?php  
         Loader::element('editor_init');
         Loader::element('editor_config');
         Loader::packageElement('editor_controls', 'formidable', array('mode' => Config::get('CONTENTS_TXT_EDITOR_MODE'), 'template' => true));
         echo $form->textarea('template', $template->template, array('style' => 'width: 100%; height: 800px', 'class' => 'ccm-advanced-editor'));
        ?>       
       </div>
      </div>

      <div class="clearfix">
       <div class="input">
        <?php   echo $form->submit('submit', t('Save'), '', 'primary'); ?>
       </div>
      </div>
	
     </fieldset>	    
    </form> 