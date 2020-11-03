<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
?>
<section id="intro" class="section section-intro">
  <div class="container">
  <?php if($page->getAttribute('page_type') != 'home'): ?>
        <?php $this->inc('includes/breadcrumb.php'); ?>
   <?php endif; ?>
    <div class="row">

      <?php if($page->getAttribute('page_type') != 'home') { ?>
      <div class="col-md-2"></div><!-- /.col -->
      <?php } else { ?>
      <div class="col-md-1"></div><!-- /.col -->
      <?php } ?>
      <div class="col-md-8 text-center">
        <?php $a = new Area('Intro Description'); $a->display($c); ?>
      </div><!-- /.col -->
      <div class="col-md-2">
        <?php $a = new Area('Intro Description 2'); $a->display($c); ?>
      </div><!-- /.col -->

    </div><!-- /.row -->
  </div><!-- /.container -->
</section><!-- /#intro -->
