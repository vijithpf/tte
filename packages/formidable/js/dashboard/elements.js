// JavaScript Document

var txt_min_height = 18;
var txt_max_height = 70;
var toggle_delay = 350;

ccmFormidableLoadBackgroundImagesForLabels = function() {
	
	$('li[label]').each(function() {
		$(this).css('background-image', 'url(' + CCM_REL + '/packages/formidable/images/icons/' + $(this).attr('label') + '.png)');	
	});
}

ccmFormidableFormElementCheckSelectors = function(s) {
	
	$("input.option_default").mousedown(function() {
		$(this).data('wasChecked', this.checked);
	});
	$("input.option_default").click(function() {
		if ($(this).data('wasChecked'))
			this.checked = false;
	});	
	
	if ($('input[name=default_value]').is(':checked')) {
		$('select[name=default_value_type]').attr('disabled', false);	
		
		if ($('select[name=default_value_type]').val() == 'value') {
			
			$("div[id=default_value_type_value]").slideDown().find('input[name=default_value_value], textarea[name=default_value_value]').attr('disabled', false).next('.note, .note_attribute').slideDown();
			if ($('input[name=default_value_value]').attr('data-mask'))
				$('input[id=default_value_value]').mask($('input[name=default_value_value]').attr('data-mask'));
			
			if ((s && s.attr('name') == 'default_value_type') || (s && s.attr('name') == 'default_value')) {
				$("input[name=default_value_value], textarea[name=default_value_value]").focus();
				$('select[name=default_value_attribute], input[name=default_value_request]').val("").attr('disabled', true).next('.note, .note_attribute').slideUp();
			}			
			$("div[id=default_value_type_request], div.default_value_type_attribute").slideUp();
					
		} else if ($('select[name=default_value_type]').val() == 'request') {
			
			$("div[id=default_value_type_request]").slideDown().find('input[name=default_value_request]').attr('disabled', false).next('.note, .note_attribute').slideDown();

			if ($('input[name=default_value_request]').attr('data-mask'))
				$('input[id=default_value_request]').mask($('input[name=default_value_request]').attr('data-mask'));

			if ((s && s.attr('name') == 'default_value_type') || (s && s.attr('name') == 'default_value')) {
				$("input[name=default_value_request]").focus();
				$('select[name=default_value_attribute], input[name=default_value_value], textarea[name=default_value_value]').val("").attr('disabled', true).next('.note, .note_attribute').slideUp();
			}			
			$("div[id=default_value_type_value], div.default_value_type_attribute").slideUp();
					
		} else {
				
			var selected = $('select[name=default_value_type]').val();
				
			$("div[id=default_value_type_"+selected+"]").slideDown().find('select[name=default_value_attribute]').attr('disabled', false).next('.note, .note_attribute').slideDown();

			if ((s && s.attr('name') == 'default_value_type') || (s && s.attr('name') == 'default_value')) {
				$('select[name=default_value_attribute]').focus();
				$("input[name=default_value_value], textarea[name=default_value_value]").val("").attr('disabled', true).next('.note, .note_attribute').slideUp();
			}		
			$("div[id=default_value_type_value], div[id=default_value_type_request], div.default_value_type_attribute:not([id=default_value_type_"+selected+"])").slideUp();
		}
		
	} else {
		$('select[name=default_value_type]').attr('disabled', true);			
		$("div[id=default_value_type_value], div[id=default_value_type_request], div.default_value_type_attribute").slideUp();
		$("input[name=default_value_value], div[id=default_value_type_request], textarea[name=default_value_value], select[name=default_value_attribute]").val("").attr('disabled', true).next('.note, .note_attribute').slideUp();
	}
	
	/*
	if ($('input[name=default_value]').is(':checked')) {
		$('input[name=default_value_value], textarea[name=default_value_value]').attr('disabled', false).next('.note').slideDown();
		if ($('input[name=default_value_value]').attr('data-mask'))
				$('input[id=default_value_value]').mask($('input[name=default_value_value]').attr('data-mask'));
		if (s && s.attr('name') == 'default_value') {
			$("input[name=default_value_value], textarea[name=default_value_value]").focus();
			$('textarea[name=default_value_value]').animate({height: txt_max_height}, toggle_delay);
		}
	} else {
		if ($('input[name=default_value]'))
			$("input[name=default_value_value], textarea[name=default_value_value]").val("").attr('disabled', true).next('.note').slideUp();
		if (s && s.attr('name') == 'default_value') $('textarea[name=default_value_value]').animate({height: txt_min_height}, toggle_delay);
		else $('textarea[name=default_value_value]').height(txt_min_height);
	}
	*/
	
	var element = $("input[name=placeholder_value]");
	if ($('input[name=placeholder]').is(':checked')) {
		element.attr('disabled', false);
		if (s && s.attr('name') == 'placeholder') element.focus();
	} else 
		element.val("").attr('disabled', true);
	
	
	if ($('input[name=min_max]').is(':checked')) {
		$("input[name=min_value], input[name=max_value], select[name=min_max_type]").attr('disabled', false);
		if (s && s.attr('name') == 'min_max') $("input[name=min_value]").focus();
	} else {
		$("input[name=min_value], input[name=max_value]").val("").attr('disabled', true);
		$("select[name=min_max_type]").attr('disabled', true);
	}
	
	if ($.find("select[name=mask_format]").length == 1) {
		var element = $("select[name=mask_format]");
		if ($('input[name=mask]').is(':checked')) {
			element.attr('disabled', false).next('.note').slideDown();
			if (s && s.attr('name') == 'mask') element.focus().find('option:first').attr('selected', true);
		} else { 
			element.attr('disabled', true).val('').next('.note').slideUp();
			element.find("option:selected").removeAttr("selected");
		}
	} else if ($.find("input[name=mask_format]").length == 1) {
		var element = $("input[name=mask_format]");
		if ($('input[name=mask]').is(':checked')) {
			element.attr('disabled', false).next('.note').slideDown();
			if (s && s.attr('name') == 'mask') element.focus().val(element.attr('placeholder'));
		} else 
			element.attr('disabled', true).val('').next('.note').slideUp();
	}
	
	var element = $("select[id=chars_allowed_value]");
	if ($('input[name=chars_allowed]').is(':checked')) {
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'chars_allowed') element.focus().find('option:first').attr('selected', true);
	} else 
		element.attr('disabled', true).find("option:selected").removeAttr("selected").next('.note').slideUp();
	
	var element = $("textarea[name=tooltip_value]");
	if ($('input[name=tooltip]').is(':checked')) {
		element.attr('disabled', false);
		if (s && s.attr('name') == 'tooltip') element.focus().animate({height: txt_max_height}, toggle_delay);
	} else {
		element.val("").attr('disabled', true);
		if (s && s.attr('name') == 'tooltip') element.animate({height: txt_min_height}, toggle_delay);
		else element.height(txt_min_height);
	}
	
	if ($('input[name=option_other]').is(':checked')) {
		$("input[name=option_other_value]").attr('disabled', false);
		$("select[name=option_other_type]").attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'option_other') {
			$("input[name=option_other_value]").focus();
			$("select[name=option_other_type]").find('option:first').attr('selected', true);
		}
	} else {
		$("input[name=option_other_value]").attr('disabled', true);
		$("select[name=option_other_type]").attr('disabled', true).val('').next('.note').slideUp();
	}
	
	
	if ($.find('input[name=multiple]').length > 0) {
		if ($('input[name=multiple]').is(':checked')) {
			$('input[name=multiple]').parent().next('.note').slideDown();
			$('.element_options').find('input[type=radio]').each(function() {
				var label = $(this).parent('label');
				$(this).detach().attr('type', 'checkbox').appendTo(label);
			});
			if (s && s.attr('name') == 'multiple') {
				if ($.find('input[name=min_max]').length > 0) {
					$('input[name=min_max]').parents('.clearfix').slideDown();	
				}
			}
		} else {
			$('input[name=multiple]').parent().next('.note').slideUp();
			$('.element_options').find('input[type=checkbox]').each(function(i, element) {
				var label = $(this).parent('label');
				$(this).detach().attr('type', 'radio').appendTo(label);
			});
			if ($.find('input[name=min_max]').length > 0) {
				$('input[name=min_max]').attr('checked', false).parents('.clearfix').slideUp();
				$("input[name=min_value], input[name=max_value]").val("").attr('disabled', true);
				$("select[name=min_max_type]").attr('disabled', true);		
			}
		}
	}
	
	var element = $("textarea[name=allowed_extensions_value]");
	if ($('input[name=allowed_extensions]').is(':checked')) {
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'allowed_extensions') element.focus().val(element.attr('placeholder'));
	} else
		element.val("").attr('disabled', true).next('.note').slideUp();
		
	var element = $("select[name=fileset_value]");
	if ($('input[name=fileset]').is(':checked')) {
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'fileset') element.focus().find('option:first').attr('selected', true);
	} else 
		element.attr('disabled', true).val('').next('.note').slideUp();
	
	var element = $("textarea[name=advanced_value]");
	if ($('input[name=advanced]').is(':checked')) {
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'advanced') element.focus().animate({height: txt_max_height}, toggle_delay);
	} else {
		element.val("").attr('disabled', true).next('.note').slideUp();
		if (s && s.attr('name') == 'advanced') element.animate({height: txt_min_height}, toggle_delay);
		else element.height(txt_min_height);
	}
	
	if ($('select[name=format]').val() == 'other') {
		$("input[name=format_other]").attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'format') $("input[name=format]").focus();
	} else 
		$("input[name=format_other]").attr('disabled', true).val('').next('.note').slideUp();	
	
	
	if ($('select[name=appearance]').val() != 'picker' && $('select[name=appearance]').val() != 'slider') {
		if (s && s.attr('name') == 'appearance') {
			if ($.find('input[name=advanced]').length > 0) {
				$('input[name=advanced]').attr('checked', false).parents('.clearfix').slideUp();
				$("textarea[name=advanced_value]").val("").attr('disabled', true);
			}
		}
		else
		{
			$("textarea[name=advanced_value]").attr('disabled', true).val('').next('.note').slideUp();
			$('input[name=advanced]').attr('checked', false).parents('.clearfix').slideUp();				
		}
	} else {
		if ($.find('input[name=advanced]').length > 0) {
			$('input[name=advanced]').parents('.clearfix').slideDown();	
		}
	}
	
	var element = $("input[name=css_value]");
	if ($('input[name=css]').is(':checked')) {
		element.attr('disabled', false).next('.note').slideDown();
		if (s && s.attr('name') == 'default') element.focus();
	} else 
		element.attr('disabled', true).val('').next('.note').slideUp();
		
	if ($('input[name=submission_update]').is(':checked')) {		
		$('select[name=submission_update_type], input[name=submission_update_empty]').attr('disabled', false);			
		$("div.submission_update").slideDown();
		
		var selected = $('select[name=submission_update_type]').val();			
		$("div[id=submission_update_type_"+selected+"]").slideDown().find('select[name=submission_update_attribute]').attr('disabled', false);
		
		if ((s && s.attr('name') == 'submission_update_type') || (s && s.attr('name') == 'submission_update')) {
			$('select[name=submission_update_attribute]').focus();							
		}
		$("div.submission_update_type_attribute:not([id=submission_update_type_"+selected+"])").slideUp( function() { $(this).css('display','none') } );
	
	} else {			
		$("div.submission_update_type_attribute, div.submission_update").slideUp( function() { $(this).css('display','none') } );
		$("select[name=submission_update_type], select[name=submission_update_attribute], input[name=submission_update_empty]").val("").attr('disabled', true);
	}
}

