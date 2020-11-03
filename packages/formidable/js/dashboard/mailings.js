// JavaScript Document
ccmFormidableLoadMailings = function(msg) {
	$.ajax({ 
		type: "POST",
		url: list_url,
		data: 'formID='+formID,
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
			if (msg)
				ccmFormidableLoadMessage(msg);
		},
		success: function(html){
			$('#ccm-mailing-list div.mailing_row_wrapper').remove();
			if (html) {				
				$('#ccm-mailing-list').append(html);
				$('#ccm-mailing-list div.mailing_row_wrapper').fadeIn();
				$('div.placeholder, div.loader').fadeOut();
			}
			else 
				ccmFormidableLoadPlaceholder(true);
				
			ccmFormidableCreateMenu();	
		}
	});	
}

ccmFormidableOpenMailingDialog = function(mailingID) {
	var query_string = "?formID="+formID;
	if (parseInt(mailingID) != 0) query_string += "&mailingID="+mailingID;
	jQuery.fn.dialog.open({ 
		width: 700,
		height: 600,
		modal: true,
		href: dialog_url+query_string,
		title: (parseInt(mailingID) != 0)?title_message_edit:title_message_add
	});
}

ccmFormidableAddMailingToForm = function() {
	var params = $('#mailingForm').serialize();
	ccmFormidableSaveMailing(params);
	jQuery.fn.dialog.closeTop();
}

ccmFormidableSaveMailing = function(data) {
	data += '&action=save&formID='+formID;
	$.ajax({ 
		type: "POST",
		url: tools_url,
		data: data,
		dataType: 'json',
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
		},
		success: function(ret) {
			ccmFormidableSetMessage(ret['type'], ret['message']);
			ccmFormidableLoadMailings('save');
		}
	});	
}

ccmFormidableDeleteMailing = function(mailingID) {
	data = 'action=delete&mailingID='+mailingID+'&formID='+formID;
	$.ajax({ 
		type: "POST",
		url: tools_url,
		data: data,
		dataType: 'json',
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
		},
		success: function(ret) {
			ccmFormidableSetMessage(ret['type'], ret['message']);
			ccmFormidableLoadMailings('delete');
		}
	});	
}

ccmFormidableDuplicateMailing = function(mailingID) {
	data = 'action=duplicate&mailingID='+mailingID+'&formID='+formID;
	$.ajax({ 
		type: "POST",
		url: tools_url,
		data: data,
		dataType: 'json',
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
		},
		success: function(ret) {
			ccmFormidableSetMessage(ret['type'], ret['message']);
			ccmFormidableLoadMailings('duplicate');
		}
	});	
}

ccmFormidableCheckFormMailingSubmit = function() {
	var errors = [];
	var data = $('#mailingForm').serialize();	
	$.ajax({ 
		type: "POST",
		url: tools_url+'?action=validate',
		data: data,
		dataType: 'json',
		success: function(data) {
			if (data!==false) {
				var message = $('div.dialog_message').empty();
				$.each(data, function(i, row) {
					message.append(row+'<br />').show();
				});
				$("div.element-body").scrollTop(0);
			} else ccmFormidableAddMailingToForm();
		}
	});	
}

ccmFormidableFormMailingAddAttachment = function(s) {
	if (s instanceof jQuery) {} else { s = $(s); }
	
	var attachments_options = s.parents('.attachments_options');
	attachment_counter++;
		
	var attachment = "<div class=\"file_selector\">"+attachment_default+"</div>";
	attachment = attachment.replace(/counter_tmp/g, attachment_counter);
	attachment += '<a href="javascript:;" onclick="ccmFormidableFormMailingAddAttachment(this);" class="btn success option_button">+</a> ';
	attachment += '<a href="javascript:;" onclick="ccmFormidableFormMailingRemoveAttachment(this);" class="btn error option_button">-</a>';
	var new_attachment = $('<div>').addClass('input attachment_row')
							  	   .append($('<div>').addClass('input_prepend')
													 .html(attachment));
	s.parents('.input').after(new_attachment);
	
	attachments_options.find('.error').attr('disabled', false);	
}

ccmFormidableFormMailingRemoveAttachment = function(s) {
	if (s instanceof jQuery) {} else { s = $(s); }
	var attachments_options = s.parents('.attachments_options');
	if (attachments_options.children('.input').length > 2)
		s.parents('.input').remove();
	
	if (attachments_options.find('.input').length == 2)
		attachments_options.find('.error').attr('disabled', true);	
}

ccmFormidableFormMailingCheckSelectors = function(s) {
	
	var element = $("textarea[name=send_custom_value]");
	if ($('input[name=send_custom]').attr('checked')) {
		element.attr('disabled', false).next('.note').slideDown();
	} else
		element.val("").attr('disabled', true).next('.note').slideUp();

	if ($('select[name=from_type]').val() == 'other') {
		$("input[name=from_name], input[name=from_email]").attr('disabled', false).parents('.input').slideDown();
		$('div.reply_to').slideDown().find('input').attr('disabled', false);
		if (s && s.attr('name') == 'from_type') {
			$("input[name=from_name]").focus();
			$('div.reply_to').find('select[name=reply_type]').val('from');
		}
	} else {
		$("input[name=from_name], input[name=from_email]").attr('disabled', true).val('').parents('.input').slideUp();
		$('div.reply_to').slideUp().find('input').attr('disabled', true).va;('');
	}
	
	if ($('select[name=reply_type]').val() == 'other') {
		$("input[name=reply_name], input[name=reply_email]").attr('disabled', false).parents('.input').slideDown();
		if (s && s.attr('name') == 'reply_type') $("input[name=reply_name]").focus();
	} else 
		$("input[name=reply_name], input[name=reply_email]").attr('disabled', true).val('').parents('.input').slideUp();
			
	var element = $("select[id=attachments_element_value]");
	if ($('input[name=attachments_element]').attr('checked')) {	
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'attachments_element') element.focus();
	} else 
		element.val("").attr('disabled', true).next('.note').slideUp();
	
	var element = $("select[name=templateID]");
	if ($('input[name=template]').is(':checked')) {
		element.attr('disabled', false).focus();
	} else 
		element.val("").attr('disabled', true);

}

ccm_editorFormidableOverlay = function(formID) {

	tinyMCE.activeEditor.focus();
	var bm = tinyMCE.activeEditor.selection.getBookmark();

    $.fn.dialog.open({
        title: title_element_overlay,
        href: tools_url+'?action=select&formID='+formID,
        width: 550,
        modal: false,
        height: 400
    });
		
    ccm_editorSelectFormidableElement = function(label, label2) {
		var mceEd = tinyMCE.activeEditor;	
		mceEd.selection.moveToBookmark(bm);
		
		var selectedText = '{%'+label+'%}';
		if (label2 != '') selectedText += ': {%'+label2+'%}';
		tinyMCE.execCommand('mceInsertRawHTML', false, selectedText, true); 
		$.fn.dialog.closeTop();
	}
}

ccm_editorFormidableSubjectOverlay = function(formID) {

    $.fn.dialog.open({
        title: title_element_overlay,
        href: tools_url+'?action=select&formID='+formID,
        width: 550,
        modal: false,
        height: 400
    });
		
    ccm_editorSelectFormidableElement = function(label, label2) {		
		var selectedText = '{%'+label+'%}';
		if (label2 != '') selectedText += ': {%'+label2+'%}';
		$('#subject').val($('#subject').val() + selectedText);
		$.fn.dialog.closeTop();
	}
}