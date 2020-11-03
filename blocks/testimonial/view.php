<?php  defined('C5_EXECUTE') or die("Access Denied.");
?>

<li class="client-t-item">
	<div class="client-t-wrap">

		<div class="client-t-logo">
			<img src="<?php echo $field_2_image->src; ?>" alt="...">
		</div>


		<h4><?php echo htmlentities($field_3_textbox_text, ENT_QUOTES, APP_CHARSET); ?></h4>
		<h5><?php echo htmlentities($field_4_textbox_text, ENT_QUOTES, APP_CHARSET); ?></h5>
		<p><?php echo nl2br(htmlentities($field_5_textarea_text, ENT_QUOTES, APP_CHARSET)); ?></p>

	</div>

</li>

<?php /* if (!empty($field_1_image)): ?>
	<img src="<?php  echo $field_1_image->src; ?>" width="<?php  echo $field_1_image->width; ?>" height="<?php  echo $field_1_image->height; ?>" alt="" />
<?php  endif; ?>

<?php  if (!empty($field_2_image)): ?>
	<img src="<?php  echo $field_2_image->src; ?>" width="<?php  echo $field_2_image->width; ?>" height="<?php  echo $field_2_image->height; ?>" alt="" />
<?php  endif; ?>

<?php  if (!empty($field_3_textbox_text)): ?>
	<?php echo htmlentities($field_3_textbox_text, ENT_QUOTES, APP_CHARSET); ?>
<?php  endif; ?>

<?php  if (!empty($field_4_textbox_text)): ?>
	<?php  echo htmlentities($field_4_textbox_text, ENT_QUOTES, APP_CHARSET); ?>
<?php  endif; ?>

<?php  if (!empty($field_5_textarea_text)): ?>
	<?php echo nl2br(htmlentities($field_5_textarea_text, ENT_QUOTES, APP_CHARSET)); ?>
<?php  endif; */ ?>
