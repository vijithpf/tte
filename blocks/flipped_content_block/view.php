<?php  defined('C5_EXECUTE') or die("Access Denied.");
?>

<div class="row col-grid">
  <div class="col-md-6">
    <?php  if (!empty($field_1_image)): ?>
      <div class="img-wrap">
      <img src="<?php  echo $field_1_image->src; ?>" width="<?php  echo $field_1_image->width; ?>" height="<?php  echo $field_1_image->height; ?>" alt="" />
      </div>
    <?php  endif; ?>
  </div>
  <div class="col-md-6">
    <?php  if (!empty($field_2_wysiwyg_content)): ?>
      <div class="content-wrap">
      <?php  echo $field_2_wysiwyg_content; ?>
      </div>
    <?php  endif; ?>
  </div>
</div>