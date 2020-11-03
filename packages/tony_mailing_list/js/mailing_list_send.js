
if( $(window).width()<1000 ){
	var buttonLineOne = "bold,italic,underline,strikethrough,formatselect,|,forecolor,backcolor,|,justifyleft,justifycenter,justifyright,justifyfull";	
	var buttonLineTwo = 'bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,image,|,cleanup,code,charmap,spellchecker'; 
}else{
	var buttonLineOne = "bold,italic,underline,strikethrough,formatselect,|,forecolor,backcolor,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,image,|,cleanup,code,charmap,spellchecker";	
	var buttonLineTwo = ''; 
}

tinyMCE.init({
	mode : "textareas",
	width: "100%",
	height: "300px",	
	inlinepopups_skin : "concreteMCE",
	theme_concrete_buttons2_add : "spellchecker",
	relative_urls : false,
	convert_urls: false, 
	theme : "advanced", 
	theme_advanced_buttons1 : buttonLineOne,
	theme_advanced_buttons2 : buttonLineTwo,
	theme_advanced_buttons3 : '',
	theme_advanced_blockformats : "p,h1,h2,h3,h4,h5,div,blockquote",
	theme_advanced_toolbar_location : "top",		
	theme_advanced_toolbar_align : "left",
	plugins: "inlinepopups,spellchecker,safari,advlink",
	editor_selector : "advancedEditor",
	spellchecker_languages : "+English=en" 
}); 


var MailingList = {
	
	init:function(){ 
		$('input[name=recipients]').each(function(i,el){ 
			$(el).click( MailingList.recipientChange );	
			$(el).change( MailingList.recipientChange );	
		}); 
		
		$('input[name=sender]').each(function(i,el){ 
			$(el).click( MailingList.senderChange );	
			$(el).change( MailingList.senderChange );	
		});  
		
		$('#attributeReplacementPanel').dialog(); 
		//jQuery.fn.dialog.showLoader(); 
		
		var editorControlLIs = $('.ccm-editor-controls-right-cap ul li'); 
		if( editorControlLIs.length==3 ){
			editorControlLIs.css('display','block'); 
		}
	},  
	
	getRecipientsQueryStr:function(){
		var recipMode = $('input[name=recipients]:checked').val();
		var qStr = '&recipients='+recipMode;
		qStr = qStr+'&whiteListAttrId='+parseInt( $('select[name=whiteListAttrId]').val() );
		//qStr = qStr+'&blackListAttrId='+parseInt( $('input[name=blackListAttrId]').val() );
		var hasGroups=0;
		$('.mailing-list-group input').each(function(i,el){ 									  
			if(el.checked){
				qStr = qStr + '&gID[]='+parseInt(el.value);
				hasGroups=1;
			}
		});
		if( !hasGroups && recipMode=='groups') return 'NoGroups';
 		return qStr;
	}, 
	
	msgSelectGroup:'Please select at least one group', 
	exportEmails:function(mode,a){  
		var recips = this.getRecipientsQueryStr(); 
		if( recips=='NoGroups' ){
			alert( this.msgSelectGroup ); 
			return false;
		} 
		var qStr='export_mode='+mode+recips; 
		a.href= $('#exportURL').val() + '?' + qStr;
		return true;
	},
	
	recipientChange:function(){
		if( $('input[name=recipients]:checked').val()=='groups' ){
			$('#mailing-list-groups').css('display','block'); 
		}else{
			$('#mailing-list-groups').css('display','none'); 
		}
	},
	
	senderChange:function(){
		var d=($('input[name=sender]:checked').val()=='other')?'inline':'none';
		$('#mailing-list-sender-other-wrap').css('display',d);
	},
	
	msgConfirmSend:"Are you sure this email is ready to send?",
	msgNumRecipients:"(%s recipients)",
	send:function(){
		
		if( this.disableSubmitConfirm || parseInt($('input[name=preview]').val())==0 ) 
			return true;
		
		var qStr=this.getRecipientsQueryStr()
		var countText='';
		$.ajaxSetup({async: false});
		
		$.ajax({ 
			type:'post',					  
			data:qStr,
			url: $('#recipientCountURL').val(),
			success: function(response){  
				eval('var jObj='+response);
				if( jObj && jObj.error ){ 
					alert(jObj.error)
				}else if( (jObj.count+"").length>0 ){ 
					countText= MailingList.msgNumRecipients.replace("%s",jObj.count);
				}
			}
		});
		
		$.ajaxSetup({async: true});
		
		if( !confirm( this.msgConfirmSend + ' '+ countText ) ) return false;
		
		return true;	
	},
	
	toggleEditor:function(id) { 
		if (!tinyMCE.get(id)){
			tinyMCE.execCommand('mceAddControl', false, id);
		}else{
			tinyMCE.execCommand('mceRemoveControl', false, id);
		}	
	},
	
	msgViewGroup:"This will only show subscribed registered users, not unregistered subscribers. Use the export feature to see all subscribers. Continue?",
	viewUserGroup:function(){
		if(!confirm(this.msgViewGroup) )	return false;
		return true;
	},
	
	clickAddAttachment:function(a){ 
		ccm_editorCurrentAuxTool='attachment';
		ccm_alLaunchSelectorFileManager('lastAttachment'); 
		return false
	},
	
	addAttachment:function(data){
		if(!parseInt(data.fID)) return false; 
		var html = '<div class="fileAttachmentRow" id="fileAttachmentRow'+data.fID+'">'; 
		html = html+'<input name="fileAttachmentFIDs[]" type="checkbox" checked="checked" value="'+data.fID+'" />'; 
		html = html+'<a class="fileAttachmentTitle" href="'+data.filePathDirect+'" target="_blank">'+data.title+'</a>';
		html = html+'</div>'; 
		$('#attachedFilesList').append(html);
	},
	
	saveAttributes:function(){  
		jQuery.fn.dialog.showLoader();
		$.ajax({ 
			type:'post',					  
			data: $('#mailingUserAttributeForm').serialize(),
			url: $('#saveAttrDefaultURL').val(),
			success: function(response){  
				eval('var jObj='+response);
				if( jObj && jObj.error ){
					alert(jObj.error)
				}else{ 
					$('#mailingUserAttrsSaved').css('display','block'); 
				}
				jQuery.fn.dialog.hideLoader();
			}
		});
		
	}
}
$( MailingList.init ); 


