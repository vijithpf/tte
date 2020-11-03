<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = $c;
$CDN_URL = 'https://static-tte.s3-accelerate.dualstack.amazonaws.com';
$THEME_URL = '/themes/tte_theme/';
?>

<?php if( !$page->getAttribute("hide_bottom_links") ){ ?>
  <!-- 3 blocks for pages -->
  <div class="bottom-items">
    <div class="container">
      <div class="links-hoster">
       <div class="container">
         <div class="row">
           <ul class="link-blod list-inline">
             <li><?php $a = new GlobalArea('Botlink A'); $a->display($c); ?></li>
             <li><?php $a = new GlobalArea('Botlink B'); $a->display($c); ?></li>
             <li><?php $a = new GlobalArea('Botlink C'); $a->display($c); ?></li>
           </ul><!-- /.link-blod -->
         </div><!-- /.row -->
       </div><!-- /.container -->
     </div><!-- /.links-hoster -->
   </div><!-- /.container -->
  </div><!-- /bottom-items -->
<?php } ?>

<footer class="main-footer">
  <div class="container">

    <div class="row main_footer__row">

      <div class="main-footer__col left footer-logo">

        <div class="main-footer__innerrow clearfix">

          <div class="main-footer__innercol left">
            <a href="http://www.algurg.com/" target="_blank" class="main-footer__logo">
              <img src="<?php echo $CDN_URL . $THEME_URL; ?>images/footer-logo.png" alt="...">
            </a>
          </div>

          <div class="main-footer__innercol right">
            <?php $a = new GlobalArea('Footer 1'); $a->display($c); ?>
          </div>

        </div>

      </div>

      <div class="main-footer__col middle">

        <div class="main-footer__innerrow clearfix">
          <div class="main-footer__innercol">
            <?php $a = new GlobalArea('Footer 2'); $a->display($c); ?>
          </div>
          <div class="main-footer__innercol">
            <?php $a = new GlobalArea('Footer 3'); $a->display($c); ?>
          </div>
        </div>

      </div>
      <div class="main-footer__col right footer_search">
        <?php $a = new GlobalArea('Footer 4'); $a->display($c); ?>

        <ul>
         <li><a href="https://www.linkedin.com/company/1204071/" target="_blank"><span class="fa fa-linkedin"></span></a></li>
        </ul>

        <?php $a = new GlobalArea('Newsletter Title'); $a->display($c); ?>
        <?php $stack = Stack::getByName('Newsletter'); $stack->display(); ?>

      </div>
    </div>

  </div>
  <div class="copyright">
    <div class="row noMargin">
      <div class="col-sm-3">

      </div>
      <div class="col-sm-7 text-center">
        <p class="copy">&copy; <?php echo date('Y'); ?> Technical &amp; Trading LLC. All Rights Reserved.</p>

      </div>
      <div class="col-sm-2">
        <a href="https://www.tentwenty.me/" target="_blank"><img src="<?php echo $CDN_URL . $THEME_URL; ?>images/by-tentwenty-white.svg" alt="website design development tentwenty digital agency dubai" class="ttlogo"></a>
      </div>
    </div>
  </div>
</footer>


<div class="modal fade in" id="ie-modal">
 <div class="modal-dialog">
   <div class="modal-content">

       <div class="modal-header">
         <div class="row">

           <div class="col-sm-9">
             <h3 class="modal-title">Please Update Your Browser</h3>
           </div>

           <div class="clearfix col-sm-3">
             <button class="fa fa-times close-modal" data-dismiss="modal" type="button"></button>
           </div>

         </div>
       </div>
       <div class="modal-body">
         <p>You are using <strong>outdated browser</strong>, Please use Modern browsers like Google Chrome, Firefox, Edge etc or atleast latest Internet Explorer</p>
       </div>
   </div>
 </div>
</div>


<!-- Go to top button -->
<div class="gotoTop"><span class="fa fa-angle-up"></span></div>
