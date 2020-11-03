var tonyMailingListEdit ={     
	
	init:function(){  
		$('#mailing-list-groups').sortable({
			cursor: 'move',
			opacity: 0.5,
		});
		
		this.tabSetup();
	},
	
	tabSetup: function(){
		$('ul#ccm-blockEditPane-tabs li a').each( function(num,el){ 
			el.onclick=function(){
				var pane=this.id.replace('ccm-blockEditPane-tab-','');
				tonyMailingListEdit.showPane(pane);
			}
		});		
	},	
	showPane:function(pane){
		$('ul#ccm-blockEditPane-tabs li').each(function(num,el){ $(el).removeClass('ccm-nav-active') });
		$(document.getElementById('ccm-blockEditPane-tab-'+pane).parentNode).addClass('ccm-nav-active');
		$('div.ccm-blockEditPane').each(function(num,el){ el.style.display='none'; });
		$('#ccm-blockEditPane-'+pane).css('display','block'); 
	},		
	
	showEmailValidateWrap:function(cb){
		var d=(cb.checked)?'block':'none';
		$('#validateEmailWrap').css('display',d);
	},
	
	validate:function(){
		var failed=0; 
		
		if(failed){
			ccm_isBlockError=1;
			return false;
		} 
		return true;
	}
	
}

$(function(){ tonyMailingListEdit.init(); });
 
ccmValidateBlockForm = function() { return tonyMailingListEdit.validate(); }