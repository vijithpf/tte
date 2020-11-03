<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
$CDN_URL = 'https://static-tte.s3-accelerate.dualstack.amazonaws.com';
$THEME_URL = '/themes/tte_theme/';
$editMode = $page->isEditMode();
?>
<main class="site-body">
  <div class="container-fluid">

    <?php if ( $page->getAttribute('page_type') != 'news'): ?>
      <?php if ($page->getAttribute('page_type') == 'home'){ ?>
        <?php $this->inc('includes/home_banner.php'); ?>
      <?php } else { ?>
        <?php $this->inc('includes/banner.php'); ?>
      <?php } ?>
    <?php endif; ?>

    <?php if ($page->getAttribute('page_type') != 'clients' && $page->getAttribute('page_type') != 'contact' && $page->getAttribute('page_type') != 'news'): ?>
      <?php $this->inc('includes/introduction.php'); ?>
    <?php endif; ?>

  <?php if ($page->getAttribute('page_type') == 'home'): ?>

      <div class="relative">
        <?php $a = new Area('Image Content Block With Slider 1'); $a->display($c); ?>

        <div class="container ic_block_slider_stack white-slick-dots">
          <div class="row">
            <div class="col-sm-5"></div>

            <div class="col-sm-7">
              <div class="clients-selection-slider">
                <ul class="row home-client-selection-slider">
                  <?php $stack = Stack::getByName('MEP Clients Selection Logos White'); $stack->display(); ?>
                </ul>
              </div><!-- /.clients-selection-slider -->
            </div>
          </div>
        </div>
      </div>

      <div class="relative">

      <?php $a = new Area('Image Content Block 1'); $a->display($c); ?>
      <div class="container ic_block_slider_stack white-slick-dots right">
        <div class="row">
          <div class="col-sm-5"></div>

          <div class="col-sm-7">
            <div class="clients-selection-slider">
              <ul class="row home-client-selection-slider">
                <?php $stack = Stack::getByName('Trading & Distribution Clients Selection Logos White'); $stack->display(); ?>
              </ul>
            </div><!-- /.clients-selection-slider -->
          </div>
        </div>
      </div>
    </div>

      <div class="relative">
        <?php $a = new Area('Image Content Block With Slider 2'); $a->display($c); ?>

        <div class="container ic_block_slider_stack white-slick-dots">
          <div class="row">
            <div class="col-sm-5"></div>
            <div class="col-sm-7">
              <div class="clients-selection-slider">
                <ul class="row home-client-selection-slider">
                  <?php $stack = Stack::getByName('Facilities Management Clients Selection Logos White'); $stack->display(); ?>
                </ul>
              </div><!-- /.clients-selection-slider -->
            </div>
        </div>
      </div>
    </div>


      <?php $a = new Area('Image Content Block 2'); $a->display($c); ?>


      <section class="section section_testimonials white-slick-dots">
        <div class="container">

          <div class="testimonial_bg full_bg mainImage" <?php if($editMode) { ?>style="height: auto;"<?php } ?>>
            <div class="testimonials_title_wrap">
              <?php $a = new Area('Testimonials Title'); $a->display($c); ?>
            </div>
          </div><!-- /.testimonials_bg -->

        </div><!-- /.container -->

          <div class="container relative noPadding">

            <div class="testimonials_slider_wrap container noPadding" <?php if($editMode) { ?>style="position: relative;top: 0;right: 0;"<?php } ?>>
              <div class="blurred_image_wrap blurWrap right">
                <div class="blurred_image blurImage" style="background-image: url('<?php echo $CDN_URL . $THEME_URL; ?>images/banner-1.jpg');"></div>
              </div>
              <ul id="testimonials_slider" class="testimonials_list">

                <?php if($editMode) { ?><div style="position: relative; z-index: 5;"><?php } ?>
                  <?php $a = new Area('Client Testimonials'); $a->display($c); ?>
                <?php if($editMode) { ?></div><?php } ?>

              </ul><!-- /.testimonials_list -->

            </div><!-- /.testimonials_wrap -->
          </div><!-- /.container -->

        </section><!-- /.testimonials -->

        <?php $stack = Stack::getByName('Featured News List'); $stack->display(); ?>

      <?php endif; ?>


      <?php if ($page->getAttribute('page_type') == 'mep-solutions'): ?>
        <?php $stack = Stack::getByName('MEP Projects Page List'); $stack->display(); ?>

        <?php $this->inc('includes/clients_selection.php'); ?>

        <section class="more-detail">
          <div class="container">
            <div class="more-detail-wrap">
              <div class="row">
                <div class="col-sm-6 more-detail-box purple-box">
                  <?php $a = new Area('More MEP Detail Left'); $a->display($c); ?>
                </div><!-- /.col -->
                <div class="col-sm-6 more-detail-box">
                 <?php $a = new Area('More MEP Detail Right'); $a->display($c); ?>
               </div><!-- /.col -->
              </div><!-- /.row -->
            </div><!-- /.more-detail-wrap -->
          </div><!-- /.container -->
        </section><!-- /.more-detail -->


        <?php $this->inc('includes/usp.php'); ?>


        <section class="prequalification-documents">
          <div class="container">
            <div class="prequalification-documents-wrap">
              <div class="row">
                <div class="col-sm-8 left">
                  <?php $a = new Area('More Detail Bottom'); $a->display($c); ?>
                </div><!-- /.col -->
                <div class="col-sm-4 right">
                  <?php $a = new Area('More Detail Button'); $a->display($c); ?>
                </div><!-- /.col -->
              </div><!-- /.row -->
            </div><!-- /.prequalification-documents-wrap -->
          </div><!-- /.container -->
        </section><!-- /.prequalification-documents -->

      <?php endif; ?>

      <?php if ($page->getAttribute('page_type') == 'etihad-copper'): ?>
        <?php $stack = Stack::getByName('Etihad Copper Products List'); $stack->display(); ?>

        <?php $this->inc('includes/usp.php'); ?>


        <section class="etihad-copper-enquiry-form">
          <div class="container">
            <div class="row">
              <div class="col-sm-6 col-sm-offset-3">
                <?php $a = new Area('Eithad Copper Enquiry Title'); $a->display($c); ?>
              </div><!-- /.col -->
            </div><!-- /.row -->
            <div class="row">
              <div class="col-sm-6 col-sm-offset-3">
                <?php
                  $stack = Stack::getByName('Enquiry Form');
                  $stack->display();
                ?>
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.container -->
        </section><!-- /.etihad-copper-enquiry-form -->

      <?php endif; ?>

      <?php if ($page->getAttribute('page_type') == 'facilities-management'): ?>
        <section class="large-gallery" id="featured-clients">
          <div class="container text-center">
            <?php $a = new Area('Gallerire title'); $a->display($c); ?>
          </div>
          <ul class="large-gallery-slider">
            <?php $a = new Area('About Gallery'); $a->display($c); ?>
          </ul><!-- /.large-gallery-slider -->
        </section><!-- /.large-gallery -->

        <?php $this->inc('includes/clients_selection.php'); ?>

        <section class="more-detail">
          <div class="container">
            <div class="more-detail-wrap">
              <div class="row">
                <div class="col-sm-6 more-detail-box purple-box">
                  <?php $a = new Area('More Facility Manag Detail Left'); $a->display($c); ?>
                </div><!-- /.col -->
                <div class="col-sm-6 more-detail-box">
                  <?php $a = new Area('More Facility Manag Detail Right'); $a->display($c); ?>
                </div><!-- /.col -->
              </div><!-- /.row -->
            </div><!-- /.more-detail-wrap -->
          </div><!-- /.container -->
        </section><!-- /.more-detail -->

        <?php $this->inc('includes/usp.php'); ?>


      <?php endif; ?>

      <?php if ($page->getAttribute('page_type') == 'brands'): ?>

        <?php $stack = Stack::getByName('Brands Overview Page List'); $stack->display(); ?>

      <?php endif; ?>

      <?php if ($page->getAttribute('page_type') == 'clients'): ?>

        <div class="container">
          <?php $this->inc('includes/breadcrumb.php'); ?>
        </div>

        <?php $this->inc('includes/all_clients.php'); ?>


        <section class="client-testimonials">
          <div class="container">

            <div class="row">
              <div class="col-sm-8 col-sm-offset-2 text-center">
                <?php $a = new Area('Testimonials Title'); $a->display($c); ?>
              </div><!-- /.col -->
            </div><!-- /.row -->

            <ul class="client-t-list client-t-slider">
              <?php $a = new Area('Testimonials List'); $a->display($c); ?>
            </ul>

          </div><!-- /.container -->
        </section><!-- /.clients-testimonials -->


      <?php endif; ?>

      <?php if ($page->getAttribute('page_type') == 'contact'): ?>
        <div class="contact-map">
          <div class="contact-form">
            <div class="container">
              <div class="row">
                <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 contact-form-col">
                  <div class="text-center">
                    <?php $a = new Area('Contact Title'); $a->display($c); ?>
                  </div>
                  <?php $stack = Stack::getByName('Contact Form'); $stack->display(); ?>
                </div><!-- /.col -->
              </div><!-- /.row -->
            </div><!-- /.container -->
          </div><!-- /.contact-form -->
          <?php echo $this->inc('includes/map.php'); ?>
       </div><!-- /.contact-map -->
      <?php endif; ?>

      <?php if ($page->getAttribute('page_type') == 'about'): ?>
        <section class="large-gallery">
          <div class="container text-center">
            <?php $a = new Area('Gallerire title'); $a->display($c); ?>
          </div>
          <ul class="large-gallery-slider">
            <?php $a = new Area('About Gallery'); $a->display($c); ?>
          </ul><!-- /.large-gallery-slider -->
        </section><!-- /.large-gallery -->

        <section class="milestone-section">
          <div class="container">
            <div class="row">
              <div class="col-sm-8 col-sm-offset-2 text-center">
                <?php $a = new Area('Milestone Title'); $a->display($c); ?>
              </div>
            </div>
            <div class="milestone-section-wrap">

            <div class="row">
              <div class="col-sm-6">


                <?php $a = new Area('Milestone Image'); $a->display($c); ?>

              </div><!-- /.col -->
              <div class="col-sm-6">
                <div class="ms-next"></div>
                <div class="ms-prev"></div>

                <div class="milestone-list__parent frame smart">
                  <ul class="milestone-list" id="milestone-list-slider">
                    <?php $a = new Area('Milestones List'); $a->display($c); ?>
                  </ul>
                </div>

              </div><!-- /.col -->
            </div><!-- /.row -->
          </div>

          </div><!-- /.container -->
        </section><!-- /.milesstones-section -->

        <section id="about-al-gurg" class="about-etihad-copper">
          <div class="container">
            <div class="row">
              <div class="col-sm-5 left">
                <?php $a = new Area('About Content Left'); $a->display($c); ?>
              </div><!-- /.col -->
              <div class="col-sm-7 right">
                <?php $a = new Area('About Content Right'); $a->display($c); ?>
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.container -->
        </section>

      <?php endif; ?>

      <?php if ($page->getAttribute('page_type') == 'news'): ?>
        <?php $stack = Stack::getByName('News List'); $stack->display(); ?>
      <?php endif; ?>

      <?php if ($page->getAttribute('page_type') == 'etihad-copper-products'): ?>
        <?php
          $stack = Stack::getByName('Products Page List');
          if($stack) $stack->display();
        ?>

        <div id="productModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-body make-enquery-wrap">
                    <h2>Product Enquiry</h2>
                    <?php $stack = Stack::getByName('Product Enquiry Form');
                    $stack->display(); ?>
                </div>
                <button class="fa fa-times close-modal close-enquiry-modal" data-dismiss="modal"></button>
            </div>
        </div>
      <?php endif; ?>

  </div><!-- /.container-fluid -->
</main><!-- /.site-body -->
