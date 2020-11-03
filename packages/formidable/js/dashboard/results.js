// JavaScript Document
ccmFormidableSetupCheckboxes = function (e) {
    $("#ccm-submission-list-cb-all").unbind();
    $("#ccm-submission-list-cb-all").click(function () {
        if ($(this).prop("checked") == 1) {
            $("#ccm-results-list td.ccm-submission-list-cb input[type=checkbox]").attr("checked", !0);
            $("#ccm-submission-list-multiple-operations").attr("disabled", !1)
        } else {
            $("#ccm-results-list td.ccm-submission-list-cb input[type=checkbox]").attr("checked", !1);
            $("#ccm-submission-list-multiple-operations").attr("disabled", !0)
        }
    });
    $("#ccm-results-list td.ccm-submission-list-cb input[type=checkbox]").click(function (t) {
        t.stopPropagation();
		ccmFormidableRescanMultiResultsMenu()
    });
    $("#ccm-results-list td.ccm-submission-list-cb").click(function (t) {
        t.stopPropagation();
        $(this).find("input[type=checkbox]").click();
    });

    $("#ccm-submission-list-multiple-operations").change(function () {
        var t = $(this).val();
		var n = []
		switch (t) {
            case "delete":
                if (confirm(delete_all)) {
					$("#ccm-results-list td.ccm-submission-list-cb input[type=checkbox]:checked").each(function() {
						n.push($(this).val());
					});	
					ccmFormidableDeleteMultipleAnswerSet(n.join(','));
				}
                break;
        }
        $(this).get(0).selectedIndex = 0
    });
};

ccmFormidableRescanMultiResultsMenu = function(){
	if ($("#ccm-results-list td.ccm-submission-list-cb input[type=checkbox]:checked").length > 0)
		$("#ccm-submission-list-multiple-operations").attr("disabled",!1);
	else
		$("#ccm-submission-list-multiple-operations").attr("disabled",!0);
}

ccmFormidableSearchResults = function(object) {
	var formObj = $('#ccm-results-search');	
	var params = formObj.serializeArray();
	path = formObj.attr('action');
	if (params.length > 0) {
		path += params[0].value+'/'+params[1].value;
	}
	window.location.href = path;
}

ccmFormidableOpenAnswerSetDialog = function(answerSetID) {
	var query_string = "answerSetID="+answerSetID;
	jQuery.fn.dialog.open({ 
		width: 670,
		height: 600,
		modal: true,
		href: dialog_url+"?"+query_string,
		title: "View Formidable Form submission"
	});
}

ccmFormidableDeleteAnswerSet = function(answerSetID) {
	data = 'action=delete&answerSetID='+answerSetID;
	jQuery.fn.dialog.showLoader();					
	ccm_deactivateSearchResults('results');					
	$.ajax({ 
		type: "POST",
		url: tools_url,
		data: data,
		dataType: 'json',
		success: function(ret) {
			ccmFormidableSetMessage(ret['type'], ret['message']);
			$("#ccm-results-advanced-search").ajaxSubmit(function(r) {
				ccm_parseAdvancedSearchResponse(r, 'results');
			});
		}
	});	
}


ccmFormidableDeleteMultipleAnswerSet = function(answerSetIDs) {
	data = 'action=delete_multiple&answerSetID='+answerSetIDs;
	jQuery.fn.dialog.showLoader();					
	ccm_deactivateSearchResults('results');		
	$.ajax({ 
		type: "POST",
		url: tools_url,
		data: data,
		dataType: 'json',
		success: function(ret) {
			ccmFormidableSetMessage(ret['type'], ret['message']);
			$("#ccm-results-advanced-search").ajaxSubmit(function(r) {
				ccm_parseAdvancedSearchResponse(r, 'results');
			});
		}
	});	
}

ccm_setupResultSearch = function (e) {
    $(".chosen-select").chosen();
    $("#ccm-result-list-cb-all").click(function () {
        if ($(this).prop("checked") == 1) {
            $(".ccm-list-record td.ccm-result-list-cb input[type=checkbox]").attr("checked", !0);
            $("#ccm-results-list-multiple-operations").attr("disabled", !1)
        } else {
            $(".ccm-list-record td.ccm-result-list-cb input[type=checkbox]").attr("checked", !1);
            $("#ccm-results-list-multiple-operations").attr("disabled", !0)
        }
    });
    $("td.ccm-result-list-cb input[type=checkbox]").click(function (e) {
        $("td.ccm-result-list-cb input[type=checkbox]:checked").length > 0 ? $("#ccm-results-list-multiple-operations").attr("disabled", !1) : $("#ccm-results-list-multiple-operations").attr("disabled", !0)
    });
    $("#ccm-results-list-multiple-operations").change(function () {
        var t = $(this).val();
        switch (t) {
            case "delete":               
				var n = [];
				if (confirm(delete_all)) {
					$("td.ccm-result-list-cb input[type=checkbox]:checked").each(function () {
						n.push($(this).val());
					});	
					ccmFormidableDeleteMultipleAnswerSet(n.join(','));					
				}
				break;
        }
        $(this).get(0).selectedIndex = 0
    })
	ccmFormidableCreateMenu();
};