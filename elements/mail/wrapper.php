<?php
/* @var MailHelper $mh */
/* @var string $template */
/* @var string $pkgHandle */
defined('C5_EXECUTE') or die(_('Access Denied.'));

if (!isset($pkgHandle)) {
    $pkgHandle = null;
}

$theme = PageTheme::getSiteTheme();
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo e(SITE); ?></title>
</head>
<body style="background-color: #fff; font-family:'Open Sans', sans-serif; font-size:12px; line-height:22px; margin:0; padding:0; color:#333;">
<table width="680" border="0" cellspacing="0" style="border: 0 none;">
    <tr style="border: 0 none;">
        <td height="50">
            <a href="<?php echo BASE_URL; ?>" target="_blank">
                <img style="display:block;margin-left:10px;" src="<?php echo BASE_URL . $theme->getThemeURL() . '/images/logo-email.png'; ?>" alt="<?php echo e(SITE); ?>"/>
            </a>
        </td>
    </tr>
    <tr style="border: 0 none;">
        <td>
            <div style="font-family: 'Open Sans', sans-serif; padding: 10px 20px; color: #333;">
                <?php Loader::element('mail/' . $template, $mh->getParameters(), $pkgHandle); ?>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
