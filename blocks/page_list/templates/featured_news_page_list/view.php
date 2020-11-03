<?php
defined('C5_EXECUTE') or die('Access Denied.');
$rssUrl = $showRss ? $controller->getRssUrl($b) : '';
$th = Loader::helper('text');
$dh = Loader::helper('date');
$i = 1;
$ih = Loader::helper('image');
//$ih = Loader::helper('image'); //<--uncomment this line if displaying image attributes (see below)
//Note that $nh (navigation helper) is already loaded for us by the controller (for legacy reasons)
$currentPage = Page::getCurrentPage();
?>
<section class="section section_latestnews">
  <div class="container">

    <div class="row">

      <div class="col-sm-4 left paddingTop">
        <?php $a = new Area('Featured News Title'); $a->display($currentPage); ?>
        <a href="<?php echo View::url('/news'); ?>" class="btn light btn-dark btn-underline">See all news</a>
      </div><!-- /.col -->

     <div class="col-sm-8 right">

        <ul class="articles_list" id="articles_slider">

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

            <li itemscope itemtype="http://schema.org/Article">
              <div class="wrap">
                <div class="article_list_content">
                  <div class="article_list_image" itemprop="image" style="background-image: url('<?php if($thumbImage) { echo $thumbImage->src; } else { echo $this->getThemePath() . '/images/banner-1.jpg'; }?>')">
                    <a href="<?php echo $url; ?>" itemprop="url" class="clicktosee">Read More&nbsp;&nbsp;<span class="fa fa-angle-right"></span></a>
                  </div>
                  <h3 class="article_list_title" itemprop="name"><?php echo $title; ?></h3>
                  <p class="article_list_desc" itemprop="description"><?php echo $description; ?></p>
                  <?php /* ?><span class="article_list_date" itemprop="datePublished"><?php echo $date . ' ' . $year; ?></span><?php */ ?>
                </div>
              </div>
            </li>

          <?php endforeach; ?>

        </ul><!-- /.articles_list -->
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container -->
</section><!-- /.latestnews -->
