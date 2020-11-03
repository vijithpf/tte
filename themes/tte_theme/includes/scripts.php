<?php
/** @var View $this */
defined('C5_EXECUTE') or die(_("Access Denied."));
/** @var HtmlHelper $htmlHelper */
$CDN_URL = 'https://static-tte.s3-accelerate.dualstack.amazonaws.com';
$htmlHelper = Loader::helper('html');
?>

<?php $this->addFooterItem($htmlHelper->javascript($CDN_URL . '/js/all.js')); ?>
<script src="https://www.google.com/recaptcha/api.js?onload=CaptchaCallback&render=explicit" async defer></script>
<?php $this->addFooterItem($htmlHelper->javascript($CDN_URL .'/js/custom.js')); ?>