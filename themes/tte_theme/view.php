<?php
/* @var View $this */
/* @var Page $c */
/* @var string $bodyClass */
defined('C5_EXECUTE') or die(_('Access Denied.'));

global $u;

$page = $c;

$pageClass = $page->getCollectionTypeHandle();
if (!$pageClass) {
    $pageClass = $page->getCollectionHandle();
}
$pageClass .= '-view-page';

if (!$bodyClass) {
    $bodyClass = '';
}
$bodyClass .= ' ' . $pageClass;
if (User::isLoggedIn()) {
    $bodyClass .= ' logged-in';
}
if ($page->isEditMode()) {
    $bodyClass .= ' edit-mode';
}
if ($u->isAdmin()) {
    $bodyClass .= ' admin-user';
}
if ($page->getAttribute('page_type') == 'p-detail') {
    $bodyClass .= ' p-detail';
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en-us" class="ie10 ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en-us" class="ie10 ie9"> <![endif]-->
<!--[if gt IE 9]><!--> <html lang="en-us"> <!--<![endif]-->
<head>
    <?php Loader::element('header_required', array('noOutput' => true)); ?>
    <?php $this->inc('includes/head.php'); ?>
</head>
<body class="<?php echo $bodyClass; ?>">
<div class="wrapper">
    <!-- Wrap all page content here -->
    <?php $this->inc('includes/header.php'); ?>
    <?php Loader::element('system_errors', array('error' => $error)); ?>
    <?php print $innerContent ?>
    <?php $this->inc('includes/footer.php'); ?>
</div>
<?php $this->inc('includes/scripts.php'); ?>
<?php Loader::element('footer_required'); ?>
</body>
</html>
