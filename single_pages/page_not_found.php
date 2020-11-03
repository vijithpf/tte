<?php defined('C5_EXECUTE') or die('Access Denied.'); ?>

<div class="container not_found_page offset-top text-center">

<h1 class="error"><?php echo t('Page Not Found :(')?></h1>

<?php echo t('No page could be found at this address.')?>

<?php if (is_object($c)) { ?>
	<br/><br/>
	<?php $a = new Area('Main'); $a->display($c); ?>
<?php } ?>
<br/>

<a class="btn light btn-dark btn-underline btn-clr-purple" href="<?php echo DIR_REL?>/">Back to home</a>

</div>
