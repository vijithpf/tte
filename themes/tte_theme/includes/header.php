<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
?>

<?php $this->inc('includes/logo_svg.php'); ?>

<div class="temp-wrap">
  <div class="right-links">
    <ul class="list-inline">
      <li><a href="tel:+971800883" class="call" title="Call">Call</a></li>
      <li><a href="<?php echo View::url('/contact'); ?>" class="enquire toggle-menu menu-left push-body jPushMenuBtn" title="Enquire">Enquire</a></li>
    </ul>
  </div>
</div>

<?php
  $parentPage = Page::getByID($page->getCollectionParentID());
  $parentPageHandle = $parentPage->getCollectionHandle();

  $altClass = ($parentPageHandle == 'news' ? ' alt' : '');
?>
<header id="header" class="main-header<?php echo $altClass; ?>">
  <div class="container">
    <div class="row">
      <div class="col_left">
        <a href="<?php echo View::url('/'); ?>" class="header-logo">
          <svg class="header-logo" viewBox="0 0 144.48 59.27">
            <use xlink:href="#logo" />
          </svg>
        </a>
      </div>
      <div class="col_right">
        <div class="row">
          <?php
            $stack = Stack::getByName('Main Navigation');
            $stack->display();
          ?>
          <ul class="main_nav right">
            <li class="menu-hamburger">
              <a href="#">
                <div class="hamburger"></div>
              </a>
              <div class="other_links other_links_mobile">
                <?php $stack = Stack::getByName('Other Links'); $stack->display(); ?>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
    <div class="nav_overlay"></div><!-- /.nav_overlay -->

  <div class="other_links">
    <?php $this->inc('includes/other_links.php'); ?>
  </div>
</header>

<div class="mobile-nav">
  <?php
    $stack = Stack::getByName('Main Navigation');
    $stack->display();
  ?>

  <?php $stack = Stack::getByName('Other Links'); $stack->display(); ?>
</div>

<nav class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-left">
  <div class="container">
    <button class="toggle-menu menu-left push-body fa fa-times pull-right close-enquiry jPushMenuBtn noNeedForClass"></button>
		<div class="enquiry-form">
      <div class="form-container">
				<div class="row">
					  <div class="col-sm-10 col-sm-offset-1">
                <h1 class="text-center">Make an Enquiry</h1>

                <?php
                  $stack = Stack::getByName('Enquiry Form');
                  $stack->display();
                ?>
            </div>
				 </div>
			</div>
    </div>
  </div>
</nav>