ccmFormidableFormElementAddOptions = function(s) {
	if (s instanceof jQuery) {} else { s = $(s); }
	
	var element_options = s.parents('.element_options');
	option_counter++;
	
	var element_type = 'radio';
	var second_text = false;
	switch ($('input[name=element_type]').val()) {
		case 'checkbox': 
			element_type = 'checkbox';
		break;
		case 'select': 
			if ($('input[name=multiple]').is(':checked')) 
				element_type = 'checkbox';
		break;
		case 'recipientselector': 
			if ($('input[name=multiple]').is(':checked')) 
				element_type = 'checkbox';
			second_text = true;	
		break;
	}
	
	var option = '';
	option += '<label class="add-on-formidable">';
	option += '<input type="'+element_type+'" name="options_selected[]" value="'+option_counter+'" class="option_default ccm-input-radio">';
	option += '</label>';
	if (second_text)
	{
		option += '<input type="text" name="options_name['+option_counter+']" value="" style="width: 200px; float:left; margin-right: 7px;" placeholder="'+placeholder_name+'" class="ccm-input-text">';
		option += '<input type="text" name="options_value['+option_counter+']" value="" style="width: 200px; float:left;" placeholder="'+placeholder_email+'" class="ccm-input-text">';
	}
	else
	{
		option += '<input type="text" name="options_name['+option_counter+']" value="" style="width: 417px; float:left;" placeholder="'+placeholder_option+'" class="ccm-input-text">';
	}
	option += '<a href="javascript:;" onclick="ccmFormidableFormElementAddOptions(this);" class="btn success option_button">+</a> ';
	option += '<a href="javascript:;" onclick="ccmFormidableFormElementRemoveOptions(this);" class="btn error option_button">-</a>';
	var new_option = $('<div>').addClass('input option_row')
							   .append($('<div>').addClass('input_prepend')
												 .html(option));
	s.parents('.input').after(new_option);
	
	$("input.option_default").mousedown(function() {
		$(this).data('wasChecked', this.checked);
	});
	$("input.option_default").click(function() {
		if ($(this).data('wasChecked'))
			this.checked = false;
	});	
	element_options.find('.error').attr('disabled', false);	
}

