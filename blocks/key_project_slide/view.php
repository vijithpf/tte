<?php  if (!empty($field_1_image)): ?>
<li>
  <a href="<?php  echo $field_1_image->src; ?>" class="block fancy-img" title="Hyatt Hotel and Residence Towers">
    <div class="block_image full_bg mainImage" style="background-image: url('<?php  echo $field_1_image->src; ?>')">
      <div class="the_block">
        <div class="block_content">
          <div class="blurred_image_wrap blurWrap left">
            <div class="blurred_image blurImage" style="background-image: url('<?php  echo $field_1_image->src; ?>');">

            </div><!-- /.blurred_image -->
          </div><!-- /.blurred_image_wrap -->
          <?php  if (!empty($field_2_wysiwyg_content)): ?>
            <div class="block_details">
              <?php  echo $field_2_wysiwyg_content; ?>
            </div>
          <?php  endif; ?>
        </div><!-- /.block_content -->
      </div><!-- /.the_block -->
    </div><!-- /.block_image -->
  </a><!-- /.the_block -->
  </li><!-- /li -->
<?php  endif; ?>