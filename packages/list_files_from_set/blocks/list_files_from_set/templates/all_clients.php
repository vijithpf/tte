<?php     defined('C5_EXECUTE') or die("Access Denied.");
$date = Loader::helper('date');
$c = Page::getCurrentPage();
$i = 0;
if($c instanceof Page) {
$cID = $c->getCollectionID();
}
$files = $controller->getFileSet();
$imgHelper = Loader::helper('image');

$fileGroups = array_chunk($files, 5);
$slideGroups = array_chunk($fileGroups, 3);
?>

<?php if (!empty($files)) { ?>
	<section class="clients-selection all-clients">
		<div class="container">
			<div class="row">
				<div class="col-sm-6">
					<h2>An Overview of our clients</h2>
				</div><!-- /.col -->
				<div class="col-sm-6">

				</div><!-- /.col -->
			</div><!-- /.row -->
			<div class="clients-selection-wrap">

				<div class="clients-selection-slider">
					<?php foreach($slideGroups as $fileGroups): ?>
					<div class="client-selection-slide">

					<?php foreach($fileGroups as $files){ ?>

							<ul class="row">
							<?php
							foreach($files as $f) {
								$fp = new Permissions($f);
									if ($fp->canViewFile()) {
										$thumb = $imgHelper->getThumbnail($f, 1400, 800, false);
										$imgCaption =  $f->getAttribute('image_caption');
									?>
									<li class="client-item">
										<a href="<?php echo $thumb->src;?>">
											<img src="<?php if($thumb) { echo $thumb->src; } ?>" alt=".." />
										</a>
									</li>
									<?php } ?>
								<?php } ?>
							</ul><!-- /.row -->
					<?php } ?>
				</div><!-- /.clients-selection-slide -->
			<?php endforeach; ?>
			</div><!-- /.clients-selection-slider -->
		</div><!-- /.clients-selection-wrap -->
	</div><!-- /.container -->
</section><!-- /.clients-selection -->
<?php } ?>
