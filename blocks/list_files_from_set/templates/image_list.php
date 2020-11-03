<?php     defined('C5_EXECUTE') or die("Access Denied.");
$date = Loader::helper('date');
$c = Page::getCurrentPage();
if($c instanceof Page) {
$cID = $c->getCollectionID();
}
$files = $controller->getFileSet();
$imgHelper = Loader::helper('image'); 
?>

<?php if (!empty($files)) { ?>
<div class="simple_image_gallery_container">
<?php  
foreach($files as $f) {
$fp = new Permissions($f);
if ($fp->canViewFile()) {
	$thumb = $imgHelper->getThumbnail($f, 1200, 1200, false);
?>
<div class="g-image"><img src="<?=$thumb->src;?>" alt="slide image"  /></div>
<?php
} ?>

<?php } ?>
</div>
<?php } ?>