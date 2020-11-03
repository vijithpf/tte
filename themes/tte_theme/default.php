<?php
/* @var View $this */
/* @var Page $c */
/* @var string $bodyClass */
defined('C5_EXECUTE') or die(_('Access Denied.'));

global $u;

$page = $c;

$pageType = (string) $page->getAttribute('page_type');
if (!$pageType) {
    $pageType = 'default';
}

if (!$bodyClass) {
    $bodyClass = '';
}
$bodyClass .= ' ' . $pageType . '-page';
if (User::isLoggedIn()) {
    $bodyClass .= ' logged-in';
}
if ($page->isEditMode()) {
    $bodyClass .= ' edit-mode';
}
if ($u->isAdmin()) {
    $bodyClass .= ' admin-user';
}
?>
<!DOCTYPE html>
<!--[if lte IE 8]> <html lang="en-us" class="ie10 ie9 ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en-us" class="ie10 ie9"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="en-us"> <!--<![endif]-->
<head>
    <?php Loader::element('header_required', array('noOutput' => true)); ?>
    <?php $this->inc('includes/head.php'); ?>
</head>
<body class="<?php echo $bodyClass; ?>">
<!--<div class="site-loader">
    <div class="logo-middle">
        <img src="<?php //echo $this->getThemePath(); ?>/images/logo-purple.png" alt="<?php //echo e(SITE); ?>"/>
    </div>
</div>-->
<script>
    if (document.cookie.indexOf("visited=") == -1) {
        setCookie("visited", "1");
        $('.site-loader').show();
    }
</script>
<div class="wrapper">
    <?php $this->inc('includes/header.php'); ?>
    <?php $this->inc('includes/main.php'); ?>
    <?php $this->inc('includes/footer.php'); ?>
</div>
<?php $this->inc('includes/scripts.php'); ?>
<?php Loader::element('footer_required'); ?>
</body>
</html>
