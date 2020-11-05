<?php  defined('C5_EXECUTE') or die("Access Denied.");
Loader::element('editor_config');
?>

<style type="text/css" media="screen">
	.ccm-block-field-group h2 { margin-bottom: 5px; }
	.ccm-block-field-group td { vertical-align: middle; }
</style>

<div class="ccm-block-field-group">
	<h2>Request Callback Title</h2>
	<?php  Loader::element('editor_controls'); ?>
	<textarea id="field_1_wysiwyg_content" name="field_1_wysiwyg_content" class="ccm-advanced-editor"><?php  echo $field_1_wysiwyg_content; ?></textarea>
</div>