ccmFormidableFormElementRemoveOptions = function(s) {
	if (s instanceof jQuery) {} else { s = $(s); }
	var element_options = s.parents('.element_options');
	if (element_options.children('.input').length > 1)
		s.parents('.input').remove();
	
	if (element_options.find('.input').length == 1)
		element_options.find('.error').attr('disabled', true);	
}


ccmFormidableLoadElements = function(msg) {
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
			$('#ccm-element-list div').remove();
			if (html) {
				$('#ccm-element-list').append(html);
				$('#ccm-element-list div.element_row_wrapper:not(.hide)').fadeIn();
				$('div.placeholder, div.loader').fadeOut();
				ccmFormidableCreateMenu();
			}
			else 
				ccmFormidableLoadPlaceholder(true);
		}
	});	
}

ccmFormidableOpenElementDialog = function(element_type, element_text, layout_id, elementID) {
	jQuery.fn.dialog.closeTop();
	var query_string = "element_type="+element_type+"&element_text="+element_text+"&layoutID="+layout_id+"&formID="+formID;
	if (element_type == 'line' || element_type == 'hr') {
		ccmFormidableSaveElement(query_string+"&label="+element_text);
	} else {
		if (parseInt(elementID)!=0 && elementID!=undefined) query_string += "&elementID="+elementID;
		jQuery.fn.dialog.open({ 
			width: 670,
			height: 600,
			modal: true,
			href: dialog_url+"?"+query_string,
			title: (parseInt(elementID)!=0 && elementID!=undefined)?element_message_edit:element_message_add		
		});
	}
}

