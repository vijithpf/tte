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

        <section class="prequalification-documents">
          <div class="container">
            <div class="prequalification-documents-wrap">
              <div class="row">
                <div class="col-sm-8 left">
                  <?php $a = new Area('Corporate Profile Title'); $a->display($c); ?>
                </div><!-- /.col -->
                <div class="col-sm-4 right">
                  <?php $a = new Area('Callback Form'); $a->display($c); ?>
                </div><!-- /.col -->
              </div><!-- /.row -->
            </div><!-- /.prequalification-documents-wrap -->
          </div><!-- /.container -->
        </section><!-- /.prequalification-documents -->

      <?php } else { ?>

        <section class="all-brands">
          <div class="container">
            <div class="all-brands__row">

            <?php
            $stack = Stack::getByName('Elevators Products List');
            $stack->display();
            $stack = Stack::getByName('Elevators Categories List');
            $stack->display();
            // $stack = Stack::getByName('Products Page List');
            // $stack->display();
            // $stack = Stack::getByName('Categories Page List');
            // $stack->display();
            // die();
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

            </div>
          </div>
        </section>

        <?php
        $stack = Stack::getByName('Elevators Callback');
        $stack->display();
        ?>

    <?php } ?>

  </div><!-- /.container-fluid -->
</main><!-- /.site-body -->
