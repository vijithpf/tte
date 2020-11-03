<?php  defined('C5_EXECUTE') or die("Access Denied.");
?>

<li class="testimonials_list_item">

	<blockquote>
		<p class="the_quote"><?php echo nl2br(htmlentities($field_2_textarea_text, ENT_QUOTES, APP_CHARSET)); ?></p>
		<span class="quote_by"><?php echo htmlentities($field_3_textbox_text, ENT_QUOTES, APP_CHARSET); ?></span>
	</blockquote>
</li>

<?php /*  if (!empty($field_1_image)): ?>
	<img src="<?php echo $field_1_image->src; ?>" width="<?php  echo $field_1_image->width; ?>" height="<?php  echo $field_1_image->height; ?>" alt="" />
<?php  endif; ?>
<img src="<?php echo $field_1_image->src; ?>" alt="..">
<?php  if (!empty($field_2_textarea_text)): ?>
	<?php echo nl2br(htmlentities($field_2_textarea_text, ENT_QUOTES, APP_CHARSET)); ?>
<?php  endif; ?>

<?php  if (!empty($field_3_textbox_text)): ?>
	<?php  echo htmlentities($field_3_textbox_text, ENT_QUOTES, APP_CHARSET); ?>
<?php  endif; */ ?>
