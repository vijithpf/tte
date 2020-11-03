// JavaScript Document
function ccm_t_ff(s, v) {
	return I18N_FF && I18N_FF[s] ? (I18N_FF[s].replace('%s', v) || s) : s;
}

ccmFormidableLoadPlaceholder = function(show) {	
	if (show) { 
		$('div.placeholder').show(); 
		$('div.loader').hide(); 
	} 
	else
		$('div.placeholder').hide();
}
ccmFormidableLoadLoader = function(show) {	
	if (show)
		$('div.loader').show(); 
	else 
		$('div.loader').hide();
}

ccmFormidableSetMessage = function(type, message) {
	$('.ccm-dashboard-page-container').find('div.message').remove();
	$('.ccm-dashboard-page-container').find('div#ccm-dashboard-result-message').remove();
	$('<div>').addClass('message alert-message '+type).append(message).show().prependTo($('.ccm-dashboard-page-container .ccm-ui'));
}

ccmFormidableLoadMessage = function(msg) {
	
	if (msg == 'save') message = message_save;
	if (msg == 'duplicate') message = message_duplicate;
	if (msg == 'delete') message = message_delete;
	if (msg == 'save_layout') message = message_save_layout;
	if (msg == 'delete_layout') message = message_delete_layout;
	
	var message_button = $('<button>').attr({'type':'button', 'data-dismiss':'alert'}).addClass('close').text('x');
	var message_box = $('<div>').addClass('alert alert-info').append(message_button, message);
	
	var box = $('div[id=ccm-dashboard-result-message]');
	if (box.length > 0) box.fadeOut().empty();
	else var box = $('<div>').addClass('ccm-ui').attr('id', 'ccm-dashboard-result-message');			
	box.append($('<div>').addClass('row').append(message_box));
		
	$('div[id=ccm-dashboard-content] div.container').prepend(box);
	box.fadeIn(1000);	
}

//  Callback when all layouts and elements are loaded.
var ccmFormidableCreateMenu = function () {
	$('.show_menu').click(function(e) { 
		ccm_showMenu($(this), e); 
	});
}

// Show a cool menu
ccm_showMenu = function(obj, e) {
 	ccm_hideMenus();
	e.stopPropagation();
	ccm_menuActivated = true;
 
	// now, check to see if this menu has been made
	var aobj = document.getElementById("ccm-item-menu" + obj.attr("itemID"));
	if (!aobj) {
   		// create the 1st instance of the menu
		el = document.createElement("DIV");
		el.id = "ccm-item-menu" + obj.attr("itemID");
		el.className = "ccm-menu ccm-ui";
		el.style.display = "none";
		document.body.appendChild(el);
 		aobj = $("#ccm-item-menu" + obj.attr("itemID"));
		aobj.css("position", "absolute");
 
		// top wrapper &  menu items
		var html = '<div class="popover below"><div class="arrow"></div>';
		html += '<div class="inner"><div class="content"><ul>';
		$(obj.attr("target")).find('a, li').each(function(index) {
			var el =  $("<div />").append($(this).clone()).html();
		    html += '<li>'+ el +'</li>';
		});
		html += '</ul></div></div></div></div>';
		aobj.append(html);
 	} else {
		aobj = $("#ccm-item-menu" + obj.attr("itemID"));
	}
    ccm_fadeInMenu(aobj, e);
}