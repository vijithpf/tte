<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
$ih = Loader::helper('image');
$image = $page->getAttribute('banner_images');
if ($image) {
    foreach ($image as $img) {
        $bannerImage = $ih->getThumbnail($img['file'], 1600, 1600, false);
        $hAlign = $img['align'];
        $vAlign = $img['vAlign'];
        break;
    }
}

$th = Loader::helper('text');
$dh    = Loader::helper('date');

$title    = $th->entities($page->getCollectionName());
$description = $page->getCollectionDescription();
$description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
$description = $th->entities($description);
$thumbnailImage = $page->getAttribute('thumbnail_image');
?>
<main class="site-body offset-top">
  <div class="container-fluid">


    <article class="news-article-content" itemscope itemtype="http://schema.org/Article">
      <div class="container">
        <div class="row">
          <div class="col-sm-10 col-sm-offset-1">

            <?php /* ?><p class="news-article-date" itemtype="datePublished"><?php echo $dh->date('d F Y', strtotime($page->getCollectionDatePublic())); ?></p><?php */ ?>
            <div class="news-article-title">
              <h1 itemtype="name"><?php echo $title; ?></h1>

              <?php if ($thumbnailImage): ?>
                <div class="mb30">
                  <img src="<?php echo $thumbnailImage->getRelativePath(); ?>" alt="<?php echo $title; ?>" />
                </div>
              <?php endif; ?>

              <p><?php echo $description; ?></p>
            </div>

            <div itemtype="articleBody">
              <?php $a = new Area('Content'); $a->display($c); ?>
            </div>

          </div>
        </div>
      </div>
    </article>

  </div><!-- /.container-fluid -->
</main><!-- /.site-body -->
