var ccmFormidableAddressStates = '';
var ccmFormidableUploaders = [];

var I18N_FF = {}

function ccm_t_ff(s, v) {
	return I18N_FF && I18N_FF[s] ? (I18N_FF[s].replace('%s', v) || s) : s;
}

ccmFormidableTagsCounter = function(fObj) {
	if (fObj) {	
		var cObj = $('span[id='+fObj.attr('id')+'_count]');	
		if (cObj) {			
			var current_tags = parseInt(fObj.next('.tagsinput').find('span.tag').length);
			var max_tags = parseInt(cObj.parent('.counter').attr('max'));	
			cObj.text(max_tags - current_tags);			
		}
	}
}

ccmFormidableAddressSelectCountry = function(cls, country) {
	var ss = $('select[id="' + cls + '[province]"]');
	var si = $('input[id="' + cls + '[province]"]');
	
	var foundStateList = false;
	ss.html("");
	if (ccmFormidableAddressStates) {
		for (j = 0; j < ccmFormidableAddressStates.length; j++) {
			var sa = ccmFormidableAddressStates[j].split(':');
			
			if (jQuery.trim(sa[0]) == country) {
				if (!foundStateList) {
					foundStateList = true;
					si.attr('name', 'inactive_' + si.attr('ccm-attribute-address-field-name'));
					si.hide();
					ss.append('<option value="">'+ccm_t_ff('Choose State/Province')+'</option>');
				}
				ss.show();
				ss.attr('name', si.attr('ccm-attribute-address-field-name'));		
				ss.append('<option value="' + jQuery.trim(sa[1]) + '">' + jQuery.trim(sa[2]) + '</option>');
			}
		}
		if (ss.attr('ccm-passed-value') != '') {
			$(function() {
				ss.find('option[value=' + ss.attr('ccm-passed-value') + ']').attr('selected', true);
			});
		}
	}
	if (!foundStateList) {
		ss.attr('name', 'inactive_' + si.attr('ccm-attribute-address-field-name'));
		ss.hide();
		si.show();
		si.attr('name', si.attr('ccm-attribute-address-field-name'));		
	}
}

ccmFormidableDependencyChange = function(selector, arguments) {
	
	//if (ccmFormidableDependencyFirstLoad == false) {
	//	ccmFormidableDependencyFirstLoad = true;
	//	return false;
	//}
	
	var eObj = $('[name="'+selector+'"]');
	if (!eObj.length)
		eObj = $('[name^="'+selector+'["]');	
	if (!eObj.length)
		eObj = $('[id="'+selector+'"]');
			
	if (eObj.length <= 0)
		return false;
	
	var tagName = eObj.get(0).tagName.toLowerCase();	
	var typeName = eObj.attr('type');
	
	for (var i=0; i<arguments.length; i++) {
		switch (arguments[i][0]) {			
			
			case 'disable':			
				eObj.attr('disabled', true);
			break;
			
			case 'enable':			
				eObj.attr('disabled', false);
			break;
			
			case 'show':			
				if (eObj.closest('.element').length > 0)
					eObj.closest('.element').show();
				else
					eObj.show();	
			break;
				
			case 'hide':			
				if (eObj.closest('.element').length > 0)
					eObj.closest('.element').hide();
				else
					eObj.hide();
					
			break;
			
			case 'value':		
				if (tagName == 'input' || tagName == 'textarea' || tagName == 'select') {
					if (typeName == 'checkbox' || typeName == 'radio') {
						var _argument = arguments[i][1];
						eObj.each(function(j, eObjItem) {							
							if ($(eObjItem).val() == _argument)
								$(eObjItem).attr('checked', false).trigger('click');
								
						});
					} else {
						eObj.val(arguments[i][1]).trigger('change');
					}
				}
			break;
			
			case 'class':
				eObj.removeClass(arguments[i][1]);
				if (arguments[i][2] == 'add')			
					eObj.addClass(arguments[i][1]);
			break;	
		}
	}		
}

ccmFormidableAddressSetupStateProvinceSelector = function(cls) {	
	var cs = $('select[id="' + cls + '[country]"]');
	cs.change(function() {
		var v = $(this).val();
		ccmFormidableAddressSelectCountry(cls, v);
	});
	if (cs.attr('ccm-passed-value') != '') {
		$(function() {
			cs.find('option[value=' + cs.attr('ccm-passed-value') + ']').attr('selected', true);
			ccmFormidableAddressSelectCountry(cls, cs.attr('ccm-passed-value'));
			var ss = $('select[id="' + cls + '[province]"]');
			if (ss.attr('ccm-passed-value') != '') {
				ss.find('option[value=' + ss.attr('ccm-passed-value') + ']').attr('selected', true);
			}
		});
	}	
	ccmFormidableAddressSelectCountry(cls, '');
}

ccm_activateEditableProperties = function() {
	$("tr.ccm-attribute-editable-field").each(function() {
		var trow = $(this);
		$(this).find('a').click(function() {
			trow.find('.ccm-attribute-editable-field-text').hide();
			trow.find('.ccm-attribute-editable-field-clear-button').hide();
			trow.find('.ccm-attribute-editable-field-form').show();
			trow.find('.ccm-attribute-editable-field-save-button').show();
		});
		
		trow.find('form').submit(function() {
			ccm_submitEditableProperty(trow);
			return false;
		});
		
		trow.find('.ccm-attribute-editable-field-save-button').parent().click(function() {
			trow.find('form input[name=action]').val('update_result');
			ccm_submitEditableProperty(trow);
		});

		trow.find('.ccm-attribute-editable-field-clear-button').parent().unbind();
		trow.find('.ccm-attribute-editable-field-clear-button').parent().click(function() {
			if (confirm(confirm_clear)) {
				trow.find('form input[name=action]').val('clear_result');
				ccm_submitEditableProperty(trow);
				return false;
			}
		});

	});
}