ccmFormidableAddElementToForm = function() {
	var data = $('#elementForm').serialize();
	ccmFormidableSaveElement(data);
	jQuery.fn.dialog.closeTop();
}

ccmFormidableCheckFormElementSubmit = function() {	
	var errors = [];	
	$('#dependencies_rules [disabled]').attr('disabled', false);
	var data = $('#elementForm').serialize();	
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
			} else ccmFormidableAddElementToForm();
		}
	});	
}

ccmFormidableSaveElement = function(data) {
	data += '&action=save';
	ccmFormidableActionElement('save', data);
}

ccmFormidableDuplicateElement = function(elementID) {
	data = 'action=duplicate&elementID='+elementID;
	ccmFormidableActionElement('duplicate', data);
}

ccmFormidableDeleteElement = function(elementID) {
	data = 'action=delete&elementID='+elementID;
	ccmFormidableActionElement('delete', data);
}

ccmFormidableActionElement = function(action, data) {
	$.ajax({ 
		type: "POST",
		url: tools_url,
		data: data,
		dataType: 'json',
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
		},
		success: function(ret) {
			ccmFormidableLoadElements(action);
		}
	});		
}

ccmFormidableAddDependency = function(elementID, rule) {	
	var objDep = $('#dependencies_rules');
	if (rule === undefined) rule = parseInt(objDep.attr('data-next_rule'));
	$.ajax({ 
		type: "POST",
		url: tools_url,
		data: 'action=add_dependency&elementID='+elementID+'&rule='+rule,
		dataType: 'html',
		beforeSend: function () {
			//console.log('loading');
		},
		success: function(ret) {
			objDep.append(ret).attr('data-next_rule', rule+1);
			ccmFormidableInitDependency(elementID, rule);
		}
	});		
}

