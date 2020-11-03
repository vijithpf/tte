ccmValidateBlockForm = function() {
	
	if ($('#field_1_image_fID-fm-value').val() == '' || $('#field_1_image_fID-fm-value').val() == 0) {
		ccm_addError('Missing required image: Logo');
	}

	if ($('#field_2_textarea_text').val() == '') {
		ccm_addError('Missing required text: Quote');
	}

	if ($('#field_3_textbox_text').val() == '') {
		ccm_addError('Missing required text: Quote By');
	}


	return false;
}
