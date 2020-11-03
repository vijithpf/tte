<?php  defined('C5_EXECUTE') or die("Access Denied.");
$nh = Loader::helper('navigation');
?>

<?php $link_url = $nh->getLinkToCollection(Page::getByID($field_3_link_cID), true);
$link_text = empty($field_3_link_text) ? $link_url : htmlentities($field_3_link_text, ENT_QUOTES, APP_CHARSET); ?>

<a href="<?php  echo $link_url; ?>" title="<?php  echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?>">
	<div class="content-holding" style="background-image: url('<?php  echo $field_2_image->src; ?>');">
		<div class="contentinside">
			<h2>
			<?php  echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?>
			</h2>
		</div>
	</div>
</a>


<?php /* if (!empty($field_1_textbox_text)): ?>
 <?php  echo htmlentities($field_1_textbox_text, ENT_QUOTES, APP_CHARSET); ?>
<?php  endif; ?>

<?php  if (!empty($field_2_image)): ?>
 <img src="<?php  echo $field_2_image->src; ?>" width="<?php  echo $field_2_image->width; ?>" height="<?php  echo $field_2_image->height; ?>" alt="" />
<?php  endif; ?>

<?php  if (!empty($field_3_link_cID)):
 $link_url = $nh->getLinkToCollection(Page::getByID($field_3_link_cID), true);
 $link_text = empty($field_3_link_text) ? $link_url : htmlentities($field_3_link_text, ENT_QUOTES, APP_CHARSET);
 ?>
 <a href="<?php  echo $link_url; ?>"><?php  echo $link_text; ?></a>
<?php  endif; */ ?>
