<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
?>
<main class="site-body">
  <div class="container-fluid">

      <?php if ($page->getAttribute('page_type') == 'home'){ ?>
        <?php $this->inc('includes/home_banner.php'); ?>
      <?php } else { ?>
        <?php $this->inc('includes/banner.php'); ?>
      <?php } ?>

      <?php $this->inc('includes/introduction.php'); ?>

      <?php $stack = Stack::getByName('Brand Products Page List'); $stack->display(); ?>

        <section class="about-etihad-copper">
            <div class="container">
            <div class="row">
              <div class="col-sm-5 left">
                <?php $a = new Area('Left Content'); $a->display($c); ?>
              </div><!-- /.col -->
              <div class="col-sm-7 right">
                <?php $a = new Area('Right Content'); $a->display($c); ?>
              </div><!-- /.col -->
            </div><!-- /.row -->
          </div><!-- /.container -->
        </section>


        <?php $this->inc('includes/usp.php'); ?>

      <?php $stack = Stack::getByName('Related Brands Page List'); $stack->display(); ?>

  </div><!-- /.container-fluid -->
</main><!-- /.site-body -->
