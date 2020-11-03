<?php   
	
	defined('C5_EXECUTE') or die(_("Access Denied."));
	 
	$concrete_interface = Loader::helper('concrete/interface');
	
	$view_url = View::url('/dashboard/formidable/forms/');
	
	echo $concrete_interface->button('Cancel', $view_url, array(), 'btn');
  	echo $concrete_interface->button('Save', 'javascript:$(\'#ccm-form-record\').trigger(\'submit\')', array(), 'ccm-button-right btn primary accept');

?> 