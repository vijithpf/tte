<?php 
defined('C5_EXECUTE') or die("Access Denied.");

$form = Loader::helper('form');
$assets = Loader::helper('concrete/asset_library');		
$concrete_interface = Loader::helper('concrete/interface');

$cnt = Loader::controller('/dashboard/formidable/forms/mailings');	
$mailing = $cnt->get_mailing();		

$templates = $cnt->get_templates();   
	
if (!is_object($mailing)) { 
?>
	<div class="ccm-ui element-body">
     <div class="message alert-message error dialog_message">
      <?php   echo implode('<br />', (array)$mailing); ?>
     </div>
    </div>
	<div class="dialog-buttons">     
    	<a href="javascript:void(0)" class="btn" onclick="ccm_blockWindowClose()"><?php   echo t('Cancel')?></a>
    </div>  
<?php   
	die(); }
?>
<style>
.ccm-ui .input-prepend input, .ccm-ui .input-append input, .ccm-ui .input-prepend select, .ccm-ui .input-append select, .ccm-ui .input-prepend .uneditable-input, .ccm-ui .input-append .uneditable-input { position:static !important;}
</style>
		
    <div class="ccm-ui element-body">
    
    <div class="message alert-message error dialog_message hide"></div>
    
    <?php 
	$tabs = array(
		array('from', t('Send from'), true),
		array('to', t('Send to')),
		array('message', t('Message')),
		array('attachments', t('Attachments'))
	);
	// Print tab element
	echo Loader::helper('concrete/interface')->tabs($tabs);
	?>    
	<form id="mailingForm" method="post" action="javascript:ccmFormidableCheckFormMailingSubmit();void(1);">
	<?php   echo (intval($mailing->formID)!=0?$form->hidden('formID', $mailing->formID):''); ?>
	<?php   echo (intval($mailing->mailingID)!=0?$form->hidden('mailingID', $element->mailingID):''); ?>
    
    <div id="ccm-tab-content-from" class="ccm-tab-content">
    <fieldset>	
    <p><?php  echo t('Set the "From"-header of the mail. You can either select a form element or add a custom one. You can also set the "Reply-To"-header of the mail when you use a custom "From"-header. This is for the advanced user.'); ?></p>
    
    <div class="clearfix">
     <?php   echo $form->label('from_name', t('From').' <span class="ccm-required">*</span>') ?>
     <div class="input">	 
     <?php   echo $form->select('from_type', $mailing->from, $mailing->from_type, array('style' => 'width: 555px;'))?>
     </div>
     <br />
     <div class="input">
      <?php   echo $form->text('from_name', $mailing->from_name, array('style' => 'width: 266px;', 'placeholder' => t('Name')))?>
	  <?php   echo $form->text('from_email', $mailing->from_email, array('style' => 'width: 266px;', 'placeholder' => t('E-mailaddress')))?>
     </div>
	</div>
	
	<div class="clearfix reply_to">
     <?php   echo $form->label('reply_name', t('Reply To').' <span class="ccm-required">*</span>') ?>
     <div class="input">	 
     <?php   echo $form->select('reply_type', $mailing->reply_to, $mailing->reply_type, array('style' => 'width: 555px;'))?>
     </div>
     <br />
     <div class="input">
      <?php   echo $form->text('reply_name', $mailing->reply_name, array('style' => 'width: 266px;', 'placeholder' => t('Name')))?>
	  <?php   echo $form->text('reply_email', $mailing->reply_email, array('style' => 'width: 266px;', 'placeholder' => t('E-mailaddress')))?>
     </div>
	</div>
    </fieldset>
    </div>
    
    <div id="ccm-tab-content-to" class="ccm-tab-content">
    <fieldset>
    <p><?php  echo t('You can send a mail to multiple addresses. You can select "Send to"-elements if you have them in your form. You can also add custom Email Address(es) in the field below.'); ?></p>
    <div class="clearfix">
     <?php   echo $form->label('send', t('Send to').' <span class="ccm-required">*</span>')?>
     <div class="input">
      <?php   if (sizeof($mailing->send_to) > 0) { ?>
	   <div class="checkbox">
       <?php   foreach ($mailing->send_to as $key => $option) { ?>
        <label><input type="checkbox" name="send[]" value="<?php   echo $key ?>" <?php   echo (@in_array($key, $mailing->send))?'checked="checked"':''; ?> class="ccm-input-checkbox"> <?php   echo $option ?></label>
       <?php   } ?>
       </div>
       <span style="padding: 10px 0px 5px 0px;display: block;"><strong><?php  echo t('AND/OR'); ?></strong></span>       
      <?php   } else { ?>
       <div class="no_send_to_select">
        <?php   echo t('Add an "Email Address" or a "Recipient Selector" element(s) to enable selection of recipient.') ?><br />
        <?php   echo t('You must add custom e-mailaddress now:') ?>
       </div>
	  <?php   } ?>
     </div>
     <br />
     <?php   echo $form->label('send', t('E-mail Address(es)').' <span class="ccm-required">*</span>')?>
	 <div class="input">
      <div class="input-prepend">
       <label class="add-on-formidable"><?php   echo $form->checkbox('send_custom', 1, intval($mailing->send_custom) != 0)?></label>
       <?php   echo $form->textarea('send_custom_value', $mailing->send_custom_value, array('style' => 'width: 522px; height: 35px;')); ?>
       <div class="note addon"><?php   echo t('Comma seperate each e-mailaddress') ?></div>
      </div>
     </div>
    </div>
    
    <div class="clearfix">
     <?php   echo $form->label('send_cc', t('Send as CC'))?>
     <div class="input">
      <div class="input-prepend">
       <label class="add-on"><?php   echo $form->checkbox('send_cc', 1, intval($mailing->send_cc) != 0)?></label>
       <div class="no_send_to_select">
        <?php   echo t('Use CC instead of BCC (default) for sending mail') ?>
       </div>
      </div>
     </div>
	</div>
    
    </fieldset>
    </div>
    
    <div id="ccm-tab-content-message" class="ccm-tab-content">
    <fieldset>
    <p><?php  echo t('Define your mail here. Add a subject and a HTML-message. In your message and subject you can use element values and labels of the form by clicking on "Insert Formidable Element" in the top bar of the editor.'); ?></p>
           
    <div class="clearfix">
     <?php   echo $form->label('subject', t('Subject').' <span class="ccm-required">*</span>') ?>
     <div class="input">
      <?php   echo $form->text('subject', $mailing->subject, array('style' => 'width: 548px'))?>
      <div class="no_send_to_select"> 
       <a href="#" onclick="ccm_editorFormidableSubjectOverlay(<?php   echo $mailing->formID ?>);"><?php   echo t('Insert Formidable Element')?></a>
      </div>
     </div>
    </div>
    
    <div class="clearfix">
     <?php   echo $form->label('templateID', t('Use template')) ?>
     <?php   if ($templates) { ?>
        <div class="input">
          <div class="input-prepend">
            <label class="add-on-formidable"><?php   echo $form->checkbox('template', 1, intval($mailing->template) != 0)?></label>
             <?php   echo $form->select('templateID', $templates, $mailing->templateID, array('style' => 'width: 532px;'))?>
          </div>
        </div>
      <?php   } else { ?>
      <div class="no_attachments_select">
        <?php   echo t('Not templates available.') ?><br />
        <?php   echo t('It\'s not required, its just that your mail will use no template'); ?>
       </div>
      <?php   } ?>
    </div>

    <div class="clearfix">
     <?php   echo $form->label('message', t('Message').' <span class="ccm-required">*</span>') ?>
     <div class="input">
      <div class="mail_message">
	  <?php  
       Loader::element('editor_init');
       Loader::element('editor_config');
	     Loader::packageElement('editor_controls', 'formidable', array('mode' => Config::get('CONTENTS_TXT_EDITOR_MODE'), 'form_id' => $mailing->formID));
       echo $form->textarea('message', $mailing->message, array('style' => 'width: 100%; height: 354px', 'class' => 'ccm-advanced-editor'));
	  ?>
      </div>
     </div>
    </div>
    
    <div class="clearfix">
     <?php   echo $form->label('discard_empty', t('Discard empty'))?>
     <div class="input">
      <div class="input-prepend">
       <label class="add-on"><?php   echo $form->checkbox('discard_empty', 1, intval($mailing->discard_empty) != 0)?></label>
       <div class="no_send_to_select">
        <?php   echo t('If checked empty elements will not be shown in message') ?>
       </div>
      </div>
     </div>
	</div>
    
    <div class="clearfix">
     <?php   echo $form->label('discard_layout', t('Discard layout'))?>
     <div class="input">
      <div class="input-prepend">
       <label class="add-on"><?php   echo $form->checkbox('discard_layout', 1, intval($mailing->discard_layout) != 0)?></label>
       <div class="no_send_to_select">
        <?php   echo t('If checked layout elements will not be shown in message') ?>
       </div>
      </div>
     </div>
	</div>
      
    </fieldset>
    </div>
    
    <div id="ccm-tab-content-attachments" class="ccm-tab-content">
    <fieldset>
    <p><?php  echo t('With your mailing you can send attachments. You can add the files from "Upload Files"-elements in your form. You can also add files from the filemanager.'); ?></p>
    <div class="clearfix">
	 <?php   echo $form->label('attachment', t('Uploaded Files'))?>
	 <div class="input">
      <div class="input-prepend">
       <?php   if (sizeof($mailing->uploader_elements) > 0) { ?>
	   <label class="add-on-formidable"><?php   echo $form->checkbox('attachments_element', 1, intval($mailing->attachments_element) != 0)?></label>       
	   <select name="attachments_element_value[]" id="attachments_element_value" style="width:532px;height:45px;" multiple="1" class="ccm-input-select">
       <?php   foreach ($mailing->uploader_elements as $key => $option) { ?>
        <option value="<?php   echo $key ?>" <?php   echo (@in_array($key, $mailing->attachments_element_value))?'selected="selected"':''; ?>><?php   echo $option ?></option>
       <?php   } ?>
       </select>
       <div class="note addon"><?php   echo t('Use CTRL (or option) to deselect or select multiple') ?></div>
      <?php   } else { ?>
       <div class="no_attachments_select">
        <?php   echo t('Add an "Upload Files"-element to enable selection of attachments.') ?><br />
		    <?php   echo t('You can select attachments from the filemanager:') ?>
       </div>
	  <?php   } ?>
      </div>
     </div>
    </div>
    <div class="clearfix"> 
     <?php   echo $form->label('attachment', t('From Filemanager'))?>
     <div class="attachments_options clearfix">
      <?php   $i = 1; foreach($mailing->attachments as $attach) { ?>
       <div class="input attachment_row">
		<div class="file_selector">
		 <?php   $_POST['attachment['.$i.']'] = $attach; ?>
         <?php   echo $assets->file('attachment_'.$i, 'attachment['.$i.']', t('Choose file')); ?>
        </div>
        <a href="javascript:;" onclick="ccmFormidableFormMailingAddAttachment(this);" class="btn success option_button">+</a>
        <a href="javascript:;" onclick="ccmFormidableFormMailingRemoveAttachment(this);" class="btn error option_button" <?php   if (sizeof($mailing->attachments) <= 1) {?>disabled="disabled"<?php   } ?>>-</a> 
       </div>
      <?php   $i++; } ?>
      <div class="input"> 
       <div class="note">
        <?php   echo t('Be careful with size and extensions, some spamfilters can block your mail.') ?><br />
       </div>
      </div>
     </div>
    </div>
    </fieldset>
    </div>
    
    </form>
    </div>
    
	<div class="dialog-buttons">	  
	  <?php   echo $concrete_interface->button_js(t('Cancel'), 'ccm_blockWindowClose()', 'left'); ?>
	  <?php   echo $concrete_interface->button_js(t('Save'), '$(\'#mailingForm\').trigger(\'submit\')', 'right', 'primary'); ?>
	</div>  

<script>
$(function() 
{
	ccmFormidableFormMailingCheckSelectors();
	
	$("select[name=from_type]").change(function() {
		ccmFormidableFormMailingCheckSelectors($(this));
	});	
	$("select[name=reply_type]").change(function() {
		ccmFormidableFormMailingCheckSelectors($(this));
	});	
	$("input[name=send_custom]").click(function() {
		ccmFormidableFormMailingCheckSelectors($(this));
	});
	$("input[name=attachments_element]").click(function() {
		ccmFormidableFormMailingCheckSelectors($(this));
	});	
	$('input[name=from_name]').keydown(function() {
		$('input[name=reply_name]').val($(this).val());
	});
	$('input[name=from_email]').keydown(function() {
		$('input[name=reply_email]').val($(this).val());
	});
  $('input[name=template]').click(function() {
    ccmFormidableFormMailingCheckSelectors($(this));
  });
	
	$('.element-body').parent('.ui-dialog-content').addClass('formidable-dialog-content');
	
});
</script> 