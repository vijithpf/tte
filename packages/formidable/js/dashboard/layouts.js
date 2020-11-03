// JavaScript Document

jQuery.expr[':'].contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

$(function() {
	$('input[id="quick_search_element"]').on('keydown, keyup', function() {
		var s = $(this).val();
		$(".searchable_elements li").show();
		if (s.length > 0)
			$(".searchable_elements li:not(:contains('"+s+"'))").hide();	
	});
	$("#ccm-pane-body-left li").click(function(){
		ccmFormidableOpenElementDialog($(this).attr('label'), $(this).text(), $(this).attr('data-layout'));
	});
});


ccmFormidableMoveLayout = function() {
	$('.f-row').addClass('moving');

	$(".f-row.moving").parent().sortable({
		items: "div.moving",
		handle: "div.overlay",
		sort: function(event, ui) {},
		stop: function(event, ui) {
			
			var list = 'action=sort&formID='+formID;
			$("div.f-row").each(function(i, row) {
				list += '&rows[]='+$(row).attr('data-id');
			});

			$.post(layout_tools_url, list, function(r) {});
			$('.f-row').removeClass('moving');
			ccmFormidableInitializeSortables()
		}
	});
} 

ccmFormidableMoveColumns = function(rowID) {
	$('#row_'+rowID+' .f-col').addClass('moving');

	$(".f-col.moving").parent().sortable({
		items: "div.moving",
		handle: "div.overlay",
		stop: function(event, ui) {
			
			var list = 'action=sort&rowID='+rowID+'&formID='+formID;
			$("div.f-col.moving").each(function(i, row) {
				list += '&cols[]='+$(row).attr('data-id');
			});

			$.post(layout_tools_url, list, function(r) {});
			$('.f-col').removeClass('moving');
			ccmFormidableInitializeSortables()
		}
	});
} 

ccmFormidableInitializeSortables = function () {
	$("#ccm-element-list").sortable({
		items: "div.element_row_wrapper",
		handle: ".mover",
		sort: function(event, ui) {
			$(this).removeClass( "ui-state-default" );
			
			ui.item.parents('.f-col').each(function() {
				var elnum = $('.element_row_wrapper:not(.element-empty)',this).length;
				if(elnum == 1) $('.element-empty', this).fadeIn();
				else $('.element-empty', this).hide();
			});
			
			$('.ui-sortable-placeholder').parents('.f-col').each(function() {
				var elnum = $('.element_row_wrapper:not(.element-empty)',this).length;
				if(elnum == 0) $('.element-empty', this).fadeIn();
				else $('.element-empty', this).hide();
			});
		},
		stop: function(event, ui) {
			var elemID = ui.item.attr('data-element_id');
			var newPos = ui.item.index();
			// Show or hide empty-elements
			$('.f-col').each(function() {
				var elnum = $('.element_row_wrapper:not(.element-empty)',this).length;
				if(elnum == 0) $('.element-empty', this).fadeIn();
				else $('.element-empty', this).hide();
			});
			
			var list = 'action=sort&formID='+formID;
			$("#ccm-element-list").find('.element_row_wrapper').each(function(i, row) {
				list += '&elements[]='+$(row).attr('data-element_id')+'&layout[]='+$(row).parent().parent().attr('data-id');
			});
			$.post(tools_url, list, function(r) {});
		}
	});
}

ccmFormidableOpenNewElementDialog = function (layout_id) {
	jQuery.fn.dialog.closeTop();
	$("#ccm-pane-body-left li").attr('data-layout', layout_id);
	$('#quick_search').val('');
	$(".searchable_elements li").show();
	jQuery.fn.dialog.open({ 
		width: 900, height: 340, modal: true, element: '#ccm-pane-body-left', title: element_message_add		
	});
}
ccmFormidableOpenLayoutDialog = function(layoutID, rowID) {
	jQuery.fn.dialog.closeTop();
	var query_string = "formID="+formID+"&rowID="+rowID+"&layoutID="+layoutID;
	jQuery.fn.dialog.open({
		width: 670, height: 230, modal: true, href: layout_url+"?"+query_string, 
		title: (rowID < 0 ? layout_message_add : layout_message_edit)		
	});
}
ccmFormidableCheckFormLayoutSubmit = function() {
	var data = $('#layoutForm').serialize();
	data += '&action=save&formID='+formID;
	$.ajax({ 
		type: "POST",
		url: layout_tools_url,
		data: data,
		dataType: 'json',
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
		},
		success: function(ret) {
			ccmFormidableLoadMessage('save_layout');
			ccmFormidableLoadElements();
			jQuery.fn.dialog.closeTop();
		}
	});	
}
ccmFormidableDeleteLayout = function(layoutID, rowID) {
	data = 'action=delete&layoutID='+layoutID+'&rowID='+rowID+'&formID='+formID;
	$.ajax({ 
		type: "POST",
		url: layout_tools_url,
		data: data,
		dataType: 'json',
		beforeSend: function () {
			ccmFormidableLoadLoader(true);
		},
		success: function(ret) {
			ccmFormidableLoadMessage('delete_layout');
			ccmFormidableLoadElements();
		}
	});	
}