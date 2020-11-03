<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
?>
<main class="site-body">
  <div class="container-fluid">

      <?php $this->inc('includes/trading_banner.php'); ?>

      <?php $this->inc('includes/introduction.php'); ?>

      <?php if ($page->getAttribute('page_type') == 'trading-and-distribution'){ ?>

        <?php $stack = Stack::getByName('Trading Sub Page List'); $stack->display(); ?>

      <?php } else { ?>

        <?php $stack = Stack::getByName('Categories Page List'); $stack->display(); ?>

      <?php } ?>

  </div><!-- /.container-fluid -->
</main><!-- /.site-body -->
