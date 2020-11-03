<?php     defined('C5_EXECUTE') or die("Access Denied.");
$date = Loader::helper('date');
$c = Page::getCurrentPage();
$i = 0;
if($c instanceof Page) {
$cID = $c->getCollectionID();
}
$files = $controller->getFileSet();
$imgHelper = Loader::helper('image');
?>

<?php if (!empty($files)) { ?>
<?php
	foreach($files as $f) {
	$fp = new Permissions($f);
		if ($fp->canViewFile()) {
			$thumb = $imgHelper->getThumbnail($f, 1400, 800, false);
			$imgCaption =  $f->getAttribute('image_caption');
		?>
		<li>
			<a href="<?php echo $thumb->src;?>" class="client-item">
				<img src="<?php if($thumb) { echo $thumb->src; } ?>" alt=".." />
			</a>
		</li>
		<?php } ?>
	<?php } ?>
<?php } ?>
