<?php  defined('C5_EXECUTE') or die("Access Denied.");
?>

<li class="milestone-item">
	<div class="milestone-wrap" data-number="1">
		<h3><?php echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?></h3>
		<p><?php echo nl2br(htmlentities($field_2_textarea_text, ENT_QUOTES, APP_CHARSET)); ?></p>
	</div>
</li>
<?php /* if (!empty($field_1_textbox_text)): ?>
	<?php echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?>
<?php  endif; ?>

<?php  if (!empty($field_2_textarea_text)): ?>
	<?php echo nl2br(htmlentities($field_2_textarea_text, ENT_QUOTES, APP_CHARSET)); ?>
<?php  endif; */ ?>
