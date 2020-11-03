ccmValidateBlockForm = function() {
	
	if ($('#field_3_textbox_text').val() == '') {
		ccm_addError('Missing required text: Name');
	}

	if ($('#field_4_textbox_text').val() == '') {
		ccm_addError('Missing required text: Position');
	}

	if ($('#field_5_textarea_text').val() == '') {
		ccm_addError('Missing required text: Description');
	}


	return false;
}
