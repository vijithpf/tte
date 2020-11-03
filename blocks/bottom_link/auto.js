ccmValidateBlockForm = function() {
	
	if ($('#field_1_textbox_text').val() == '') {
		ccm_addError('Missing required text: Title');
	}

	if ($('#field_2_image_fID-fm-value').val() == '' || $('#field_2_image_fID-fm-value').val() == 0) {
		ccm_addError('Missing required image: Image');
	}

	if ($('input[name=field_3_link_cID]').val() == '' || $('input[name=field_3_link_cID]').val() == 0) {
		ccm_addError('Missing required link: Link');
	}


	return false;
}
