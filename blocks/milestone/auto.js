ccmValidateBlockForm = function() {
	
	if ($('#field_1_textbox_text').val() == '') {
		ccm_addError('Missing required text: Title');
	}

	if ($('#field_2_textarea_text').val() == '') {
		ccm_addError('Missing required text: Description');
	}


	return false;
}
