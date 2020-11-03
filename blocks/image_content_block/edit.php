<?php  defined('C5_EXECUTE') or die("Access Denied.");
$al = Loader::helper('concrete/asset_library');
$ps = Loader::helper('form/page_selector');
Loader::element('editor_config');
?>

<style type="text/css" media="screen">
	.ccm-block-field-group h2 { margin-bottom: 5px; }
	.ccm-block-field-group td { vertical-align: middle; }
</style>

<div class="ccm-block-field-group">
	<h2>Background Image</h2>
	<?php  echo $al->image('field_1_image_fID', 'field_1_image_fID', 'Choose Image', $field_1_image); ?>
</div>

<div class="ccm-block-field-group">
	<h2>Content</h2>
	<?php  Loader::element('editor_controls'); ?>
	<textarea id="field_2_wysiwyg_content" name="field_2_wysiwyg_content" class="ccm-advanced-editor"><?php  echo $field_2_wysiwyg_content; ?></textarea>
</div>

<div class="ccm-block-field-group">
	<h2>Link</h2>
	<?php  echo $ps->selectPage('field_3_link_cID', $field_3_link_cID); ?>
	<table border="0" cellspacing="3" cellpadding="0" style="width: 95%;">
		<tr>
			<td align="right" nowrap="nowrap"><label for="field_3_link_text">Link Text:</label>&nbsp;</td>
			<td align="left" style="width: 100%;"><?php  echo $form->text('field_3_link_text', $field_3_link_text, array('style' => 'width: 100%;')); ?></td>
		</tr>
	</table>
</div>

<div class="ccm-block-field-group">
	<h2>Label</h2>
	<?php  echo $form->text('field_4_textbox_text', $field_4_textbox_text, array('style' => 'width: 95%;')); ?>
</div>

<div class="ccm-block-field-group">
	<h2>Position of the Block</h2>
	<?php 
	$options = array(
		'0' => 'Choose Position',
		'1' => 'left',
		'2' => 'right',
	);
	echo $form->select('field_5_select_value', $options, $field_5_select_value);
	?>
</div>


