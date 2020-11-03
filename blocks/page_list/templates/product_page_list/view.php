<?php
defined('C5_EXECUTE') or die('Access Denied.');
$rssUrl = $showRss ? $controller->getRssUrl($b) : '';
/** @var TextHelper $th */
$th = Loader::helper('text');
$dh = Loader::helper('date');
$ih = Loader::helper('image');

$curPage = Page::getCurrentPage();

$paginator      = null;
$showPagination = false;

$pageList = new PageList();
$pageList->filterByCollectionTypeHandle('product_detail');
$pageList->filterByParentID($page->getCollectionID());

$brand    = $th->sanitize($_GET['brand']);
$filter   = $th->sanitize($_GET['filter']);
$keywords = $th->sanitize($_GET['keywords']);


if ($brand) {
    $pageList->filterBySelectAttribute('brands', $brand);
}

if ($filter) {
    $pageList->filterBySelectAttribute('product_filters', $filter);
}

if ($keywords) {
    $pageList->filterByKeywords($keywords);
}


$pageList->setItemsPerPage(9);
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
      <div class="all-brands__row">

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

            $thumb      = $page->getAttribute('thumbnail_image');
            $thumbImage = $ih->getThumbnail($thumb, 500, 500, true);

            $productDescription = $page->getAttribute('product_description');

            ?>

            <div class="all-brands__col brand-box" itemscope itemtype="http://schema.org/Product">
              <a data-product="<?php echo $title; ?>" itemtype="url" data-target="#productModal" class="brand-image">
                <img src="<?php if ($thumbImage) {
                    echo $thumbImage->src;
                } else {
                    echo $this->getThemePath() . '/images/banner-1.jpg';
                }; ?>" alt="<?php echo $title; ?>" itemtype="image">
              </a>
                <h3 itemtype="name"><?php echo $title; ?></h3>
                <?php if ($description){ ?>
                  <p itemtype="description"><?php echo $description; ?></p>
                <?php } else { ?>
                  <?php echo $productDescription; ?>
                <?php } ?>
                <a href="#" class="btn btn-underline btn-clr-purple enquire-btn-modal" data-product="<?php echo $title; ?>" data-target="#productModal" datatype="url">Enquire Now</a>
            </div>

        <?php } ?>
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
    </div>
</section>
