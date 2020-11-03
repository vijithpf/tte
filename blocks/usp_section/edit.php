<?php  defined('C5_EXECUTE') or die("Access Denied.");
$al = Loader::helper('concrete/asset_library');
Loader::element('editor_config');
?>

<style type="text/css" media="screen">
	.ccm-block-field-group h2 { margin-bottom: 5px; }
	.ccm-block-field-group td { vertical-align: middle; }
</style>

<div class="ccm-block-field-group">
	<h2>Main Title</h2>
	<?php  echo $form->text('field_1_textbox_text', $field_1_textbox_text, array('style' => 'width: 95%;')); ?>
</div>

<div class="ccm-block-field-group">
	<h2>Image</h2>
	<?php  echo $al->image('field_2_image_fID', 'field_2_image_fID', 'Choose Image', $field_2_image); ?>
</div>

<div class="ccm-block-field-group">
	<h2>Content 1</h2>
	<?php  Loader::element('editor_controls'); ?>
	<textarea id="field_3_wysiwyg_content" name="field_3_wysiwyg_content" class="ccm-advanced-editor"><?php  echo $field_3_wysiwyg_content; ?></textarea>
</div>

<div class="ccm-block-field-group">
	<h2>Content 2</h2>
	<?php  Loader::element('editor_controls'); ?>
	<textarea id="field_4_wysiwyg_content" name="field_4_wysiwyg_content" class="ccm-advanced-editor"><?php  echo $field_4_wysiwyg_content; ?></textarea>
</div>

<div class="ccm-block-field-group">
	<h2>Content 3</h2>
	<?php  Loader::element('editor_controls'); ?>
	<textarea id="field_5_wysiwyg_content" name="field_5_wysiwyg_content" class="ccm-advanced-editor"><?php  echo $field_5_wysiwyg_content; ?></textarea>
</div>


