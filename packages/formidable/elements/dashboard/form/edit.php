<?php  

	defined('C5_EXECUTE') or die(_("Access Denied."));
	 
	$form = Loader::helper('form');
	$date_time = Loader::helper('form/date_time');
	$form_page_selector = Loader::helper('form/page_selector');
	$text = Loader::helper('text');
	
?>
    <form method="post" action="<?php   echo $this->action('save')?>" id="ccm-form-record" name="formidable_form_edit">
     <fieldset>
      
	  <?php   echo $form->hidden('formID', intval($f->formID)); ?>
      <p><?php   echo t('Set the properties for your form.'); ?></p>
      <div class="clearfix">
       <?php   echo $form->label('label', t('Form name').' <span class="ccm-required">*</span>') ?>
       <div class="input">
        <?php   echo $form->text('label', $f->label, array('style' => 'width: 670px', 'placeholder' => t('My Formidable Form')))?>
       </div>
      </div>
            
      <p><?php   echo t('Use captcha on your form.'); ?></p>
      <div class="clearfix">
       <?php   echo $form->label('captcha', t('Captcha (label)'))?>
       <div class="input">
        <div class="input-prepend">
         <label class="add-on"><?php   echo $form->checkbox('captcha', 1, intval($f->captcha) != 0)?></label>
         <?php    echo $form->text('captcha_label', $f->captcha_label, array('style' => 'width: 644px;', 'placeholder' => t('Captcha')))?>
         <div class="note addon"><?php   echo t('Label for the captcha') ?></div>
        </div>
       </div>
      </div>
    
      <p><?php   echo t('Buttons'); ?></p>
      <div class="clearfix">
       <?php   echo $form->label('submit_button_label', t('Submit button label').' <span class="ccm-required">*</span>') ?>
       <div class="input">
        <?php   echo $form->text('submit_button_label', $f->submit_button_label, array('style' => 'width: 670px', 'placeholder' => t('Submit now!')))?>
       </div>
      </div>
      <div class="clearfix">
       <?php   echo $form->label('clear_button_label', t('Clear button label'))?>
       <div class="input">
        <div class="input-prepend">
         <label class="add-on"><?php   echo $form->checkbox('clear_button', 1, intval($f->clear_button) != 0)?></label>
         <?php   echo $form->text('clear_button_label', $f->clear_button_label, array('style' => 'width: 644px;', 'placeholder' => t('Clear form')))?>
         <div class="note addon"><?php   echo t('Label for the "reset form" button') ?></div>
        </div>
       </div>
      </div>
    
      <p><?php   echo t('Review the submission. If enabled, the submitter will be prompted to a preview page that lets them double check their entries before submitting the form.'); ?></p>
      <div class="clearfix">
       <?php   echo $form->label('review', t('Enable review'))?>
       <div class="input">
        <div class="input-prepend">
         <label class="add-on"><?php   echo $form->checkbox('review', 1, intval($f->review) != 0)?></label>
		 <?php   echo $form->hidden('review_content', $text->entities($f->review_content)); ?>
         <a class="btn" id="review_content_btn" href="javascript:ccmFormidableAddContent('review_content');" data-js="javascript:ccmFormidableAddContent('review_content');" ><?php   echo t('Add/Edit Description') ?></a>
         <div class="note addon"><?php   echo t('Click on the button to add/edit the content.') ?></div>
		</div>
       </div>
      </div>
    
      <p><?php   echo t('Redirect or show message after succesfully submitted'); ?></p>
      <div class="clearfix">
       <?php   echo $form->label('submission_redirect', t('After submission').' <span class="ccm-required">*</span>') ?>
       <div class="input">
        <?php   echo $form->select('submission_redirect', array(t('Show message'), t('Redirect to page')), intval($f->submission_redirect), array('style' => 'width: 670px')); ?>
       </div>
       <div style="clear:both;"><br /></div>
       <div id="submission_redirect_content">
        <div class="clearfix">
         <?php   echo $form->label('submission_redirect_content', t('Message').' <span class="ccm-required">*</span>')?>
         <div class="input">
          <?php   echo $form->hidden('submission_redirect_content', $text->entities($f->submission_redirect_content)); ?>
          <a class="btn" href="javascript:ccmFormidableAddContent('submission_redirect_content');" data-js="javascript:ccmFormidableAddContent('submission_redirect_content');" ><?php   echo t('Add/Edit Message') ?></a>
         </div>
        </div>
       </div>
       <div id="submission_redirect_page">
        <div class="clearfix">
         <?php   echo $form->label('submission_redirect_page', t('Select page').' <span class="ccm-required">*</span>')?>
         <div class="input" style="width:670px;">
          <?php   echo $form_page_selector->selectPage('submission_redirect_page', (intval($f->submission_redirect_page)!=0)?intval($f->submission_redirect_page):''); ?>
         </div>
        </div>
       </div>
      </div>
		
      <p><?php   echo t('Limit submissions'); ?></p>
      <div class="clearfix">
       <?php   echo $form->label('limit_submissions', t('Enable limits'))?>
       <div class="input">
        <div class="input-prepend">
         <label class="add-on"><?php   echo $form->checkbox('limit_submissions', 1, intval($f->limit_submissions) != 0)?></label>
         <?php   echo $form->text('limit_submissions_value', (intval($f->limit_submissions_value)==0)?'':intval($f->limit_submissions_value), array('style' => 'width: 150px;', 'placeholder' => t('Value')))?>
		 <?php   echo $form->select('limit_submissions_type', $limit_submissions_types, $f->limit_submissions_type, array('style' => 'width: 481px')); ?>
        </div>
       </div>
       <div style="clear:both;"><br /></div>
	   <div id="limit_submissions_div">
	    <div class="clearfix">
	     <?php   echo $form->label('limit_submissions_label', t('If limit reached')) ?>
         <div class="input">
           <?php   echo $form->select('limit_submissions_redirect', array(t('Show message'), t('Redirect to page')), intval($f->limit_submissions_redirect), array('style' => 'width: 670px')); ?>
         </div>
        </div>
		<div id="limit_submissions_redirect_content">
         <div class="clearfix">
          <?php   echo $form->label('limit_submissions_redirect_content', t('Message'))?>
          <div class="input">
           <?php   echo $form->hidden('limit_submissions_redirect_content', $text->entities($f->limit_submissions_redirect_content)); ?>
           <a class="btn" href="javascript:ccmFormidableAddContent('limit_submissions_redirect_content');" ><?php   echo t('Add/Edit Message') ?></a>
          </div>
         </div>
        </div>
        <div id="limit_submissions_redirect_page">
         <div class="clearfix">
          <?php   echo $form->label('limit_submissions_redirect_page', t('Select page').' <span class="ccm-required">*</span>')?>
          <div class="input" style="width:670px;">
           <?php   echo $form_page_selector->selectPage('limit_submissions_redirect_page', (intval($f->limit_submissions_redirect_page)!=0)?intval($f->limit_submissions_redirect_page):''); ?>
          </div>
         </div>
        </div>
	   </div>
      </div>
	  
	  <p><?php   echo t('Scheduling'); ?></p>
      <div class="clearfix">
       <?php   echo $form->label('schedule', t('Enable scheduling'))?>
       <div class="input">
        <div class="input-prepend">
		 <label class="add-on"><?php   echo $form->checkbox('schedule', 1, intval($f->schedule) != 0)?></label>
		</div>
	   </div>
	   <div style="clear:both;"><br /></div>
	   <div id="schedule_div">
	    <div class="clearfix">
	     <?php  echo $form->label('schedule_start', t('From')) ?>
         <div class="input">
          <?php  echo $date_time->datetime('schedule_start', strtotime($f->schedule_start)>0?$f->schedule_start:'', true, true); ?>
		  <?php  echo $form->label('schedule_end', t('To')) ?>
		  <?php  echo $date_time->datetime('schedule_end', strtotime($f->schedule_end)>0?$f->schedule_end:'', true, true); ?>
         </div>
        </div>
	    <div class="clearfix">
	     <?php  echo $form->label('schedule_label', t('If outside schedule')) ?>
         <div class="input">
           <?php  echo $form->select('schedule_redirect', array(t('Show message'), t('Redirect to page')), intval($f->scheduleredirect), array('style' => 'width: 670px')); ?>
         </div>
        </div>
		<div id="schedule_redirect_content">
         <div class="clearfix">
          <?php  echo $form->label('schedule_redirect_content', t('Message'))?>
          <div class="input">
           <?php  echo $form->hidden('schedule_redirect_content', $text->entities($f->schedule_redirect_content)); ?>
           <a class="btn" href="javascript:ccmFormidableAddContent('schedule_redirect_content');" ><?php   echo t('Add/Edit Message') ?></a>
          </div>
         </div>
        </div>
        <div id="schedule_redirect_page">
         <div class="clearfix">
          <?php   echo $form->label('schedule_redirect_page', t('Select page').' <span class="ccm-required">*</span>')?>
          <div class="input" style="width:670px;">
           <?php   echo $form_page_selector->selectPage('schedule_redirect_page', (intval($f->schedule_redirect_page)!=0)?intval($f->schedule_redirect_page):''); ?>
          </div>
         </div>
        </div>
	   </div>
      </div>
	  
	  <div class="clearfix">
       <?php   echo $form->label('html5', t('Use HTML5 elements'))?>
       <div class="input">
        <div class="input-prepend">
         <label class="add-on"><?php   echo $form->checkbox('html5', 1, intval($f->html5) != 0)?></label>
        </div>
       </div>
	  </div>
	   
	  <div class="clearfix">
       <?php   echo $form->label('css', t('CSS Classes'))?>
       <div class="input">
        <div class="input-prepend">
         <label class="add-on"><?php   echo $form->checkbox('css', 1, intval($f->css) != 0)?></label>
         <?php   echo $form->text('css_value', $f->css_value, array('style' => 'width: 644px;')); ?>
	     <div class="note addon"> <?php   echo t('Add classname(s) to customize your form. Example: myform'); ?></div>	   
        </div>
       </div>
      </div>
      
      <div class="clearfix">
       <div class="input">
        <?php   echo $form->submit('submit', t('Save').' '.t('Form Properties'), '', 'primary'); ?>
       </div>
      </div>
	
     </fieldset>	    
    </form> 