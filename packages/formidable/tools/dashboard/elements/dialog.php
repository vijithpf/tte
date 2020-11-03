<?php   

defined('C5_EXECUTE') or die("Access Denied.");

$form = Loader::helper('form');
$concrete_interface = Loader::helper('concrete/interface');

$cnt = Loader::controller('/dashboard/formidable/forms/elements');			
$element = $cnt->get_element();	
	
if (!is_object($element)) { 
?>
<div class="ccm-ui element-body">
	<div class="message alert-message error dialog_message">
		<?php   echo implode('<br />', (array)$element); ?>
	</div>
</div>
<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix ccm-ui">
	<a href="javascript:void(0)" class="btn" onclick="ccm_blockWindowClose()"><?php   echo t('Cancel')?></a>
</div>  
<?php   
die(); }
?>
<style>
.ccm-ui .input-prepend input, .ccm-ui .input-append input, .ccm-ui .input-prepend select, .ccm-ui .input-append select, .ccm-ui .input-prepend .uneditable-input, .ccm-ui .input-append .uneditable-input { position:static !important;}
.ccm-ui .btn { border-radius: 4px !important; }
.note_right { padding-left: 35px; line-height: 28px;color:#666;font-size:.8em;}
.CodeMirror { height: 388px !important; }
.dependency a.mover { margin-top: 5px; margin-left: -15px; }
</style>
	
<div class="ccm-ui element-body">    
    <div class="message alert-message error dialog_message hide"></div>    
	<form id="elementForm" method="post" action="javascript:ccmFormidableCheckFormElementSubmit();void(1);">
		<?php  if ($element->formID) { ?><input type="hidden" name="formID" id="formID" value="<?php  echo $element->formID; ?>" /><?php  } ?>
        <?php  if ($element->layoutID) { ?><input type="hidden" name="layoutID" id="layoutID" value="<?php  echo $element->layoutID; ?>" /><?php  } ?>
        <?php  if ($element->elementID) { ?><input type="hidden" name="elementID" id="elementID" value="<?php  echo $element->elementID; ?>" /><?php  } ?>
    	<input type="hidden" name="element_text" id="element_text" value="<?php  echo $element->element_text; ?>" />
        <input type="hidden" name="element_type" id="element_type" value="<?php  echo $element->element_type; ?>" />
        
        <?php  
		$disabled = true;
		if ($element->elementID)
			$disabled = false;
		?>
        <ul class="nav-tabs nav" id="ccm-tabs-element-options">
            <li class="active">
                <a href="#" data-tab="properties"><?php  echo t('Properties'); ?></a>
            </li>
            <?php   if ($element->properties['handling'] !== false) { ?>
            <li>
            	<a href="#" data-tab="handling"><?php  echo t('On submission'); ?></a>
            </li> 
            <?php  } ?>
            <li>
            	<a href="#" class="<?php  echo $disabled?'disabled':''; ?>" data-tab="dependency"><?php  echo t('Dependency'); ?></a>
            </li>            
        </ul>
		<script type="text/javascript">$(function() { ccm_activateTabBar($('#ccm-tabs-element-options'));});</script>
        
        <div id="ccm-tab-content-properties" class="ccm-tab-content">        
        <fieldset>	
		<p><?php  echo t('Please set the properties of this element. Each element has his own behaviour and/or values.'); ?></p>
		<div class="clearfix">
			<?php   echo $form->label('element_type_disabled', t('Type').' <span class="ccm-required">*</span>') ?>
			<div class="input">
				<?php   echo $form->text('element_type_disabled', $element->element_text, array('style' => 'width: 510px', 'disabled' => true))?>
			</div>
		</div>
		
		<?php   if ($element->properties['label']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('label', t('Label / Name').' <span class="ccm-required">*</span>') ?>
				<div class="input">
					<?php   echo $form->text('label', $element->label, array('style' => 'width: 510px'))?>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['label_hide']) { ?> 
				<div class="clearfix">
					<?php   echo $form->label('label_hide', t('Hide label / name'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('label_hide', 1, intval($element->label_hide) != 0)?></label>
						<div class="input-append note_right"><?php  echo t('When enabled, the label of the form element will not be displayed'); ?></div>
					</div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['confirmation']) { ?> 
			<div class="clearfix">
				<?php   echo $form->label('confirmation', t('Confirmation'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('confirmation', 1, intval($element->confirmation) != 0)?></label>
						<div class="note"><?php   echo t('When enabled, duplicates field and compare both values') ?></div>
					</div>
				</div>
			</div>
		<?php   } ?>    
		
		<?php   if ($element->properties['required']) { ?> 
			<div class="clearfix">
				<?php   echo $form->label('required', t('Required'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('required', 1, intval($element->required) != 0)?></label>
						<div class="input-append note_right"><?php  echo t('When enabled, the submitted value will be required and checked'); ?></div>
					</div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['placeholder']) { ?>
		<div class="clearfix">
			<?php   echo $form->label('placeholder', t('Placeholder'))?>
			<div class="input">
				<div class="input-prepend">
					<label class="add-on-formidable"><?php   echo $form->checkbox('placeholder', 1, intval($element->placeholder) != 0)?></label>
					<?php   echo $form->text('placeholder_value', $element->placeholder_value, array('style' => 'width: 483px;')); ?>
					<?php  if ($element->properties['placeholder']['note']) { ?>
						<div class="note addon">
							<?php   echo @implode('<br />', $element->properties['placeholder']['note']); ?>
						</div>	   
					<?php  } ?>
				</div>
			</div>
		</div>
		<?php   } ?>
		
		<?php  if ($element->properties['default']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('default_value', t('Default value'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('default_value', 1, intval($element->default_value) != 0)?></label>
						<?php  echo $form->select('default_value_type', array('value' => t('Value'), 'request' => t('Request Data ($_REQUEST)'), 'user_attribute' => t('User Data'), 'collection_attribute' => t('Collection Data')), $element->default_value_type, array('style' => 'width: 493px')); ?>
       				</div>
                    <div style="clear:both;"><br /></div>
                    <div id="default_value_type_value">
						<?php   
						if ($element->properties['default']['type'] == 'textarea') 
							echo '<span style="width:60px;display:inline-block">'.t('Value').':</span>'.$form->textarea('default_value_value', $element->default_value_value, array('style' => 'width: 449px; height: 100px;'));
						else 
							echo '<span style="width:60px;display:inline-block">'.t('Value').':</span>'.$form->text('default_value_value', $element->default_value_value, array('style' => 'width: 449px;', 'data-mask' => $element->properties['default']['mask']));
						?>
						<?php  if ($element->properties['default']['note']) { ?>
							<div class="note addon">
								<?php   echo @implode('<br />', $element->properties['default']['note']); ?>
							</div>	   
						<?php  } ?>
					</div>
					<div id="default_value_type_request">
						<?php   
							echo '<span style="width:60px;display:inline-block">'.t('Value').':</span>'.$form->text('default_value_request', $element->default_value_value, array('style' => 'width: 449px;', 'data-mask' => '*?*************************************************'));
						?>
						<?php  if ($element->properties['default']['note']) { ?>
							<div class="note addon">
								<?php   echo @implode('<br />', $element->properties['default']['note']); ?>
							</div>	   
						<?php  } ?>
					</div>
                    <div id="default_value_type_user_attribute" class="default_value_type_attribute">
                    	<span style="width:60px;display:inline-block"><?php  echo t('Select') ?>:</span>
                        <select name="default_value_user_attribute" id="default_value_user_attribute" class="form-control" style="width:455px;">
                        	<optgroup label="<?php  echo t('Properties'); ?>">
                            	<?php 
									$_select = array(
										'user_id' => t('User ID'),
										'user_name' => t('Username'),
										'user_email' => t('Email Address'),
										'user_date_added' => t('Date added')
									);
									foreach ($_select as $v => $n) {
										$_sel = '';
										if ($v == $element->default_value_value)
											$_sel = 'selected';
										
										echo '<option value="'.$v.'" '.$_sel.'>'.$n.'</option>';	
									}	
								?>							
                            </optgroup>
                            <option></option>
                            <optgroup label="<?php  echo t('Attributes'); ?>">
                            <?php  
								$attribs = UserAttributeKey::getList();
								if(is_array($attribs) && count($attribs)) {
									foreach ($attribs as $at) {										
										$_sel = '';
										if ('ak_'.$at->getAttributeKeyHandle() == $element->default_value_value)
											$_sel = 'selected';
											
										echo '<option value="ak_'.$at->getAttributeKeyHandle().'" '.$_sel.'>'.$at->getAttributeKeyName().'</option>';
									}
								}
							?>
                            </optgroup>
                        </select>
                        <?php  if ($element->properties['default']['note_attribute']) { ?>
							<div class="note_attribute addon">
								<?php  echo @implode('<br />', $element->properties['default']['note_attribute']); ?>
							</div>	   
						<?php  } ?>	
                    </div>
                    <div id="default_value_type_collection_attribute" class="default_value_type_attribute">
                    	<span style="width:60px;display:inline-block"><?php  echo t('Select') ?>:</span>
                        <select name="default_value_collection_attribute" id="default_value_collection_attribute" class="form-control" style="width:455px;">
                        	<optgroup label="<?php  echo t('Properties'); ?>">
                            	<?php 
									$_select = array(
										'collection_id' => t('Collection ID'),
										'collection_name' => t('Name'),
										'collection_handle' => t('Handle'),
										'collection_type_id' => t('Page Type (ID)'),
										'collection_date_added' => t('Date added')
									);
									foreach ($_select as $v => $n) {
										$_sel = '';
										if ($v == $element->default_value_value)
											$_sel = 'selected';
										
										echo '<option value="'.$v.'" '.$_sel.'>'.$n.'</option>';	
									}	
								?>							
                            </optgroup>
                            <option></option>
                            <optgroup label="<?php  echo t('Attributes'); ?>">
                            <?php  
								$attribs = CollectionAttributeKey::getList();
								if(is_array($attribs) && count($attribs)) {
									foreach ($attribs as $at) {
										$_sel = '';
										if ('ak_'.$at->getAttributeKeyHandle() == $element->default_value_value)
											$_sel = 'selected';
											
										echo '<option value="ak_'.$at->getAttributeKeyHandle().'" '.$_sel.'>'.$at->getAttributeKeyName().'</option>';
									}
								}
							?>
                            </optgroup>
                        </select>
                        <?php  if ($element->properties['default']['note_attribute']) { ?>
							<div class="note_attribute addon">
								<?php  echo @implode('<br />', $element->properties['default']['note_attribute']); ?>
							</div>	   
						<?php  } ?>	
                    </div>
				</div>
			</div>
		<?php  } ?>
		
        <?php  if ($element->properties['content']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('content', t('Content').' <span class="ccm-required">*</span>')?>
				<div class="input">
					<?php  
					echo $form->textarea('content', $element->content, array('style' => 'width: 510px; height: 388px'));
					?>
				</div>
			</div>
		<?php  } ?>
        
		<?php  if ($element->properties['tinymce']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('tinymce_value', t('Content').' <span class="ccm-required">*</span>')?>
				<div class="input">
					<?php  
					Loader::element('editor_init');
					Loader::element('editor_config');
					Loader::element('editor_controls');
					echo $form->textarea('tinymce_value', $element->tinymce_value, array('style' => 'width: 100%; height: 354px', 'class' => 'ccm-advanced-editor'));
					?>
				</div>
			</div>
		<?php  } ?>
		
		<?php  if ($element->properties['html_code']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('html_code', t('Code').' <span class="ccm-required">*</span>')?>
				<div class="input">
					<?php  
					echo $form->textarea('html_code', $element->html_code, array('style' => 'width: 510px; height: 388px'));
					echo '<script> CodeMirror.fromTextArea(document.getElementById(\'html_code\'), { theme: \'neat\', lineNumbers: true, lineWrapping: true, tabSize: 2 }); </script>';
					?>
				</div>
			</div>
		<?php  } ?>
		
		<?php   if ($element->properties['options']) { ?>
			<div class="clearfix">
				<div class="element_options clearfix">
					<?php  echo $form->label('element_option', t('Element options'))?>
					<?php  $i = 1; ?>
					<?php  foreach((array)$element->options as $opt) { ?>
						<div class="input option_row">
							<div class="input-prepend">
								<label class="add-on-formidable">
									<?php   if ($element->element_type == 'checkbox' || ($element->element_type == 'select' && intval($element->multiple) != 0) || ($element->element_type == 'recipientselector' && intval($element->multiple) != 0)) { ?>
									<?php   echo $form->checkbox('options_selected[]', $i, $opt['selected'], array('class' => 'option_default'))?>
									<?php   } else { ?>
									<?php   echo $form->radio('options_selected[]', $i, $opt['selected']?$i:'', array('class' => 'option_default'))?>
									<?php   } ?>
								</label>
								<?php   if ($element->element_type == 'recipientselector') { ?>
									 <?php   echo $form->text('options_name['.$i.']', $opt['name'], array('style' => 'width: 200px; float:left; margin-right: 7px;', 'placeholder' => t('Name'))); ?>
									 <?php   echo $form->text('options_value['.$i.']', $opt['value'], array('style' => 'width: 200px; float:left;', 'placeholder' => t('E-mailaddress'))); ?>
								 <?php   } else { ?>
									 <?php   echo $form->text('options_name['.$i.']', $opt['name'], array('style' => 'width: 417px; float:left;', 'placeholder' => t('Option'))); ?>
								 <?php   }?>
								 <a href="javascript:;" onclick="ccmFormidableFormElementAddOptions(this);" class="btn success option_button">+</a>
								 <a href="javascript:;" onclick="ccmFormidableFormElementRemoveOptions(this);" class="btn error option_button" <?php   if (sizeof($element->options) <= 1) {?>disabled="disabled"<?php   } ?>>-</a> 
							</div>
						</div>
					<?php  $i++; ?> 
					<?php  } ?> 
				</div>
				<?php   if ($element->properties['option_other']) { ?>
					<div class="input">
						<div class="input-prepend">
							<label class="add-on-formidable"><?php   echo $form->checkbox('option_other', 1, intval($element->option_other) != 0)?></label>
							<?php   echo $form->text('option_other_value', $element->option_other_value, array('style' => 'width: 350px;', 'placeholder' => 'Other')); ?>
							<?php   echo $form->select('option_other_type', $element->properties['option_other'], $element->option_other_type, array('style' => 'width: 130px;'))?>
							<div class="note addon"><?php   echo t('When enabled, user can add a new option.') ?></div>
						</div>
					</div>
				<?php   } ?>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['multiple']) { ?> 
			<div class="clearfix">
				<?php   echo $form->label('multiple', t('Multiple options'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('multiple', 1, intval($element->multiple) != 0)?></label>
						<div class="note"><?php   echo t('When enabled, multiple options can be selected') ?></div>
					</div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['min_max']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('min_max', t('Minimum').' / '.t('Maximum'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('min_max', 1, intval($element->min_max) != 0)?></label>
						<?php   echo $form->text('min_value', $element->min_value, array('style' => 'width: 85px;', 'placeholder' => t('Minimum')))?>
						<?php   echo $form->text('max_value', (intval($element->max_value)==0)?'':intval($element->max_value), array('style' => 'width: 85px;', 'placeholder' => t('Maximum')))?>
						<?php   echo $form->select('min_max_type', $element->properties['min_max'], $element->min_max_type, array('style' => 'width: 294px;'))?>
					</div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['chars_allowed']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('chars_allowed', t('Allowed certain chars'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('chars_allowed', 1, intval($element->chars_allowed) != 0)?></label>
						<select name="chars_allowed_value[]" id="chars_allowed_value" style="width: 492px;" multiple="1" class="ccm-input-select">
							<?php  foreach ($element->properties['chars_allowed'] as $key => $option) { ?>
							<option value="<?php   echo $key ?>" <?php   echo (@in_array($key, $element->chars_allowed_value))?'selected="selected"':''; ?>><?php   echo $option ?></option>
							<?php  } ?>
						</select>
						<div class="note addon"><?php   echo t('Use CTRL (or option) to deselect or select multiple') ?></div>
					</div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['file_handling']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('file_handling', t('File handling'))?>
				<div class="input">
					<?php   echo $form->select('file_handling', $element->properties['file_handling'], $element->file_handling, array('style' => 'width: 518px;'))?>
					<div class="note"><?php   echo t('What do you want to happen when files are uploaded?') ?></div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['allowed_extensions']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('allowed_extensions', t('Allowed extensions'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('allowed_extensions', 1, intval($element->allowed_extensions) != 0)?></label>
						<?php   echo $form->textarea('allowed_extensions_value', $element->allowed_extensions_value, array('style' => 'width: 483px; height: 35px;', 'placeholder' => $element->properties['allowed_extensions']))?>
						<div class="note addon"><?php   echo t('Comma seperate each extension')?></div>
					</div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   
		if ($element->properties['fileset']) {
		
		Loader::model('file_set');
		$s1 = FileSet::getMySets();
		$sets = array();
		foreach ($s1 as $s)
			$sets[$s->fsID] = $s->fsName; ?>
			
			<div class="clearfix">
				<?php   echo $form->label('fileset', t('Assign to fileset'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('fileset', 1, intval($element->fileset) != 0, (sizeof($sets)>0)?'':array('disabled' => true))?></label>
						<?php   if (sizeof($sets) > 0) { ?>
						<?php   echo $form->select('fileset_value', $sets, $element->fileset_value, array('style' => 'width: 492px; height:28px;'))?>
						<?php   } else { ?>
						<?php   echo $form->text('fileset_value', t('No filesets available'), array('style' => 'width: 483px', 'disabled' => true))?>
						<?php   } ?>
						<div class="note addon"><?php   echo t('Assign uploaded file to selected fileset') ?></div>
					</div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['mask']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('mask', t('Enable masking')) ?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('mask', 1, intval($element->mask) != 0)?></label>
						<?php   if (is_array($element->properties['mask']['formats'])) { ?>
						<?php   echo $form->select('mask_format', $element->properties['mask']['formats'], $element->mask_format, array('style' => 'width: 492px; height:28px;'))?>
						<?php   } else { ?>
						<?php   echo $form->text('mask_format', $element->mask_format, array('style' => 'width: 483px;', 'placeholder' => $element->properties['mask']['placeholder']))?>
						<?php   } ?>
						<?php   if ($element->properties['mask']['note']) { ?>
							<div class="note addon">
								<?php   echo @implode('<br />', $element->properties['mask']['note']); ?>
							</div>	   
						<?php   } ?>
					</div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['format']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('format', t('Format').' <span class="ccm-required">*</span>') ?>
				<div class="input">
					<?php   echo $form->select('format', $element->properties['format']['formats'], $element->format, array('style' => 'width: 150px;'))?>
					<?php   echo $form->text('format_other', $element->format_other, array('style' => 'width: 355px;'))?>
					<?php   if ($element->properties['format']['note']) { ?>
					<div class="note">
						<?php   echo @implode('<br />', $element->properties['format']['note']); ?>
					</div>	   
				<?php   } ?>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['appearance']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('appearance', t('Appearance').' <span class="ccm-required">*</span>') ?>
				<div class="input">
					<?php   echo $form->select('appearance', $element->properties['appearance'], $element->appearance, array('style' => 'width: 518px;'))?>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['advanced']) { ?>
			<div class="clearfix">
			<?php   echo $form->label('advanced', t('Advanced options'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('advanced', 1, intval($element->advanced) != 0)?></label>
						<?php   echo $form->textarea('advanced_value', $element->advanced_value, array('style' => 'width: 483px; height: 70px;'))?>
						<?php   if ($element->properties['advanced']['note']) { ?>
						<div class="note addon">
							<?php   echo @implode('<br />', $element->properties['advanced']['note']); ?>
						</div>	   
					<?php   } ?>
					</div>
				</div>
			</div>
		<?php   } ?>
		
		<?php   if ($element->properties['tooltip']) { ?>
			<div class="clearfix">
				<?php   echo $form->label('tooltip', t('Tooltip / Description'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('tooltip', 1, intval($element->tooltip) != 0)?></label>
						<?php   echo $form->textarea('tooltip_value', $element->tooltip_value, array('style' => 'width: 483px; height: 70px;'))?>
					</div>
				</div>
			</div>
		<?php   } ?>
		   
		<?php   if ($element->properties['css'] !== false) { ?>
			<div class="clearfix">
				<?php   echo $form->label('css', t('CSS Classes'))?>
				<div class="input">
					<div class="input-prepend">
						<label class="add-on-formidable"><?php   echo $form->checkbox('css', 1, intval($element->css) != 0)?></label>
						<?php   echo $form->text('css_value', $element->css_value, array('style' => 'width: 483px;')); ?>
						<div class="note addon">
							<?php   echo t('Add classname(s) to customize your form field. Example: myformelement'); ?>
						</div>	   
					</div>
				</div>
			</div>
		<?php   } ?>
		
		</fieldset>
		
        </div>
        
        
        <div id="ccm-tab-content-dependency" class="ccm-tab-content"> 
        	<fieldset>
            	
            	<?php  if (!$disabled) { ?>
                                        
                    <p><?php  echo t('If you want you can use dependencies for this element. This means the behaviour or the value can be influenced with the behaviour or value of another element'); ?></p>                
                    <div id="dependencies_rules" data-next_rule="100">
                        <?php 
                            if (!empty($element->dependencies)) {
                                foreach ($element->dependencies as $rule => $dependency) {
                                    echo $cnt->dependency($element->elementID, $rule);
                                }
                            }
                        ?>
                    </div>
                    
                    <?php   echo $concrete_interface->button_js(t('Add dependency rule'), 'ccmFormidableAddDependency(\''.$element->elementID.'\')', 'right'); ?>
    			<?php  } else { ?>
                	 <div class="alert alert-info">
                     	<strong><?php  echo t('Note:'); ?></strong> <?php  echo t('You have to save the element first before you can add dependencies to this element.'); ?>
                     </div>      
                <?php  } ?>
            </fieldset>
        </div> 
        
        
        <?php   if ($element->properties['handling'] !== false) { ?>
        
        <div id="ccm-tab-content-handling" class="ccm-tab-content"> 
        	<fieldset>            
            
               <div class="alert alert-danger">
                   <strong><?php  echo t('Warning:'); ?></strong> <?php  echo t('This element can overwrite certain values within you Concrete5 installation. There will be no validation here, so if you overwrite the users username it will try to save that value. This means it could break your site! Please be aware of this!!!'); ?>
               </div>
                   
               <div class="clearfix">
					<?php   echo $form->label('submission_update', t('Update at submission'))?>
                    <div class="input">
                        <div class="input-prepend">
                            <label class="add-on-formidable"><?php   echo $form->checkbox('submission_update', 1, intval($element->submission_update) != 0)?></label>
                            <?php  echo $form->select('submission_update_type', array('user_attribute' => t('User Data'), 'collection_attribute' => t('Collection Data')), $element->submission_update_type, array('style' => 'width: 493px')); ?>
                        </div>
                        <div style="clear:both;"><br /></div>                    
                        <div id="submission_update_type_user_attribute" class="submission_update_type_attribute">
                            <span style="width:60px;display:inline-block"><?php  echo t('Select') ?>:</span>
                            <select name="submission_update_user_attribute" id="submission_update_user_attribute" class="form-control" style="width:455px;">
                                <optgroup label="<?php  echo t('Properties'); ?>">
                                    <?php 
                                        $_select = array(
                                            'user_name' => t('Username'),
                                            'user_email' => t('Email Address'),
											'user_password' => t('Password'),
                                            'user_date_added' => t('Date added')
                                        );
                                        foreach ($_select as $v => $n) {
                                            $_sel = '';
                                            if ($v == $element->submission_update_value)
                                                $_sel = 'selected';
                                            
                                            echo '<option value="'.$v.'" '.$_sel.'>'.$n.'</option>';	
                                        }	
                                    ?>							
                                </optgroup>
                                <option></option>
                                <optgroup label="<?php  echo t('Attributes'); ?>">
                                <?php  
                                    $attribs = UserAttributeKey::getList();
                                    if(is_array($attribs) && count($attribs)) {
                                        foreach ($attribs as $at) {										
                                            $_sel = '';
                                            if ('ak_'.$at->getAttributeKeyHandle() == $element->submission_update_value)
                                                $_sel = 'selected';
                                                
                                            echo '<option value="ak_'.$at->getAttributeKeyHandle().'" '.$_sel.'>'.$at->getAttributeKeyName().'</option>';
                                        }
                                    }
                                ?>
                                </optgroup>
                            </select>	
                        </div>
                        <div id="submission_update_type_collection_attribute" class="submission_update_type_attribute">
                            <span style="width:60px;display:inline-block"><?php  echo t('Select') ?>:</span>
                            <select name="submission_update_collection_attribute" id="submission_update_collection_attribute" class="form-control" style="width:455px;">
                                <optgroup label="<?php  echo t('Properties'); ?>">
                                    <?php 
                                        $_select = array(
                                            'collection_name' => t('Name'),
                                            'collection_handle' => t('Handle'),
                                            'collection_date_added' => t('Date added')
                                        );
                                        foreach ($_select as $v => $n) {
                                            $_sel = '';
                                            if ($v == $element->submission_update_value)
                                                $_sel = 'selected';
                                            
                                            echo '<option value="'.$v.'" '.$_sel.'>'.$n.'</option>';	
                                        }	
                                    ?>							
                                </optgroup>
                                <option></option>
                                <optgroup label="<?php  echo t('Attributes'); ?>">
                                <?php  
                                    $attribs = CollectionAttributeKey::getList();
                                    if(is_array($attribs) && count($attribs)) {
                                        foreach ($attribs as $at) {
                                            $_sel = '';
                                            if ('ak_'.$at->getAttributeKeyHandle() == $element->submission_update_value)
                                                $_sel = 'selected';
                                                
                                            echo '<option value="ak_'.$at->getAttributeKeyHandle().'" '.$_sel.'>'.$at->getAttributeKeyName().'</option>';
                                        }
                                    }
                                ?>
                                </optgroup>
                            </select>                    
                        </div>
            		</div>
                </div>
                
                <div class="clearfix submission_update">
					<?php   echo $form->label('submission_update_empty', t('Skip if empty'))?>
                    <div class="input">
                        <div class="input-prepend">
                            <label class="add-on-formidable"><?php   echo $form->checkbox('submission_update_empty', 1, intval($element->submission_update_empty) != 0)?></label>
                            <div class="note"><?php   echo t('If the value of this element is empty, skip saving the data into the selected propertie or attribute') ?></div>
                        </div>
                    </div>
                </div>
        	</fieldset>
            
        </div>         
        <?php  } ?>
    
    </form>
    
</div>
		
<div class="dialog-buttons">
	<?php   echo $concrete_interface->button_js(t('Cancel'), 'ccm_blockWindowClose()', 'left'); ?>
	<?php   echo $concrete_interface->button_js(t('Save'), '$(\'#elementForm\').trigger(\'submit\')', 'right', 'primary'); ?>
</div>  

<script>
$(function() {
	
	<?php  if (intval($element->elementID) == 0) { ?>
		$('input[id=label]').focus();
	<?php  } ?>
		   
	ccmFormidableFormElementCheckSelectors();
	
	$("input[name=mask]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=placeholder]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=default_value]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("select[name=default_value_type]").change(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=min_max]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=tooltip]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=chars_allowed]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=option_other]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=multiple]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=fileset]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=allowed_extensions]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input.option_default").mousedown(function() {
		$(this).data('wasChecked', this.checked);
	});
	$("input.option_default").click(function() {
		if ($(this).data('wasChecked'))
			this.checked = false;
	});
	$("input[name=advanced]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("select[name=format]").change(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("select[name=appearance]").change(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=css]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("input[name=submission_update]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	$("select[name=submission_update_type]").change(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	<?php  
		if (!empty($element->dependencies)) {
			foreach($element->dependencies as $rule => $dependency) {
	?>
	setTimeout(function() { 
		ccmFormidableInitDependency('<?php  echo $element->elementID; ?>', '<?php  echo $rule; ?>');
	<?php  foreach((array)$dependency->actions as $action_rule => $a) { ?>
		ccmFormidableInitDependencyAction('<?php  echo $element->elementID; ?>', '<?php  echo $rule; ?>', '<?php  echo $action_rule; ?>');
	<?php  } ?>
	<?php  foreach((array)$dependency->elements as $element_rule => $e) { ?>
		ccmFormidableInitDependencyElement('<?php  echo $element->elementID; ?>', '<?php  echo $rule; ?>', '<?php  echo $element_rule; ?>');
	<?php  } ?> 
	}, <?php  echo 200*intval($rule); ?>);
	<?php  		
			}
		}
	?>
	
	$("#dependencies_rules").sortable({
		items: "div.dependency",
		handle: "a.mover",
		sort: function(event, ui) {
			$(this).removeClass( "ui-state-default" );
		},
		stop: function(event, ui) {
			$("#dependencies_rules").find('.dependency').each(function(i, row) {
				$(row).find('span.rule').text(i + 1);
				if (i == 0) $(row).find('div.operator').hide();	
				else $(row).find('div.operator').show();
			});
		}
	});
		
	$('.element-body').parent('.ui-dialog-content').addClass('formidable-dialog-content');
	
});

</script> 