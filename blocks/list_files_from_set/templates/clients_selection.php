<?php     defined('C5_EXECUTE') or die("Access Denied.");
$date = Loader::helper('date');
$c = Page::getCurrentPage();
$i = 0;
if($c instanceof Page) {
$cID = $c->getCollectionID();
}
$files = $controller->getFileSet();
$imgHelper = Loader::helper('image');


// $fileGroups = array_chunk($files, 5);
?>

		<?php if (!empty($files)) { ?>

					<?php
						foreach($files as $f) {
						$fp = new Permissions($f);
							if ($fp->canViewFile()) {
								$thumbImage = $f->getRelativePath();

                // $thumbImage = $imgHelper->output($f, '...', true);
							?>
							<li class="client-item">
                <img src="<?php echo $thumbImage; ?>" alt="...">
							</li>
							<?php } ?>
						<?php } ?>

		<?php } ?>
