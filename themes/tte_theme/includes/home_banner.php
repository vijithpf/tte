<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
$ih = Loader::helper('image');
$image = $page->getAttribute('banner_images');

$pageTitle = $page->getCollectionName();

?>
<section id="home_banner" class="banner home-banner">
  <div class="nav_overlay"></div><!-- /.nav_overlay -->


  <div id="banner_slides" class="bannerMainImg">
    <ul class="slides-container">
    <?php if ($image) {
        foreach ($image as $img) {
            $bannerimage = $ih->getThumbnail($img['file'], 1600, 1600, false);
            $imgTitle = $img['file']->getTitle();
            ?>

            <li><img src="<?php echo $bannerimage->src; ?>" alt="<?php echo $pageTitle; ?>"></li>

    <?php }
        } ?>
  </ul><!-- /.slides-container -->
  </div><!-- /#banner_slides -->
  <div class="relative container">
    <div class="banner_block">
      <a href="#intro" class="arrowdown bounce"></a>
      <div class="banner_content">
        <!-- star banner blur IMAGES -->
        <div class="banner_blurred_wrap bannerBlurWrap left">
          <?php $count= 0; if ($image) {
              foreach ($image as $img) {

                $count++;
                  $bannerimage = $ih->getThumbnail($img['file'], 1600, 1600, false); ?>

                  <div class="banner_blurred_image bannerBlurImg<?php if($count == 1){ echo ' active'; } ?>" style="background-image: url('<?php echo $bannerimage->src; ?>');"></div><!-- /.banner_blurred_image -->

            <?php }
                } ?>
          </div><!-- /.banner_blurred_wrap -->

        <!-- end banner blur IMAGES -->
        <?php $detailCount = 0; foreach ($image as $img) {
          $detailCount++;
            $imgUrl = "#";
            if($img['file']->getAttribute('slide_url')) {
                $imgUrl = $img['file']->getAttribute('slide_url');
            }

            ?>

          <div class="banner_details<?php if($detailCount == 1){ echo ' active'; } ?>">
              <h1 class="banner_title"><?php echo $img['file']->getAttribute('slide_title'); ?></h1>
              <p><?php echo $img['file']->getAttribute('slide_content'); ?></p>
              <a href="<?php echo $imgUrl; ?>" class="btn light btn-md btn-white"><?php echo $img['file']->getAttribute('slide_button_text'); ?></a>
          </div><!-- banner_details -->

        <?php } ?>

      </div><!-- /banner_content -->

      <div class="banner_space"></div><!-- banner space -->

    </div><!-- /banner_block -->

  </div><!-- .container -->
</section><!-- /.home-banner -->
