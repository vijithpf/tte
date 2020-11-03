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
