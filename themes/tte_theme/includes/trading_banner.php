<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
$ih = Loader::helper('image');
$image = $page->getAttribute('banner_images');

?>

<section class="banner">
  <div class="nav_overlay"></div><!-- /.nav_overlay -->


  <div id="banner_slides" class="bannerMainImg">
    <ul class="slides-container">
      <?php if ($image) {
          foreach ($image as $img) {
              $bannerimage = $ih->getThumbnail($img['file'], 1600, 1600, false); ?>

              <li><img src="<?php echo $bannerimage->src; ?>" alt=".."></li>

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

      <div class="banner_space"></div><!-- banner space -->

    </div><!-- /banner_block -->

  </div><!-- .container -->

  <?php $stack = Stack::getByName('Trading and Distribution Navigation'); $stack->display(); ?>

</section><!-- /.home-banner -->
