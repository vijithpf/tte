<?php  defined('C5_EXECUTE') or die("Access Denied.");
?>

<?php  if (!empty($field_1_textbox_text)): ?>
	<h3><?php  echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?></h3>
<?php  endif; ?>

<?php  if (!empty($field_2_textarea_text)): ?>
	<p><?php  echo nl2br(htmlentities($field_2_textarea_text, ENT_QUOTES, APP_CHARSET)); ?></p>
<?php  endif; ?>
