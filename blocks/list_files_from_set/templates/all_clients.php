<?php     defined('C5_EXECUTE') or die("Access Denied.");
$date = Loader::helper('date');
$c = Page::getCurrentPage();
$i = 0;
if($c instanceof Page) {
$cID = $c->getCollectionID();
}
$files = $controller->getFileSet();
$imgHelper = Loader::helper('image');

$fileGroups = array_chunk($files, 4);
$slideGroups = array_chunk($fileGroups, 4);
?>

<?php if (!empty($files)) { ?>
	<section class="clients-selection all-clients">
		<div class="container">
			<div class="row">
				<div class="col-sm-6">
					<?php $a = new Area('Overview Clients Title'); $a->display($c); ?>
				</div><!-- /.col -->
				<div class="col-sm-6"></div><!-- /.col -->
			</div><!-- /.row -->
			<div class="clients-selection-wrap">

				<div class="clients-selection-slider all-clients-selection-slider">
					<?php foreach($slideGroups as $fileGroups): ?>
						<div class="row_cont">
					<?php foreach ($fileGroups as $files): ?>
						<ul class="row">

						<?php foreach($files as $f) {
							$fp = new Permissions($f);
								if ($fp->canViewFile()) {
									$thumb = $imgHelper->getThumbnail($f, 1400, 800, false);
									$imgCaption =  $f->getAttribute('image_caption');
								?>
								<li class="client-item">
										<img src="<?php if($thumb) { echo $thumb->src; } ?>" alt=".." />
								</li>
								<?php } ?>
							<?php } ?>

						</ul>

					<?php endforeach; ?>
				</div>
				<?php endforeach; ?>

			</div><!-- /.clients-selection-slider -->
		</div><!-- /.clients-selection-wrap -->
	</div><!-- /.container -->
</section><!-- /.clients-selection -->
<?php } ?>