ccmFormidableInitDependency = function(elementID, rule) {
		
	var objRule = $('div#dependency_rule_'+rule);
		
	objRule.find('div.dependency_elements, div.operator').hide();
	
	if ($('div#dependencies_rules').children().length > 1 && rule > 0) {
		objRule.find('div.operator').show();
		//objRule.find('div.dependency_actions').hide();
	}
	
	var rules = objRule.find('div.dependency_actions').children().length;
	if (rules < 1)
		ccmFormidableAddDependencyAction(elementID, rule);

	$('div#dependencies_rules').find('div.dependency').each(function(i, row) {
		$(row).find('span.rule').text(i + 1);
	});
}

ccmFormidableDeleteDependency = function(rule) {
	var objRule = $('div#dependency_rule_'+rule);	
	if (confirm(dependency_confirm)) {
		objRule.remove();
			 
		$('div#dependencies_rules').find('div.dependency').each(function(i, row) {
			$(row).find('span.rule').text(i + 1);
			if (i == 0)
				$(row).find('div.operator').hide();
		});
	}		
}

ccmFormidableAddDependencyAction = function(elementID, dependency_rule, rule) {	
	var query = '';
	var objDep = $('div#dependency_rule_'+dependency_rule+' div.dependency_actions');
	if (rule === undefined) rule = parseInt(objDep.attr('data-next_rule'));
	$.ajax({ 
		type: "POST",
		url: tools_url,
		data: 'action=add_dependency_action&elementID='+elementID+'&dependency_rule='+dependency_rule+'&rule='+rule,
		dataType: 'html',
		beforeSend: function () {
			//console.log('loading');
		},
		success: function(ret) {
			objDep.append(ret).attr('data-next_rule', rule+1);
			ccmFormidableInitDependencyAction(elementID, dependency_rule, rule);
		}
	});		
}

ccmFormidableDeleteDependencyAction = function(dependency_rule, rule) {
	if ($('div#dependency_rule_'+dependency_rule+' div.dependency_actions').children().length == 1)
		return false;
		
	var objRule = $('div#dependency_rule_'+dependency_rule+' div#action_'+rule);
	objRule.remove();
	
	if ($('div#dependency_rule_'+dependency_rule+' div.dependency_actions').children().length == 1)
		$('div#dependency_rule_'+dependency_rule+' div.dependency_actions a.error').attr('disabled', true);
	
	objNext = $('div#dependency_rule_'+dependency_rule+' div.dependency_actions').children(':first');
	objNext.find('span.action_label').hide();
	objNext.find('input.action_value').width(280);
	objNext.find('select.action_select').width(290);			
}	

