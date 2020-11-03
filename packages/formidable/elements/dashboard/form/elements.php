<?php  
defined('C5_EXECUTE') or die(_("Access Denied."));
$form = Loader::helper('form');
?>
<div class="ccm-ui">
	<div class="well-small well form-inline">
    	<?php  echo $form->label('quick_search_element', t('Search:'), array('style' => 'margin-left:20px; margin-right:5px;')); ?>
        <?php  echo $form->text('quick_search_element', '', array('placeholder' => 'Quick search')); ?>
	</div>
<?php 
ksort($elements);
if (sizeof($elements) > 0) {
	foreach ($elements as $group => $types) {
		ksort($types);	
?>  
	<fieldset>
		<legend><?php  echo t($group); ?></legend> 
		<ul class="searchable_elements">
		<?php  foreach ($types as $element) { ?>
			<li label="<?php  echo $element->element_type; ?>"><?php   echo t($element->element_text)?></li>
		<?php  } ?>
		</ul>
	</fieldset>
<?php  } } else { ?>

	<div class="message alert-message error">
		<?php  echo t('No available elements found!'); ?>
	</div>
<?php  } ?>
</div>