<?php  	
defined('C5_EXECUTE') or die(_("Access Denied."));
			
if (!$ff->formID) {
	echo '<p>'.t('Can\'t find the Formidable Form').'</p>';
} else {
?>	

<div id="formidable_container_<?php  echo $ff->formID ?>" class="formidable <?php  echo $review?'review':'' ?> <?php  echo $limit_submission?'limit_submission':'' ?> <?php  echo $submission?'submission':'' ?> <?php  echo $schedule?'schedule':'' ?> <?php  echo $error?'error':'' ?> clean">
	
    <?php  if ($limit_submission || $submission || $schedule) { ?>    
        <div id="ff_msg_<?php  echo $ff->formID ?>" class="formidable_message">
            <?php  echo $submission; ?>
            <?php  echo $limit_submission; ?>
            <?php  echo $schedule; ?>
        </div>  
    <?php  } else { ?>       
        <?php  if ($error) { ?>
            <div id="ff_msg_<?php  echo $ff->formID ?>" class="formidable_message alert-error">
                <?php  foreach ((array)$error as $er) { ?>
                    <div><?php  echo $er ?></div>
                <?php  } ?>
            </div>
        <?php  } ?>
        <?php  if ($review) { ?>
            <div class="formidable_review">
                <?php  echo $review; ?>
            </div>
        <?php  } ?>
		<form id="ff_<?php  echo $ff->formID ?>" name="formidable_form" method="post" enctype="multipart/form-data" class="<?php  echo (intval($ff->css)!=0)?$ff->css_value:''?>">
			<input type="hidden" name="formID" id="formID" value="<?php  echo $ff->formID; ?>">
            <input type="hidden" name="cID" id="cID" value="<?php  echo $ff->cID; ?>">
			<input type="hidden" name="bID" id="bID" value="<?php  echo $ff->bID; ?>">
            <input type="hidden" name="resolution" id="resolution" value="">
            <input type="hidden" name="ccm_token" id="ccm_token" value="<?php  echo $ff->token; ?>">
			<?php   	
			if (sizeof($ff->layouts) > 0) {
				foreach($ff->layouts as $rowID => $row) { ?>
					<div class="formidable_row">
						<div class="inner">
						<?php 
                            $width = round(100/count($row)); $i=0;
                            foreach($row as $layoutID => $layout) { ?>
                                <div class="formidable_column<?php  echo ($i==(count($row)-1)?' last':''); ?> <?php  echo 'w'.$width; ?>">
                                    <div class="inner">
									<?php  
										echo $layout->container_start;
                                        if(count($layout->elements)) {
                                            foreach($layout->elements as $element) { ?>														
                                                <?php  if ($element->is_layout) { ?>
													<?php  echo $element->input; ?> 
                                                <?php  } else { ?>
                                                	<div class="element <?php  echo $element->element_type; ?> <?php  echo $element->handle; ?>">
														<?php  if (intval($element->label_hide) == 0) { ?>
                                                            <div class="label">
                                                                <?php  echo $form->label($element->handle, $element->label); ?>
                                                                <?php  if (intval($element->required) != 0) { ?>
                                                                    <span class="no_counter">*</span>
                                                                <?php  } ?>
                                                            </div>
                                                        <?php  } ?>
                                                        <div class="input <?php  echo intval($element->label_hide)==0?'has_label':'no_label'; ?>">
                                                            <?php  if ($review) { ?>
                                                                <span class="review"><?php  echo $element->result; ?></span>
                                                            <?php  } else { ?>													
                                                                <?php  echo $element->input; ?> 																	
                                                            <?php  } ?>                    
                                                        </div>
    
                                                        <?php  if (intval($element->option_other) != 0 && !$review) { ?>																	
                                                            <div class="input <?php  echo intval($element->label_hide)==0?'has_label':'no_label'; ?> option_other">													
                                                                <?php  echo $element->other; ?>																                 
                                                            </div>																	
                                                        <?php  } ?>
                                                    
                                                        <?php  if (intval($element->confirmation) != 0 && !$review) { ?>																	
                                                            <?php  if (intval($element->label_hide) == 0) { ?>
                                                                <div class="label">
                                                                    <?php  echo $form->label($element->handle, t('Confirm %s', $element->label)); ?>
                                                                    <?php  echo (intval($element->required) != 0)?'<span class="no_counter">*</span>':''; ?>
                                                                </div>
                                                            <?php  } ?>
                                                            <div class="input <?php  echo intval($element->label_hide)==0?'has_label':'no_label'; ?>">													
                                                                <?php  echo $element->confirm; ?> 															                 
                                                            </div>																	
                                                        <?php  } ?>
                                                    
                                                        <?php  if (intval($element->min_max) != 0 && !$review) { ?>
                                                            <div class="input <?php  echo intval($element->label_hide)==0?'has_label':'no_label'; ?>">
                                                                <div id="<?php  echo $element->handle ?>_counter" class="counter" type="<?php  echo $element->min_max_type ?>" min="<?php   echo $element->min_value ?>" max="<?php  echo $element->max_value ?>">
                                                                    <?php  if ($element->max_value > 0) { ?>
                                                                        <?php   echo t('You have') ?> <span id="<?php  echo $element->handle ?>_count"><?php  echo $element->max_value ?></span> <?php   echo ($element->min_max_type_value!='value')?$element->min_max_type_value:t('characters'); ?> <?php  echo t('left')?>.
                                                                    <?php  } ?>
                                                                </div>
                                                            </div>
                                                        <?php  } ?>
                                                        
                                                        <?php  if (intval($element->tooltip) != 0 && !$review) { ?>
                                                        	<div class="tooltip" id="<?php  echo "tooltip_".$element->elementID; ?>">
                                                            	<?php  echo $element->tooltip_value; ?>
                                                            </div>
                                                        <?php  } ?>
                                                	</div>        
                                                <?php  
													} 	
                                                }
                                            }
										echo $layout->container_stop;
										?> 
                                    </div>
                                </div>
                            <?php  $i++; ?>
                        <?php  } ?>
						</div>
					</div>
					<?php 
				}
			} ?>
            
            <?php  if (intval($ff->captcha) != 0 && !$review) { ?>
            <div class="formidable_row">
				<div class="inner">
					<div class="element">
						<?php  $captcha = Loader::helper('validation/captcha'); ?>
                        <div class="label">
							<?php  echo $form->label('ccmCaptchaCode', $ff->captcha_label); ?>
                            <span class="no_counter">*</span>
                        </div>
						<div class="input ccm_formidable_captcha">
                            <div class="captcha_image">
                                <?php  $captcha->display() ?>
                            </div>
                            <div class="captcha_input">
                                <?php  $captcha->showInput() ?>
                            </div>
                        </div>
					</div>
				</div>
			</div>
            <?php  } ?>
        
			<div class="formidable_row">
				<div class="inner">
					<div class="element">
						<div id="ff_buttons" class="buttons">
							<?php   
							if ($review) 
							{ 
								echo $form->button('reviewed_back', t('Back'), array(), 'left back');
								echo $form->submit('reviewed_submit', $text->specialchars($ff->submit_button_label), array(), 'submit primary');
							} 
							else 
							{ 
								if (intval($ff->clear_button) != 0)
									echo $form->button('reset', $text->specialchars($ff->clear_button_label), array(), 'left reset');
								
								echo $form->submit('submit', $text->specialchars($ff->submit_button_label), array(), 'submit primary');
							} ?>
							<div class="please_wait_loader"></div>
						</div>
					</div>
				</div>
			</div>
        </form>    
	<?php  
    } 
    
	if (!$review) {
		if (!empty($ff->javascript) || !empty($ff->jquery)) {	
			echo "<script>".PHP_EOL;
			if (!empty($ff->javascript)) {
				echo $ff->javascript.PHP_EOL; 
			}
			if (!empty($ff->jquery)) {
				echo "$(function() {".PHP_EOL; 
				echo $ff->jquery.PHP_EOL; 
				echo "});".PHP_EOL;
			}
			echo "</script>".PHP_EOL;
		}
	}
    ?>	
</div>
<?php  } ?>