ccmValidateBlockForm = function() {
	
	if ($('select[name=field_5_select_value]').val() == '' || $('select[name=field_5_select_value]').val() == 0) {
		ccm_addError('Missing required selection: Position of the Block');
	}


	return false;
}
