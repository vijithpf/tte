var SubscribersHelper = { 
	
	init:function(){
		
		$('#mailing-list-subscribers-form input[name=mode]').each(function(i,el){ 								
			$(el).click(function(){ SubscribersHelper.iAgreeShown(); } );
			$(el).change(function(){ SubscribersHelper.iAgreeShown(); } );
		});
		
		$('#mailing-list-subscribers-form input[name=importAttributes]').each(function(i,el){ 								
			$(el).click(function(){ SubscribersHelper.attributeSettingsShown(); } );
			$(el).change(function(){ SubscribersHelper.attributeSettingsShown(); } );
		});		
		
		$('#attributeColumns .removeColumn').live('click',function(){ 
			$(this).parent('.attributeColumn').remove();
			SubscribersHelper.renumberAttributeColumns();
		}); 
		
		$('#subscribers_list #selectAll').change(function(){ 
			var checkboxes = $('#subscribers_list .subscriberCheckbox'); 										  
			if( $(this).is(':checked') ){
				checkboxes.prop('checked', true);
			}else{
				checkboxes.prop('checked', false); 
			}
		}); 
		
		$('#subscribers_list input').change(function(){ 
			var checkboxesCount = $('#subscribers_list .subscriberCheckbox').length,
				checkedCount = $('#subscribers_list .subscriberCheckbox:checked').length,
				d = checkedCount ? 'block' : 'none'; 
			$('#subscribersSelectedActions').css('display',d); 
			
			//var allSelected = !!(checkboxesCount==checkedCount) 
			//$('#subscribers_list #selectAll').prop('checked',allSelected); 
		});  
		
		$('#deleteSelectedSubscribers').click(function(){
			SubscribersHelper.deleteSelected(); 							   
		});
		
	},
	
	deleteSelected:function(){
		var subscriberIds=[]; 
		$('#subscribers_list .subscriberCheckbox:checked').each(function(){ 
			subscriberIds.push(this.value); 														 
		}); 
		
		if( !subscriberIds.length ){ 
			alert(  $('#delete_subscribers_none_msg').val() ); 
		}else if( confirm( $('#delete_subscribers_confirm_msg').val().replace('%subscribers_length%',subscriberIds.length) )  ){   
			var form = document.createElement("form"),
				hiddenField = document.createElement("input");  
			
			form.setAttribute("method", 'POST'); 
			form.setAttribute("action", $('#delete_subscribers_service').val() ); 
			hiddenField.setAttribute("type", "hidden"); 
			hiddenField.setAttribute("name", 'ids'); 
			hiddenField.setAttribute("value", subscriberIds.join(',') ); 
	
			form.appendChild(hiddenField);
			document.body.appendChild(form); 
			form.submit(); 
		}
	}, 
	
	iAgreeShown:function(){
		var d = ($('#mailing-list-subscribers-form input[name=mode]:checked').val()=='subscribe') ? 'block':'none'; 
		$('#new_subscription_not_spam').css('display',d);
	},
	
	nonUserEditTitle:'Non-Registered User Details',
	nonUserEdit:function(nuID){ 
		
		var url = $('#non_user_details_service').val()
		if(url.indexOf('?')<0) url=url+'?';
		url=url+'nuID='+nuID;
		
		$.fn.dialog.open({
			title: this.nonUserEditTitle,
			href: url,
			width: '550',
			modal: false,
			height: '200' 
   		});	
		
	},
	
	attributeSettingsShown:function(){
		var d = (parseInt($('#mailing-list-subscribers-form input[name=importAttributes]:checked').val())>0) ? 'block':'none'; 
		$('#attributeSettings').css('display',d);
	},		
	
	addAttributeColumn:function(){
		var colNum = 1;
		$('#attributeColumns .attributeColumn').each(function(){									  
			var thisColumnNumber = parseInt(this.id.replace('attributeColumn','').replace('COL_NUM_PLACEHOLDER',''));	
			if( thisColumnNumber>colNum ) colNum=thisColumnNumber;
		});
		colNum++;
		var html = '<div class="attributeColumn" id="attributeColumn'+colNum+'">'; 
		html = html+ $('#attributeColumnCOL_NUM_PLACEHOLDER').html().replace(/COL_NUM_PLACEHOLDER/g,colNum).replace(/attributeColumnIgnore/g,'attributeColumn');
		html = html+'</div>';
		$('#attributeColumns').append(html);
	}, 
	
	renumberAttributeColumns:function(){
		$i=1;
		$('#attributeColumns .attributeColumn').each(function(){
			if(this.id=='attributeColumnCOL_NUM_PLACEHOLDER') return; 										  
			this.id='attributeColumn' + $i;		
			$(this).find('.replaceableColNum').html($i);
			$i++;											  
		}); 	
	}
	
}
$(function(){ SubscribersHelper.init(); })