var editor_id = 'mailing-body';

// store the selection/position for ie..
var bm; 
setBookMark = function () {
	bm = tinyMCE.activeEditor.selection.getBookmark();
}

ccm_selectSitemapNode = function(cID, cName) {
	var mceEd = tinyMCE.activeEditor;	
	var url = $('#dispatcherURL').val() + '?cID=' + cID;
	
	mceEd.selection.moveToBookmark(bm);
	var selectedText = mceEd.selection.getContent();
	
	if (selectedText != '') {		
		mceEd.execCommand('mceInsertLink', false, {
			href : url,
			title : cName,
			target : null,
			'class' : null
		});
	} else { 
		var selectedText = '<a href="' + url + '" title="' + cName + '">' + cName + '<\/a>';
		tinyMCE.execCommand('mceInsertRawHTML', false, selectedText, true); 
	}
	
}



var ccm_editorCurrentAuxTool = ''; 
ccm_chooseAsset = function(obj) {
	var mceEd = tinyMCE.activeEditor;
	mceEd.selection.moveToBookmark(bm); // reset selection to the bookmark (ie looses it)

	switch(ccm_editorCurrentAuxTool) {
		case "attachment":
			MailingList.addAttachment(obj); 
			break; 
		case "image":
			var args = {};
			tinymce.extend(args, {
				src : obj.filePathInline,
				alt : obj.title,
				width : obj.width,
				height : obj.height
			});
			
			mceEd.execCommand('mceInsertContent', false, '<img id="__mce_tmp" src="javascript:;" />', {skip_undo : 1});
			mceEd.dom.setAttribs('__mce_tmp', args);
			mceEd.dom.setAttrib('__mce_tmp', 'id', '');
			mceEd.undoManager.add();
			break;
		default: // file
			var selectedText = mceEd.selection.getContent();
			
			if(selectedText != '') { // make a link, let mce deal with the text of the link..
				mceEd.execCommand('mceInsertLink', false, {
					href : obj.filePath,
					title : obj.title,
					target : null,
					'class' :  null
				});
			} else { // insert a normal link
				var html = '<a href="' + obj.filePath + '">' + obj.title + '<\/a>';
				tinyMCE.execCommand('mceInsertRawHTML', false, html, true); 
			}
		break;
	}
} 

