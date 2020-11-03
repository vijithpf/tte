<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
?>
<main class="site-body">
    <div class="container-fluid">
        <?php $this->inc('includes/banner.php'); ?>

        <?php $this->inc('includes/introduction.php'); ?>

        <?php
            $stack = Stack::getByName('Brands Page List');
            if($stack) $stack->display();
        ?>
    </div><!-- /.container-fluid -->
</main><!-- /.site-body -->
<div id="productModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-body make-enquery-wrap">
            <h2>Product Enquiry</h2>
            <?php $stack = Stack::getByName('Product Enquiry Form'); $stack->display(); ?>
        </div>
        <button class="fa fa-times close-modal close-enquiry-modal" data-dismiss="modal"></button>
    </div>
</div>