ccm_submitEditableProperty = function(trow) {
	trow.find('.ccm-attribute-editable-field-save-button').hide();
	trow.find('.ccm-attribute-editable-field-clear-button').hide();
	trow.find('.ccm-attribute-editable-field-loading').show();
	try {
		tinyMCE.triggerSave(true, true);
	} catch(e) { }
	
	$.ajax({
		type: "POST",
		url: trow.find('form').attr('action'),
		data: trow.find('form').serialize(),
		dataType: 'json',					
		success: function(resp) {
			
			trow.find('.ccm-attribute-editable-field-loading').hide();
			trow.find('.ccm-attribute-editable-field-save-button').show();
			if (resp.errors && resp.errors.length > 0) {
				trow.find('.ccm-attribute-editable-field-error').html(resp.errors).show();
			} else {
				trow.find('.ccm-attribute-editable-field-error').html('').hide();
				trow.find('.ccm-attribute-editable-field-text').html(resp.success);
				trow.find('.ccm-attribute-editable-field-form').hide();
				trow.find('.ccm-attribute-editable-field-save-button').hide();
				trow.find('.ccm-attribute-editable-field-text').show();
				trow.find('.ccm-attribute-editable-field-clear-button').show();
				ccm_reloadSearchResults();
			}
		}
	});
}

ccm_reloadSearchResults = function() {
	if ($("#ccm-results-search-results") && $('#currentURL').val().length > 0) {
		$.ajax({ 
			type: "GET",
			url: $('#currentURL').val(),					
			success: function(data) {
				$('#ccm-results-search-results').replaceWith(data);
				ccm_activateSearchResults('results');
        	}
		});
    }
}

$(function() {
		
	$(".upload_preview").hover(
		function() {
			var thumb = $(this).find(".upload_preview-hover");
			if(thumb.length > 0) {
				var img = thumb.find("div");
				var pos = thumb.position();
				img.css("top",pos.top);
				img.css("left",pos.left);
				img.show();
			}
		},
		function() {
			var thumb = $(this).find(".upload_preview-hover");
			var img = thumb.find("div");
			img.hide();
		}
	);
	ccm_activateEditableProperties();

	$('.counter').closest('div.ccm-attribute-editable-field-form').find('input, textarea').each(function(){
		if ($(this).hasClass('counter_disabled')) 
			$(this).closest('div.ccm-attribute-editable-field-form').find('.counter').parent().remove();
	    else {
			var counter = $('#'+$(this).attr('id')+'_count');
			var type = $('#'+$(this).attr('id')+'_counter').attr('type');
			var max = $('#'+$(this).attr('id')+'_counter').attr('max');		
			if(type == 'value')
				max = $('#'+$(this).attr('id')+'_counter').attr('max').length;			
			if (type == 'tags') {
				var current = $(this).next('div.tagsinput').find('span.tag').length;
				counter.text(max - current);				
			} else if (type == 'files') {
				var current = $(this).closest('fieldset').find('ul.ax-file-list li').length;
				counter.text(max - current);
			} else {
				$(this).simplyCountable({
					counter: counter,
					countType: type,
					maxCount: max,
					strictMax: true
				});	
			}
		}
	});
		
	$('select').find('option[value="option_other"]:selected').each(function() { 
		$(this).closest('div.ccm-attribute-editable-field-form').find('div.option_other').slideDown(); 
	});
	$('select').change(function() {
		if ($(this).find('option[value="option_other"]:selected').length > 0) 
			$(this).closest('div.ccm-attribute-editable-field-form').find('div.option_other').slideDown();
		else 
			$(this).closest('div.ccm-attribute-editable-field-form').find('div.option_other').slideUp();
	});
	
	$('input[type="range"]').change(function() {
		$(this).closest('div.ccm-attribute-editable-field-form').find('input[type=hidden]').val($(this).val());
		$(this).closest('div.ccm-attribute-editable-field-form').find('span:not(.no_counter)').text($(this).val());
		$(this).removeClass('error').closest('.element').find('div.error').fadeOut();
	});
	
	$('input[value="option_other"]:checked').each(function() { 
		$(this).closest('div.ccm-attribute-editable-field-form').find('div.option_other').slideDown(); 
	});							
	$('input[type=radio]').click(function() {
		if ($(this).val() == 'option_other') 
			$(this).closest('div.ccm-attribute-editable-field-form').find('div.option_other').slideDown();
		else 
			$(this).closest('div.ccm-attribute-editable-field-form').find('div.option_other').slideUp();
	});
	
	$('input[type="checkbox"]').click(function() {
		var closed = true;
		$(this).closest('div.ccm-attribute-editable-field-form').find('input[type="checkbox"]:checked').each(function() {
			if ($(this).val() == 'option_other') 
				closed = false;
		});
		if (!closed) 
			$(this).closest('div.ccm-attribute-editable-field-form').find('div.option_other').slideDown();
		else 
			$(this).closest('div.ccm-attribute-editable-field-form').find('div.option_other').slideUp();
	});
	
	$('input[type="radio"].stars').rating();
						   						
	$('input[type="radio"]').bind('click', function(){
		$('input[name="' + $(this).attr('name') + '"]').not($(this)).trigger('deselect');
	});
	
	$('.element-body').parent('.ui-dialog-content').addClass('formidable-dialog-content');
});