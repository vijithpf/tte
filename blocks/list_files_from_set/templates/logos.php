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
	<ul class="clearfix">

	<?php
		foreach($files as $f) {
		$fp = new Permissions($f);
			if ($fp->canViewFile()) {
				$thumb = $imgHelper->getThumbnail($f, 1400, 800, false);
				$logoURL =  $f->getAttribute('logo_url');
			?>
			<li>
				<a href="<?php echo $logoURL; ?>">
					<img src="<?php echo $thumb->src; ?>" alt=".." />
				</a>
			</li>
			<?php } ?>
		<?php } ?>
	</ul>

<?php } ?>
