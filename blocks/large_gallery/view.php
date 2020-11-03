<?php  defined('C5_EXECUTE') or die("Access Denied.");

$currentPage = Page::getCurrentPage();
$isEditMode = $currentPage->isEditMode();
?>

<li>
	<a href="<?php  echo $field_1_image->src; ?>" class="block fancy-img">
		<div class="block_image full_bg mainImage" style="background-image: url('<?php  echo $field_1_image->src; ?>')">
			<div class="the_block"<?php if($isEditMode){ echo 'style="z-index: 3;"'; } ?>>

					<div class="block_content">
						<div class="blurred_image_wrap blurWrap left">
							<div class="blurred_image blurImage" style="background-image: url('<?php  echo $field_1_image->src; ?>');">

							</div><!-- /.blurred_image -->
						</div><!-- /.blurred_image_wrap -->
						<?php  if (!empty($field_2_wysiwyg_content)): ?>
							<div class="block_details">
								<?php  echo $field_2_wysiwyg_content; ?>
							</div><!-- /.block_details -->
						<?php  endif; ?>

					</div><!-- /.block_content -->

				</div><!-- /.the_block -->
			</div><!-- /.block_image -->
		</a><!-- /.the_block -->
	</li><!-- /li -->

<? /*  if (!empty($field_1_image)): ?>
	<img src="<?php  echo $field_1_image->src; ?>" width="<?php  echo $field_1_image->width; ?>" height="<?php  echo $field_1_image->height; ?>" alt="" />
<?php  endif; ?>

<?php  if (!empty($field_2_wysiwyg_content)): ?>
	<?php  echo $field_2_wysiwyg_content; ?>
<?php  endif; */ ?>
