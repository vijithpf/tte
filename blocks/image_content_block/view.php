<?php  defined('C5_EXECUTE') or die("Access Denied.");
$nh = Loader::helper('navigation');
?>

<?php  if (!empty($field_3_link_cID)):
	$link_url = $nh->getLinkToCollection(Page::getByID($field_3_link_cID), true);
	$link_text = empty($field_3_link_text) ? $link_url : htmlentities($field_3_link_text, ENT_QUOTES, APP_CHARSET);
	?>
<?php  endif; ?>

<section class="section section_image_content_block<?php  if ($field_5_select_value == 1){ echo ' left'; } elseif($field_5_select_value == 2) { echo ' right'; } ?>">
	<div class="container">
		<div class="block block_<?php  if ($field_5_select_value == 1){ echo 'left'; } elseif($field_5_select_value == 2) { echo 'right'; } ?>">
			<div class="block_image full_bg mainImage" style="background-image: url('<?php echo $field_1_image->src; ?>')">
				<div class="the_block">
						<div class="block_space"></div>

							<div class="block_content">
								<div class="blurred_image_wrap blurWrap <?php  if ($field_5_select_value == 1){ echo 'right'; } elseif($field_5_select_value == 2) { echo 'left'; } ?>">
									<div class="blurred_image blurImage" style="background-image: url('<?php echo $field_1_image->src; ?>');"></div>
								</div>
								<div class="block_details">
									<?php echo $field_2_wysiwyg_content; ?>
									<a href="<?php echo $link_url; ?>" class="btn light btn-md btn-white"><?php echo $link_text; ?></a>
									<span class="block_label"><?php echo htmlentities($field_4_textbox_text, ENT_QUOTES, APP_CHARSET); ?></span>
								</div>
							</div><!-- /.block_content -->
					</div><!-- /.the_block -->
				</div><!-- /.block_image -->
			</div><!-- /.block -->
		</div><!-- /.container -->
	</section><!-- /.section -->