ccmFormidableInitDependencyAction = function(elementID, dependency_rule, rule) {
		
	var objRule = $('div#dependency_rule_'+dependency_rule+' div#action_'+rule);
	
	objRule.find('span.action_label').hide();
	
	var action = objRule.find('select.action');
	var action_value = objRule.find('input.action_value');
	var action_value_select = objRule.find('select.action_select');

	if (objRule.parents('div.dependency_actions').children().length > 1 && rule > 0) {
		objRule.find('span.action_label').show();
		action_value.width(254);
		action_value_select.width(254);
	}			
	
	action_value.hide();
	action_value_select.hide();
	
	if (action.val() != '') {
		$('div#dependency_rule_'+dependency_rule+' div.dependency_elements').show();
		if (action.val() == 'class' || action.val() == 'placeholder')
			action_value.show();
			
		if (action.val() == 'value') {
			if (action_value_select.find('option').length > 0)
				action_value_select.show();			
			else
				action_value.show();
		}
	}
	
	action.on('change', function() {
		action.val(action.val());
		action_value.hide();
		action_value_select.hide();
		if (action.val() != '') {
			objRule.parents('div.dependency').find('div.dependency_elements').show();
			if (objRule.parents('div.dependency').find('div.dependency_elements').children().length < 1)
				ccmFormidableAddDependencyElement(elementID, dependency_rule);
		}
		if (action.val() == 'class')
			action_value.val('').attr('placeholder', dependency_action_placeholder_class).show();
		if (action.val() == 'placeholder')
			action_value.val('').attr('placeholder', dependency_action_placeholder_placeholder).show();
		if (action.val() == 'value') {
			if (action.val() == 'value') {
				if (action_value_select.find('option').length > 1)
					action_value_select.show();			
				else
					action_value.val('').attr('placeholder', dependency_action_placeholder_value).show();		
			}
		}
		if (action.val() == '') 
			objRule.parents('div.dependency').find('div.dependency_elements').hide();
	});
	
	$('div#dependency_rule_'+dependency_rule+' div.dependency_actions a.error').attr('disabled', true);
	if ($('div#dependency_rule_'+dependency_rule+' div.dependency_actions').children().length > 1)
		$('div#dependency_rule_'+dependency_rule+' div.dependency_actions a.error').attr('disabled', false);
}


ccmFormidableAddDependencyElement = function(elementID, dependency_rule, rule) {	
	var query = '';
	var objDep = $('div#dependency_rule_'+dependency_rule+' div.dependency_elements');
	if (rule === undefined) rule = parseInt(objDep.attr('data-next_rule'));
	$.ajax({ 
		type: "POST",
		url: tools_url,
		data: 'action=add_dependency_element&elementID='+elementID+'&dependency_rule='+dependency_rule+'&rule='+rule,
		dataType: 'html',
		beforeSend: function () {
			//console.log('loading');
		},
		success: function(ret) {
			objDep.append(ret).attr('data-next_rule', rule+1);
			ccmFormidableInitDependencyElement(elementID, dependency_rule, rule);
		}
	});		
}

ccmFormidableDeleteDependencyElement = function(dependency_rule, rule) {
	if ($('div#dependency_rule_'+dependency_rule+' div.dependency_elements').children().length == 1)
		return false;
	
	var objRule = $('div#dependency_rule_'+dependency_rule+' div#element_'+rule);	
	objRule.remove();	
	
	if ($('div#dependency_rule_'+dependency_rule+' div.dependency_elements').children().length == 1)
		$('div#dependency_rule_'+dependency_rule+' div.dependency_elements a.error').attr('disabled', true);
		
	objNext = $('div#dependency_rule_'+dependency_rule+' div.dependency_elements').children(':first');
	objNext.find('span.element_label').hide();
	objNext.find('select.element').width(433);	
}	

