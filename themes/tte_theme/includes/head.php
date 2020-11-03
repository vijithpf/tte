<?php
/** @var View $this */
defined('C5_EXECUTE') or die(_("Access Denied."));
/** @var HtmlHelper $htmlHelper */
$CDN_URL = 'https://static-tte.s3-accelerate.dualstack.amazonaws.com';
$THEME_URL = '/themes/tte_theme/';
$htmlHelper = Loader::helper('html');
$nh = Loader::helper('navigation');
$ih = Loader::helper('image');
$page       = $c;

$bannerImages = $page->getAttribute('banner_images');
$sharingFallback = BASE_URL . $this->getThemePath() . '/images/sharing.jpg';

if ($bannerImages) {
  //create an array
  $arrayOfImages = [];
  foreach ($bannerImages as $img) {
    $f = $img['file'];
    $bannerSrc = $ih->getThumbnail($f, 1600, 1600, false);

    $bannerSrcs = $bannerSrc->src;
    //push images to the array
    array_push($arrayOfImages, $bannerSrcs);

    //get first image in the array
    $bannerImg = $arrayOfImages[0];
  }
}

$sharingImg = ($bannerImages ? BASE_URL . $bannerImg : $sharingFallback);

?>
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<!-- Meta Tags for Social Media -->
<meta property="og:image" content="<?php echo $sharingImg; ?>">
<meta property="og:title" content="<?php echo SITE; ?> | <?php echo $page->getCollectionName(); ?>">
<meta property="og:description" content="<?php echo $page->getCollectionDescription(); ?>">
<meta name="twitter:title" content="<?php echo SITE; ?> | <?php echo $page->getCollectionName(); ?>">
<meta name="twitter:image" content="<?php echo $sharingImg; ?>">
<meta name="twitter:description" content="<?php echo $page->getCollectionDescription(); ?>">

<?php //$this->addHeaderItem($htmlHelper->css( 'css/all.css')); ?>
<?php //$this->addHeaderItem($htmlHelper->css('css/style.css')); ?>

<?php $this->addHeaderItem($htmlHelper->css($CDN_URL . $THEME_URL . 'css/all.css')); ?>
<?php $this->addHeaderItem($htmlHelper->css($CDN_URL . $THEME_URL . 'css/style.css')); ?>

<?php $this->addHeaderItem('<link type="text/css" rel="stylesheet" media="print" href="' . $CDN_URL . $THEME_URL . 'css/print.css">'); ?>

<?php
print $this->controller->outputHeaderItems();
$_trackingCodePosition = Config::get('SITE_TRACKING_CODE_POSITION');
if (empty($disableTrackingCode) && $_trackingCodePosition === 'top') {
    echo Config::get('SITE_TRACKING_CODE');
}
echo (is_object($c)) ? $c->getCollectionAttributeValue('header_extra_content') : '';
?>

<!--[if IE]>
<link type="text/css" rel="stylesheet" href="<?php echo $CDN_URL . $THEME_URL; ?>css/ie.css<?php echo '?t=' . filemtime($this->getThemeDirectory() . '/css/ie.css'); ?>"/>
<![endif]-->

<!--[if lt IE 9]>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

<!--[if !IE]><!-->
<script>
    if (/*@cc_on!@*/false) {
        document.documentElement.className += ' ie10';
    }
</script>
<!--<![endif]-->

<script>
    //set cookie for site
    function setCookie(cname, cvalue) {
        var d = new Date();
        d.setTime(d.getTime() + 2160000000);
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
    }
</script>

<!-- Global site tag (gtag.js) - Google Analytics -->

<script async src="https://www.googletagmanager.com/gtag/js?id=UA-137340655-1"></script>

<script>

  window.dataLayer = window.dataLayer || [];

  function gtag(){dataLayer.push(arguments);}

  gtag('js', new Date());

 

  gtag('config', 'UA-137340655-1');

</script>


