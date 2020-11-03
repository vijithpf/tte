<?php
defined('C5_EXECUTE') or die('Access Denied.');
$rssUrl  = $showRss ? $controller->getRssUrl($b) : '';
$th      = Loader::helper('text');
$dh      = Loader::helper('date');
$ih      = Loader::helper('image');
$curPage = Page::getCurrentPage();

$brandSelected = (string) $page->getAttribute('brands');
$brandTitle    = strtolower($curPage->getCollectionName());


$paginator      = null;
$showPagination = false;

$pageList = new PageList();
$pageList->filterByCollectionTypeHandle('product_detail');
$pageList->filterBySelectAttribute('brands', $brandSelected);
$pageList->setItemsPerPage(12);
$pages = $pageList->getPage();

$summery = $pageList->getSummary();
if ($summery->pages > 1) {
    $showPagination = true;
    $paginator      = $pageList->getPagination();
}

$pageGroups = array_chunk($pages, 3);

?>
<section class="all-brands">
    <div class="container">
      <?php if ($pages) { ?>

          <div class="all-brands__row">

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

                $thumb      = $page->getAttribute('thumbnail_image');
                $thumbImage = $ih->getThumbnail($thumb, 1000, 1000, false);

                $productDescription = $page->getAttribute('product_description');
                ?>

                  <div class="all-brands__col brand-box">
                      <div class="brand-image">
                        <img src="<?php if ($thumbImage) {
                            echo $thumbImage->src;
                        } else {
                            echo $this->getThemePath() . '/images/banner-1.jpg';
                        }; ?>" alt="<?php echo $title; ?>">
                      </div>
                      <h3><?php echo $title; ?></h3>
                      <?php if ($description){ ?>
                        <p><?php echo $description; ?></p>
                      <?php } else { ?>
                        <?php echo $productDescription; ?>
                      <?php } ?>
                      <a href="#" class="btn btn-underline btn-clr-purple enquire-btn-modal" data-product="<?php echo $title; ?>" data-target="#productModal">Enquire Now</a>
                  </div>

              <?php endforeach; ?>

          </div>
      <?php if ($showPagination): ?>
          <div id="pagination">
              <div class="container">
                  <div class="ccm-pagination">
                      <?php echo $paginator->getPages() ?>
                  </div>
              </div>
          </div>
      <?php endif; ?>
    <?php } ?>

    </div>
</section>
