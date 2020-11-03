<?php 
	defined('C5_EXECUTE') or die(_("Access Denied."));
	
	$concrete_interface = Loader::helper('concrete/interface');
	$navigation = Loader::helper('navigation');
	
	global $c;
	
	$list = Page::getByPath('/dashboard/formidable/forms/');
	$elem = Page::getByPath('/dashboard/formidable/forms/elements');
	$mail = Page::getByPath('/dashboard/formidable/forms/mailings');
	$resu = Page::getByPath('/dashboard/formidable/results/');
	
	$disabled = true;
	if ($f->formID)
		$disabled = false;
	
	echo $concrete_interface->button(t('Back to list'), $navigation->getLinkToCollection($list), '', 'pull-right ccm-button-right margin-right');	
	if (!$disabled) {
		echo $concrete_interface->button(t('Results').($disabled||$f->submissions<=0?'':' ('.$f->submissions.')'), $navigation->getLinkToCollection($resu).'?formID='.$f->formID, '', 'pull-right ccm-button-right margin-right');
		echo $concrete_interface->button(t('Preview'), $navigation->getLinkToCollection($list).'preview/'.$f->formID, '', 'pull-right ccm-button-right');
	}
?>
	<style> .margin-right {margin-left:10px !important;} </style>
	<ul class="nav-tabs nav" id="ccm-tabs-form-options">
		<li class="<?php  echo $c->getCollectionID()==$list->getCollectionID()?'active':''; ?>">
        	<a class="<?php  echo $disabled?'disabled':''; ?>" href="<?php  echo !$disabled?$navigation->getLinkToCollection($list).'edit/'.$f->formID:'#'; ?>"><?php  echo t('Form Properties'); ?></a>
        </li>
        <li class="<?php  echo $c->getCollectionID()==$elem->getCollectionID()?'active':''; ?>">
        	<a class="<?php  echo $disabled?'disabled':''; ?>" href="<?php  echo !$disabled?$navigation->getLinkToCollection($elem).$f->formID:'#'; ?>"><?php  echo t('Layout and elements'); ?></a>
        </li>
        <li class="<?php  echo $c->getCollectionID()==$mail->getCollectionID()?'active':''; ?>">
        	<a class="<?php  echo $disabled?'disabled':''; ?>" href="<?php  echo !$disabled?$navigation->getLinkToCollection($mail).$f->formID:'#'; ?>"><?php  echo t('Emails'); ?></a>
        </li>
	</ul>
