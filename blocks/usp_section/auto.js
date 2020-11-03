ccmValidateBlockForm = function() {
	
	if ($('#field_1_textbox_text').val() == '') {
		ccm_addError('Missing required text: Main Title');
	}

	if ($('#field_2_image_fID-fm-value').val() == '' || $('#field_2_image_fID-fm-value').val() == 0) {
		ccm_addError('Missing required image: Image');
	}


	return false;
}
