<?php   
	
	defined('C5_EXECUTE') or die(_("Access Denied."));
	 
	$concrete_interface = Loader::helper('concrete/interface');
	
	$forms_url = View::url('/dashboard/formidable/forms/');
	$properties_url = ($data['formID']?View::url('/dashboard/formidable/forms/', 'edit', $data['formID']):'javascript:;');
	$elements_url = ($data['formID']?View::url('/dashboard/formidable/forms/elements', $data['formID']):'javascript:;');
	$mailings_url = ($data['formID']?View::url('/dashboard/formidable/forms/mailings', $data['formID']):'javascript:;');
	$results_url = ($data['formID']?View::url('/dashboard/formidable/results/?formID='.$data['formID']):'javascript:;');
	
	$delete_url = ($data['formID']?View::url('/dashboard/formidable/forms/', 'delete', $data['formID']):'javascript:;');
		
	echo $concrete_interface->button(t('Properties'), $properties_url, '', 'btn '.(!$data['formID']?'disabled':''))."&nbsp;";
    echo $concrete_interface->button(t('Elements'), $elements_url, '', 'btn '.(!$data['formID']?'disabled':''))."&nbsp;";		
	echo $concrete_interface->button(t('Mailings'), $mailings_url, '', 'btn '.(!$data['formID']?'disabled':''))."&nbsp;";	
	if ($data['submissions'] > 0)
		echo $concrete_interface->button('Results ('.$data['submissions'].')', $results_url, '', 'btn '.(!$data['formID']?'disabled':''))."&nbsp;";			
    
	echo $concrete_interface->button(t('Back to forms'), $forms_url, '', 'ccm-button-right');
	
	//echo $concrete_interface->button('Delete form', $delete_url, '', 'ccm-button-right btn error '.(!$data['formID']?'disabled':''), array('onclick' => 'return confirm(\'Are you sure you want to delete this form?\');'));
?> 