ccmFormidableInitDependencyElement = function(elementID, dependency_rule, rule) {
		
	var objRule = $('div#dependency_rule_'+dependency_rule+' div#element_'+rule);
		
	objRule.find('div.element_value, div.condition, span.element_label').hide();	
	
	var element_select = objRule.find('select.element');
	var element_value_select = objRule.find('select.element_value');
	var condition_select = objRule.find('select.condition');
	var condition_value = objRule.find('input.condition_value');
	
	if (objRule.parents('div.dependency_elements').children().length > 1 && rule > 0) {
		objRule.find('span.element_label').show();
		element_select.width(408);
	}
		
	var element_select = objRule.find('select.element');
	var element_value_select = objRule.find('select.element_value');
	var condition_select = objRule.find('select.condition');
	var condition_value = objRule.find('input.condition_value');
	
	if (element_select.val() != '') {
		objRule.find('div.element_value, div.condition').hide();
		if (element_value_select.find('option').length > 0)
			objRule.find('div.element_value').show();
		else {
			element_value_select.append($('<option>').val('').text('').attr('selected', 'selected'));
			objRule.find('div.condition').show();
		}
	}
	
	element_select.on('change', function() {
		objRule.find('div.element_value, div.condition').hide();
		if (element_select.val() != '') {
			$.ajax({ 
				type: "POST",
				url: tools_url,
				data: 'action=dependency_load_element&elementID='+element_select.val(),
				dataType: 'json',
				beforeSend: function () {
					//console.log('loading');
				},
				success: function(ret) {
					element_value_select.find('option').remove();
					condition_select.find('option:gt(1)').remove();	
					if (ret.length > 1) {
						for( var i=0; i<dependency_values.length; i++) {
							element_value_select.append($('<option>').val(dependency_values[i][0]).text(dependency_values[i][1]));
						}
						for( var i=0; i<ret.length; i++) {
							element_value_select.append($('<option>').val(ret[i]['value']).text(ret[i]['name']));
						}
						objRule.find('div.element_value').show();
					} else { 
						//element_value_select.append($('<option>').val('').text('').attr('selected', 'selected'));	
						for( var i=0; i<condition_values.length; i++) {
							condition_select.append($('<option>').val(condition_values[i][0]).text(condition_values[i][1]));
						}						
						objRule.find('div.condition').show();
					}
				}
			});			
		}
		
	});
	
	condition_value.hide();	
	if (condition_select.val() != 'enabled' && condition_select.val() != 'disabled' &&
		condition_select.val() != 'empty' && condition_select.val() != 'not_empty')
		condition_value.show();
	
	condition_select.on('change', function() {
		condition_value.hide();
		if (condition_select.val() != 'enabled' && condition_select.val() != 'disabled' &&
			condition_select.val() != 'empty' && condition_select.val() != 'not_empty')
			condition_value.show().val('').attr('placeholder', dependency_condition_placeholder)	
	});
	
	$('div#dependency_rule_'+dependency_rule+' div.dependency_elements a.error').attr('disabled', true);
	if ($('div#dependency_rule_'+dependency_rule+' div.dependency_elements').children().length > 1)
		$('div#dependency_rule_'+dependency_rule+' div.dependency_elements a.error').attr('disabled', false);
}


