<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));
$th = Loader::helper('text');
$dh = Loader::helper('date');
$ih = Loader::helper('image');
$page = $c;
?>
<main class="site-body">
  <div class="container-fluid">

      <?php $this->inc('includes/elevator_banner.php'); ?>

      <?php $this->inc('includes/introduction.php'); ?>

      <?php if ($page->getAttribute('page_type') == 'elevators'){ ?>

        <?php $stack = Stack::getByName('Elevators Sub Page List'); $stack->display(); ?>
        
        <?php
        // $stack = Stack::getByName('Key Projects'); $stack->display();
        ?>

        <section class="large-gallery mep-gallery">
          <div class="container">
            <div class="row">
              <div class="col-sm-12 text-center">
                <?php $a = new Area('Key Projects Title'); $a->display($c); ?>
              </div>
            </div>
          </div>
          <ul class="large-gallery-slider">

            <?php // $stack = Stack::getByName('Projects Slider'); $stack->display(); ?>
            <?php $a = new Area('Project Slides'); $a->display($c); ?>

          </ul>
        </section>

        <?php $this->inc('includes/usp.php'); ?>

        <section class="prequalification-documents callback-form-wrap">
          <div class="container">
            <div class="prequalification-documents-wrap">
              <div class="row">
                <div class="col-sm-5 left">
                  <?php
                  $stack = Stack::getByName('Elevators Callback Form Title');
                  $stack->display();
                  ?>
                </div><!-- /.col -->
                <div class="col-sm-7 right">
                  <?php
                  $stack = Stack::getByName('Elevators Callback Form');
                  $stack->display();
                  ?>
                </div><!-- /.col -->
              </div><!-- /.row -->
            </div><!-- /.prequalification-documents-wrap -->
          </div><!-- /.container -->
        </section><!-- /.prequalification-documents -->

      <?php } else { ?>

        <?php if($page->getAttribute('elevators_page_type') == 'escalators_moving_walk'): ?>
        <section class="escalators-moving-walk-grid-wrap">
          <div class="container">
          
            <div class="custom-grid-wrap">
              <div class="item-wrap row">
                <div class="col-md-4">
                  <div class="img-wrap">
                    <?php $a = new Area('Project Image (354*672)'); $a->display($c); ?>
                  </div>
                </div>
                <div class="col-md-8 space-left">
                  <div class="content-wrap">
                    <?php $a = new Area('Project A Title 1'); $a->display($c); ?>
                  </div>
                  <div class="row item-sub-wrap">
                    <div class="col-md-6">
                      <div class="img-wrap">
                        <?php $a = new Area('Project Image (355*490)'); $a->display($c); ?>
                      </div>
                    </div>
                    <div class="col-md-6 space-left">
                      <div class="content-wrap">
                        <?php $a = new Area('Project A Title 2'); $a->display($c); ?>
                        <a href="#" class="btn btn-underline btn-clr-purple enquire-btn-modal" data-product="Home Elevator" data-target="#productModal" datatype="url">Enquire Now</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="item-wrap second-grid row">
                <div class="col-md-8">
                  <div class="img-wrap">
                    <?php $a = new Area('Project Image (714*660)'); $a->display($c); ?>
                  </div>
                </div>
                <div class="col-md-4 space-left">
                  <div class="img-wrap">
                    <?php $a = new Area('Project Image (390*370)'); $a->display($c); ?>
                  </div>
                  <div class="content-wrap">
                    <?php $a = new Area('Project B Title 1'); $a->display($c); ?>
                    <a href="#" class="btn btn-underline btn-clr-purple enquire-btn-modal" data-product="Home Elevator" data-target="#productModal" datatype="url">Enquire Now</a>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </section>
        <?php elseif($page->getAttribute('elevators_page_type') == 'maintenance_modernisation'): ?>
          <section class="escalators-moving-walk-grid-wrap">
          <div class="container">
            <div class="col-grid-wrap">

              <?php $a = new Area('Content Block'); $a->display($c); ?>

            </div>
          </div>
        </section>
        <?php else: ?>
        <section class="all-brands">
          <div class="container">
            <div class="all-brands__row">

            <?php
            $stack = Stack::getByName('Elevators Products List');
            $stack->display();
            $stack = Stack::getByName('Elevators Categories List');
            $stack->display();
            ?>

            </div>
          </div>
        </section>
        <?php endif; ?>

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

        <section class="prequalification-documents callback-form-wrap">
          <div class="container">
            <div class="prequalification-documents-wrap">
              <div class="row">
                <div class="col-sm-5 left">
                  <?php
                  $stack = Stack::getByName('Elevators Callback Form Title');
                  $stack->display();
                  ?>
                </div><!-- /.col -->
                <div class="col-sm-7 right">
                  <?php
                  $stack = Stack::getByName('Elevators Callback Form');
                  $stack->display();
                  ?>
                </div><!-- /.col -->
              </div><!-- /.row -->
            </div><!-- /.prequalification-documents-wrap -->
          </div><!-- /.container -->
        </section><!-- /.prequalification-documents -->

    <?php } ?>

  </div><!-- /.container-fluid -->
</main><!-- /.site-body -->