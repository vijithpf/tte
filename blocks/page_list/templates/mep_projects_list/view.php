<?php
defined('C5_EXECUTE') or die('Access Denied.');
$rssUrl = $showRss ? $controller->getRssUrl($b) : '';
$th = Loader::helper('text');
$dh = Loader::helper('date');
$i = 1;
$ih = Loader::helper('image');
//$ih = Loader::helper('image'); //<--uncomment this line if displaying image attributes (see below)
//Note that $nh (navigation helper) is already loaded for us by the controller (for legacy reasons)

$curPage = Page::getCurrentPage();
?>
<section class="large-gallery mep-gallery">

  <div class="container">
    <div class="row">
      <div class="col-sm-12 text-center">
        <?php $a = new Area("Gallerie Title"); $a->display($curPage); ?>
      </div>
    </div>
  </div>


  <ul class="large-gallery-slider">

		<?php foreach ($pages as $page):
        // Prepare data for each page being listed...
				$title = $th->entities($page->getCollectionName());
        $url = $nh->getLinkToCollection($page);
        $target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
        $target = empty($target) ? '_self' : $target;
        $description = $page->getCollectionDescription();
        $description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
        $description = $th->entities($description);
        $description = ($description ? $description : 'Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci. Aenean dignissim pellentesque felis.');

        $date = $dh->date('d F', strtotime($page->getCollectionDatePublic()));
        $year = $dh->date('Y', strtotime($page->getCollectionDatePublic()));

				$thumb = $page->getAttribute('thumbnail_image');
				$thumbImage = $ih->getThumbnail($thumb, 1000, 1000, false);
    	?>

      <li>
        <a href="<?php echo $thumbImage->src; ?>" class="block fancy-img" title="<?php echo $title; ?>">
          <div class="block_image full_bg mainImage" style="background-image: url('<?php echo $thumbImage->src; ?>')">
            <div class="the_block">

                <div class="block_content">
                  <div class="blurred_image_wrap blurWrap left">
                    <div class="blurred_image blurImage" style="background-image: url('<?php echo $thumbImage->src; ?>');">

                    </div><!-- /.blurred_image -->
                  </div><!-- /.blurred_image_wrap -->
                  <div class="block_details">
                    <h3><?php echo $title; ?></h3>
                    <p><?php echo $description; ?></p>
                  </div><!-- /.block_details -->
                </div><!-- /.block_content -->

              </div><!-- /.the_block -->
            </div><!-- /.block_image -->
          </a><!-- /.the_block -->
        </li><!-- /li -->

    <?php endforeach; ?>

  </ul>
</section>
