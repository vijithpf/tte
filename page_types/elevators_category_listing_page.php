<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));
$page = Page::getCurrentPage();
?>
<main class="site-body">
    <div class="container-fluid">

        <?php $this->inc('includes/elevator_banner.php'); ?>

        <?php $this->inc('includes/introduction.php'); ?>

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

    </div><!-- /.container-fluid -->
</main><!-- /.site-body -->

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
