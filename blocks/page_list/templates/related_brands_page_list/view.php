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
<section class="all-brands">
  <div class="container">
    <div class="row">
      <div class="col-sm-8 col-sm-offset-2 text-center">
        <h2>Have a look at our other brands(Add Content)</h2>
        <?php
          $a = new Area("Other Brands Title"); $a->display($c);
        ?>
      </div>
    </div>
    <?php foreach ($pageGroups as $pages) { ?>
      <div class="row">
    		<?php foreach ($pages as $page):
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
    				$thumbImage = $ih->getThumbnail($thumb, 1000, 1000, false);
        	?>

          <div class="col-sm-6 col-md-4">
            <div class="brand-image">
              <img src="<?php if($thumbImage) { echo $thumbImage->src; } else { echo $this->getThemePath() . '/images/danfoss.jpg'; }; ?>" alt="...">
            </div>
            <h3><?php echo $title; ?></h3>
            <p><?php echo $description; ?></p>
            <a href="<?php echo $url; ?>" class="btn btn-underline btn-clr-purple" title="<?php echo $title; ?>">See All Products</a>
          </div>

        <?php endforeach; ?>
      </div>
    <?php } ?>
  </div>
</section>
