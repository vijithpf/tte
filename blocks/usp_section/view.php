<?php  defined('C5_EXECUTE') or die("Access Denied.");
?>

<?php /*  if (!empty($field_1_textbox_text)): ?>
	<?php  echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?>
<?php  endif; ?>

<?php  if (!empty($field_2_image)): ?>
	<img src="<?php  echo $field_2_image->src; ?>" width="<?php  echo $field_2_image->width; ?>" height="<?php  echo $field_2_image->height; ?>" alt="" />
<?php  endif; ?>

<?php  if (!empty($field_3_wysiwyg_content)): ?>
	<?php  echo $field_3_wysiwyg_content; ?>
<?php  endif; ?>

<?php  if (!empty($field_4_wysiwyg_content)): ?>
	<?php  echo $field_4_wysiwyg_content; ?>
<?php  endif; ?>

<?php  if (!empty($field_5_wysiwyg_content)): ?>
	<?php  echo $field_5_wysiwyg_content; ?>
<?php  endif; */ ?>
<section class="section usp-section white-slick-dots">
  <div class="usp-title">
    <h2><?php echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?></h2>
  </div>
  <div class="container">

   <div class="testimonial_bg full_bg mainImage" style="background-image: url('<?php  echo $field_2_image->src; ?>');"></div><!-- /.testimonials_bg -->

  </div><!-- /.container -->

  <div class="container noPadding relative">

  <div class="usp-content-wrap">

        <div class="blurred_image_wrap blurWrap right">
          <div class="blurred_image blurImage" style="background-image: url('<?php echo $field_2_image->src; ?>');"></div>
        </div>
        <div class="block_details row">
          <div class="col-sm-4 usp__col">
            <?php  echo $field_3_wysiwyg_content; ?>
          </div>
          <div class="col-sm-4 usp__col">
            <?php  echo $field_4_wysiwyg_content; ?>
          </div>
          <div class="col-sm-4 usp__col">
            <?php  echo $field_5_wysiwyg_content; ?>
          </div>
        </div>

      </div><!-- /.testimonials_wrap -->
  </div>


  </section><!-- /.testimonials -->
