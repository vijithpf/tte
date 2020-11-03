// JavaScript Document
var serialized_form = '';

$(function() { 
	$('form[name="formidable_form_edit"] input[type="submit"]').on('click', function() { 
		$('form[name="formidable_form_edit"]').attr('date-submitted', 'true');	
	});	
	$(window).bind('beforeunload', function(e) {
		if (!!$('form[name="formidable_form_edit"]').attr('date-submitted') == false) {
			if (serialized_form != $('form[name="formidable_form_edit"]').serialize()) {
				return changed_values;
			}
		}
	});
});

ccmFormidableFormCheckSelectors = function(s) {
	
	var element = $("input[name=captcha_label]");
	if ($('input[name=captcha]').attr('checked')) {
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'captcha') element.focus().val(element.attr('placeholder'));
	} else
		element.attr('disabled', true).val('').next('.note').slideUp();
	
	var element = $("input[name=clear_button_label]");
	if ($('input[name=clear_button]').attr('checked')) {
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'clear_button') element.focus().val(element.attr('placeholder'));
	} else
		element.attr('disabled', true).val('').next('.note').slideUp();
	
	var element = $("a[id=review_content_btn]");
	if ($('input[name=review]').attr('checked')) {
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'review') element.attr('href', element.attr('data-js')).attr('data-js', '')
	} else {
		element.attr('disabled', true).next('.note').slideUp();
		if (s && s.attr('name') == 'review') element.attr('data-js', element.attr('href')).attr('href', 'javascript:;');
	}
			
	if ($('input[name=review]').attr('checked'))
		$("div[id=review]").slideDown();
	else
		$("div[id=review]").slideUp();
	
	if ($('select[name=submission_redirect]').val() == 0) {
		$("div[id=submission_redirect_content]").slideDown();
		$("div[id=submission_redirect_page]").slideUp();
	} else {
		$("div[id=submission_redirect_content]").slideUp();
		$("div[id=submission_redirect_page]").slideDown();
	}
	
	var element_value = $("input[name=limit_submissions_value]");
	var element_type = $("select[name=limit_submissions_type]");
	var element_div = $("div[id=limit_submissions_div]");
	if ($('input[name=limit_submissions]').attr('checked')) {
		element_value.attr('disabled', false);
		element_type.attr('disabled', false);
		element_div.slideDown().next('.note').slideDown();
		if (s && s.attr('name') == 'limit_submissions') element_value.focus().val('');
	} else {
		element_value.attr('disabled', true);
		if (s && s.attr('name') == 'limit_submissions') element_value.val('');
		element_type.attr('disabled', true).prop('selectedIndex', 0);
		element_div.slideUp().next('.note').slideUp();
	}
	
	if ($('select[name=limit_submissions_redirect]').val() == 0) {
		$("div[id=limit_submissions_redirect_content]").slideDown();
		$("div[id=limit_submissions_redirect_page]").slideUp();
	} else {
		$("div[id=limit_submissions_redirect_content]").slideUp();
		$("div[id=limit_submissions_redirect_page]").slideDown();
	}
	
	var element_div = $("div[id=schedule_div]");
	if ($('input[name=schedule]').attr('checked')) {
		element_div.slideDown().next('.note').slideDown();
	} else 
		element_div.slideUp().next('.note').slideUp();
	
	if ($('select[name=schedule_redirect]').val() == 0) {
		$("div[id=schedule_redirect_content]").slideDown();
		$("div[id=schedule_redirect_page]").slideUp();
	} else {
		$("div[id=schedule_redirect_content]").slideUp();
		$("div[id=schedule_redirect_page]").slideDown();
	}
	
	var element = $("input[name=css_value]");
	if ($('input[name=css]').attr('checked')) {
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'default') element.focus();
	} else 
		element.attr('disabled', true).val('').next('.note').slideUp();
		
	serialized_form = $('form[name="formidable_form_edit"]').serialize();
}


ccmFormidableAddContent = function(object) {
	var content = $("input[id="+object+"]").val();
	jQuery.fn.dialog.open({ 
		width: 670,
		height: 470,
		modal: true,
		href: editor_url+"?object="+object+'&content='+encodeURIComponent(content),
		title: (content.length>0)?edit_content:add_content
	});
}
		
ccmFormidableLoadContent = function(object) {
	var serializedstring = $("#editorForm textarea[name=content]").val();				
	serialized = decodeURIComponent((serializedstring).replace(/\+/g, '%20'));
	$("input[id="+object+"]").val(serialized);		
	jQuery.fn.dialog.closeTop();
}

ccmFormidableClearContent = function(object) {
	$("input[id="+object+"]").val('');
	jQuery.fn.dialog.closeTop();	
}