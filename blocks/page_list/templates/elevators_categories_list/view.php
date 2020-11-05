<?php
defined('C5_EXECUTE') or die('Access Denied.');
$rssUrl = $showRss ? $controller->getRssUrl($b) : '';
$th = Loader::helper('text');
$dh = Loader::helper('date');
$i = 1;
$ih = Loader::helper('image');
//$ih = Loader::helper('image'); //<--uncomment this line if displaying image attributes (see below)
//Note that $nh (navigation helper) is already loaded for us by the controller (for legacy reasons)
$pageGroups = array_chunk($pages, 3);

?>

<?php foreach ($pages as $page){
    // Prepare data for each page being listed...
    $title = $th->entities($page->getCollectionName());
    $url = $nh->getLinkToCollection($page);
    $target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
    $target = empty($target) ? '_self' : $target;
    $description = $page->getCollectionDescription();
    $description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
    $description = $th->entities($description);
    $date = $dh->date('d F', strtotime($page->getCollectionDatePublic()));
    $year = $dh->date('Y', strtotime($page->getCollectionDatePublic()));

    $thumb = $page->getAttribute('thumbnail_image');
    $thumbImage = $ih->getThumbnail($thumb, 500, 500, true);

    $productDescription = $page->getAttribute('product_description');
  ?>

  <div class="categories brand-box all-brands__col">
    <a href="<?php echo $url; ?>" class="brand-image">
      <img src="<?php if ($thumbImage) {
          echo $thumbImage->src;
      } else {
          echo $this->getThemePath() . '/images/banner-1.jpg';
      }; ?>" alt="<?php echo $title; ?>">
    </a>
    <h3><?php echo $title; ?></h3>

    <?php if ($description){ ?>
      <p><?php echo $description; ?></p>
    <?php } else { ?>
      <?php echo $productDescription; ?>
    <?php } ?>

    <a href="<?php echo $url; ?>" class="btn btn-underline btn-clr-purple light" title="<?php echo $title; ?>">See All Products</a>
  </div>

<?php } ?>