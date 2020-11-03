<?php   
defined('C5_EXECUTE') or die("Access Denied.");

$form = Loader::helper('form');
$concrete_interface = Loader::helper('concrete/interface');

$cnt = Loader::controller('/dashboard/formidable/forms/elements');
$layout = $cnt->get_layout();

$_appearances = array('default' => t('Div'), 
					  'fieldset' => t('Fieldset (with legend, if label exists)'));
 
if (!is_object($layout) && !is_array($layout)) { 
?>
<div class="ccm-ui element-body">
	<div class="message alert-message error dialog_message">
		<?php   echo implode('<br />', (array)$layout); ?>
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
.ccm-ui .btn { border-radius: 4px !important; }
</style>

<div class="ccm-ui layout-body element-body">
	<div class="message alert-message error dialog_message hide"></div>    
	<form id="layoutForm" method="post" action="javascript:ccmFormidableCheckFormLayoutSubmit();void(1);">	
		<?php   echo $form->hidden('formID', $_REQUEST['formID']); ?>
		<?php   echo $form->hidden('layoutID', $_REQUEST['layoutID']); ?>
		<?php   echo $form->hidden('rowID', $_REQUEST['rowID']); ?>	
		<fieldset>
			<?php  if ($layout->layoutID > 0) { ?>
				<p><?php  echo t('Set the properties of the column'); ?></p>

				<div class="clearfix">
					<?php   echo $form->label('label', t('Label / Name')) ?>
					<div class="input">
						<?php   echo $form->text('label', $layout->label, array('style' => 'width: 510px'))?>
					</div>
				</div>
				
                <div class="clearfix">
                    <?php   echo $form->label('appearance', t('Appearance').' <span class="ccm-required">*</span>') ?>
                    <div class="input">
                        <?php   echo $form->select('appearance', $_appearances, $layout->appearance, array('style' => 'width: 518px;'))?>
                    </div>
                </div>
                
				<div class="clearfix">
					<?php   echo $form->label('css', t('CSS Classes'))?>
					<div class="input">
						<div class="input-prepend">
							<label class="add-on-formidable"><?php   echo $form->checkbox('css', 1, intval($layout->css) != 0)?></label>
							<?php   echo $form->text('css_value', $layout->css_value, array('style' => 'width: 483px;')); ?>
							<div class="note addon">
								<?php   echo t('Add classname(s) to customize your form field. Example: myformelement'); ?>
							</div>	   
						</div>
					</div>
				</div>
			<?php  } else { ?>
				<p><?php  echo t('Set the properties of the row'); ?></p>
				
				<div class="clearfix">
					<?php   echo $form->label('cols', t('Number of columns').' <span class="ccm-required">*</span>') ?>
					<div class="input">
						<?php  echo $form->select('cols', array(1=>1, 2=>2, 3=>3, 4=>4, 5=>5), count($layout))?>
						<div class="note">
							<?php   echo t('If you want to have less columns, empty them first.'); ?>
						</div>	
					</div>
				</div>
		    <?php  } ?>
		</fieldset>
	</form>
</div>

<div class="dialog-buttons">
	<?php   echo $concrete_interface->button_js(t('Cancel'), 'ccm_blockWindowClose()', 'left'); ?>
    <?php   echo $concrete_interface->button_js(t('Save'), '$(\'#layoutForm\').trigger(\'submit\')', 'right', 'primary'); ?>
</div> 

<script>
$(function() {
	ccmFormidableFormElementCheckSelectors();
	$("input[name=css]").click(function() {
		ccmFormidableFormElementCheckSelectors($(this));
	});
	
	$('.layout-body').parent('.ui-dialog-content').addClass('formidable-dialog-content');
});
</script> 