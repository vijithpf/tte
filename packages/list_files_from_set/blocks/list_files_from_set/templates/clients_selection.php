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
?>

<?php if (!empty($files)) { ?>
	<section class="clients-selection">
		<div class="container">
			<div class="clients-selection-wrap">

				<div class="row">
					<div class="col-sm-6">
						<h2>Selection of our clients</h2>
					</div><!-- /.col -->
					<div class="col-sm-6">

					</div><!-- /.col -->
				</div><!-- /.row -->

				<div class="clients-selection-slider">
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
					</ul>

					<?php } ?>
				<?php } ?>
			</div><!-- /.clients-selection-slider -->
		</div><!-- /.clients-selection-wrap -->
	</div><!-- /.container -->
</section><!-- /.clients-selection -->