$(function() {
(function(a){var b=(a.browser.msie?"paste":"input")+".mask",c=window.orientation!=undefined;a.mask={definitions:{9:"[0-9]",a:"[A-Za-z]","*":"[A-Za-z0-9]"},dataName:"rawMaskFn"},a.fn.extend({caret:function(a,b){if(this.length!=0){if(typeof a=="number"){b=typeof b=="number"?b:a;return this.each(function(){if(this.setSelectionRange)this.setSelectionRange(a,b);else if(this.createTextRange){var c=this.createTextRange();c.collapse(!0),c.moveEnd("character",b),c.moveStart("character",a),c.select()}})}if(this[0].setSelectionRange)a=this[0].selectionStart,b=this[0].selectionEnd;else if(document.selection&&document.selection.createRange){var c=document.selection.createRange();a=0-c.duplicate().moveStart("character",-1e5),b=a+c.text.length}return{begin:a,end:b}}},unmask:function(){return this.trigger("unmask")},mask:function(d,e){if(!d&&this.length>0){var f=a(this[0]);return f.data(a.mask.dataName)()}e=a.extend({placeholder:"_",completed:null},e);var g=a.mask.definitions,h=[],i=d.length,j=null,k=d.length;a.each(d.split(""),function(a,b){b=="?"?(k--,i=a):g[b]?(h.push(new RegExp(g[b])),j==null&&(j=h.length-1)):h.push(null)});return this.trigger("unmask").each(function(){function v(a){var b=f.val(),c=-1;for(var d=0,g=0;d<k;d++)if(h[d]){l[d]=e.placeholder;while(g++<b.length){var m=b.charAt(g-1);if(h[d].test(m)){l[d]=m,c=d;break}}if(g>b.length)break}else l[d]==b.charAt(g)&&d!=i&&(g++,c=d);if(!a&&c+1<i)f.val(""),t(0,k);else if(a||c+1>=i)u(),a||f.val(f.val().substring(0,c+1));return i?d:j}function u(){return f.val(l.join("")).val()}function t(a,b){for(var c=a;c<b&&c<k;c++)h[c]&&(l[c]=e.placeholder)}function s(a){var b=a.which,c=f.caret();if(a.ctrlKey||a.altKey||a.metaKey||b<32)return!0;if(b){c.end-c.begin!=0&&(t(c.begin,c.end),p(c.begin,c.end-1));var d=n(c.begin-1);if(d<k){var g=String.fromCharCode(b);if(h[d].test(g)){q(d),l[d]=g,u();var i=n(d);f.caret(i),e.completed&&i>=k&&e.completed.call(f)}}return!1}}function r(a){var b=a.which;if(b==8||b==46||c&&b==127){var d=f.caret(),e=d.begin,g=d.end;g-e==0&&(e=b!=46?o(e):g=n(e-1),g=b==46?n(g):g),t(e,g),p(e,g-1);return!1}if(b==27){f.val(m),f.caret(0,v());return!1}}function q(a){for(var b=a,c=e.placeholder;b<k;b++)if(h[b]){var d=n(b),f=l[b];l[b]=c;if(d<k&&h[d].test(f))c=f;else break}}function p(a,b){if(!(a<0)){for(var c=a,d=n(b);c<k;c++)if(h[c]){if(d<k&&h[c].test(l[d]))l[c]=l[d],l[d]=e.placeholder;else break;d=n(d)}u(),f.caret(Math.max(j,a))}}function o(a){while(--a>=0&&!h[a]);return a}function n(a){while(++a<=k&&!h[a]);return a}var f=a(this),l=a.map(d.split(""),function(a,b){if(a!="?")return g[a]?e.placeholder:a}),m=f.val();f.data(a.mask.dataName,function(){return a.map(l,function(a,b){return h[b]&&a!=e.placeholder?a:null}).join("")}),f.attr("readonly")||f.one("unmask",function(){f.unbind(".mask").removeData(a.mask.dataName)}).bind("focus.mask",function(){m=f.val();var b=v();u();var c=function(){b==d.length?f.caret(0,b):f.caret(b)};(a.browser.msie?c:function(){setTimeout(c,0)})()}).bind("blur.mask",function(){v(),f.val()!=m&&f.change()}).bind("keydown.mask",r).bind("keypress.mask",s).bind(b,function(){setTimeout(function(){f.caret(v(!0))},0)}),v()})}})})(jQuery); });