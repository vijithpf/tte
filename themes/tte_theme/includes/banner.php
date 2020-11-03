<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
$ih = Loader::helper('image');

$pageTitle = $page->getCollectionName();
?>

<?php $this->inc('includes/logo_svg.php'); ?>


<section class="banner">

  <div id="banner_slides" class="bannerMainImg">
    <ul class="slides-container">
      <?php
      $image = $page->getAttribute('banner_images');

      if ($image) {

          foreach ($image as $img) {
              $bannerimage = $ih->getThumbnail($img['file'], 1600, 1600, false); ?>

              <li><img src="<?php echo $bannerimage->src; ?>" alt="<?php echo $pageTitle; ?>"></li>

              <?php break;
          }
      } ?>
    </ul><!-- /.slides-container -->
  </div><!-- /#banner_slides -->
  <div class="relative container">

    <div class="banner_block">

      <a href="#intro" class="arrowdown"></a>

      <div class="banner_content">

        <!-- star banner blur IMAGES -->

        <div class="banner_blurred_wrap bannerBlurWrap left">
          <?php $count= 0; if ($image) {
              foreach ($image as $img) {
                $count++;
                  $bannerimage = $ih->getThumbnail($img['file'], 1600, 1600, false); ?>

                  <div class="banner_blurred_image bannerBlurImg<?php if($count == 1){ echo ' active'; } ?>" style="background-image: url('<?php echo $bannerimage->src; ?>');">

                  </div><!-- /.banner_blurred_image -->

            <?php }
                } ?>
          </div><!-- /.banner_blurred_wrap -->

          <!-- end banner blur IMAGES -->

        <div class="banner_details">
          <?php $a = new Area('Banner Content'); $a->display($c); ?>
        </div><!-- banner_details -->

      </div><!-- /banner_content -->

      <div class="banner_space">

        <?php /* if ($page->getAttribute('page_type') == 'about'): ?>
          <svg class="banner-logo" viewBox="0 0 144.48 59.27">
            <use xlink:href="#logo" />
          </svg>
        <?php endif; */ ?>
      </div><!-- banner space -->

    </div><!-- /banner_block -->

  </div><!-- .container -->
  <?php if ($page->getAttribute('page_type') == 'brands'): ?>
    <?php $stack = Stack::getByName('Brands Navigation'); if( $stack ) $stack->display(); ?>
  <?php endif; ?>
</section><!-- /.home-banner -->
