<?php
/* @var BlockView $this */
/* @var string $bID */
/* @var int $chosenPageID */
/* @var FormHelper $form */
/* @var Page $page */
defined('C5_EXECUTE') or die("Access Denied.");
/** @var TextHelper $th */
$th = Loader::helper('text');
/** @var NavigationHelper $nh */
$nh = Loader::helper('navigation');
/** @var ImageHelper $ih */
$ih = Loader::helper('image');

$chosenPage = Page::getByID($chosenPageID);
//$image = $chosenPage->getAttribute('page_bg');
$bannerImage = $chosenPage->getAttribute('banner_images');
if(!$bannerImage){
	$parentPage = Page::getByID($chosenPage->getCollectionParentID());
	$bannerImage = $parentPage->getAttribute('banner_images');
}
foreach ($bannerImage as $image) {
  $thumb = $ih->getThumbnail($image['file'], 1000, 1000, false);
  break;
}

if (is_null($chosenPage->getCollectionID())) {
    return;
}
?>
<a href="<?php echo $nh->getLinkToCollection($chosenPage); ?>" title="<?php echo $th->entities($chosenPage->getCollectionName()); ?>">
	<div class="content-holding" style="background-image: url('<?php echo $thumb->src; ?>');">
		<div class="contentinside">
			<h2><?php echo $th->entities($chosenPage->getCollectionName()); ?></h2>
		</div>
	</div>
</a>
