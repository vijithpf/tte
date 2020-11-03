<?php 
defined('C5_EXECUTE') or die(_("Access Denied.")); 

$token = Loader::helper('validation/token');
$h = Loader::helper('concrete/interface'); 
$dashboardHelper = Loader::helper('concrete/dashboard');  

?>

<style>
.ccm-notification { background:#FFFFCC; color:#555; padding:4px; margin-bottom:16px; border:1px solid #ddd; font-weight:bold;  }

#mailing-list-settings-form {}


#responderBodyWrap { padding-top:24px; }

.fieldRow { margin-top:8px; overflow:hidden; }
.fieldRow label { width:20%; font-weight:bold; color:#666; float:left; display:block; padding:0 8px 0 0;  }
.fieldRow .fieldRowField { float:left; }
.fieldRow .fieldCol { float:left; width:79%; }
.fieldRow .warningMsg { color:#bb5555; font-size:10px; }

td.labelCol { width:180px; font-weight:bold; color:#666; } 

.ccm-note { font-size:12px; line-height:14px; color:#999; }
.ccm-ui input[type="checkbox"], .ccm-ui input[type="radio"] { margin-right:3px; } 
</style>
 
<script>
var MailingListSettings = {  
	
	init:function(){ 
		$('input[name=responderEnabled]').each(function(i,el){ 
			$(el).click(function(){ MailingListSettings.showResponderSettings(this); });
			$(el).change(function(){ MailingListSettings.showResponderSettings(this); })
		})
	}, 
	
	showResponderSettings:function(cb){
		var d = (cb.checked)?'block':'none';
		$('#responderBodyWrap').css('display',d);
	}
	
}
$( function(){ MailingListSettings.init(); } )
</script>
 
 
 <?php   
if( method_exists( $dashboardHelper, 'getDashboardPaneHeaderWrapper') ){  
	echo $dashboardHelper->getDashboardPaneHeaderWrapper(t('Subscription Auto-Responder')); 
}else{ ?> 
    <h1><span><?php echo t('Subscription Auto-Responder')?></span></h1> 
    <div class="ccm-dashboard-inner" > 
<?php  } ?>  

	<form id="mailing-list-settings-form" action="<?php echo  View::url('/dashboard/mailing_list/responder','save') ?>" method="post">
	
		<?php echo $token->output('mailing_list_responder')?>
	
		<?php  if( $successFlag ){ ?> 
			<div class="ccm-notification">
				<?php echo t('Changes Saved') ?> 
			</div>
		<?php  }elseif($errorMsg){ ?>
			<div class="ccm-notification">
				<?php echo $errorMsg?> 
			</div>
		<?php  } ?>		
		
		
		<div> 
		
			<h4><?php echo  t('Send a welcome email to new subscribers') ?>
            	<input name="responderEnabled" type="checkbox" value="1" <?php echo  (intval($responderEnabled)) ? 'checked="checked"':'' ?> /> 
            </h4>  
            
            <div id="responderBodyWrap" style="display:<?php echo ($responderEnabled)?'block':'none'; ?>; ">
            
                <div class="fieldRow">
                    <label><?php echo  t("Sender's Name (Optional):") ?></label>
                    <div class="fieldCol">  
                        <input name="senderName" type="text" value="<?php echo  htmlentities( $senderName, ENT_QUOTES, 'UTF-8') ?>" size="20"/>  
                    </div>
                    <div class="ccm-spacer"></div>
                </div>
                
                <div class="fieldRow">
                    <label><?php echo  t("Subject:") ?></label>
                    <div class="fieldCol">  
                        <input name="subject" type="text" value="<?php echo  htmlentities( $subject, ENT_QUOTES, 'UTF-8') ?>" size="20"/>  
                    </div>
                    <div class="ccm-spacer"></div>
                </div>
            
                <div class="fieldRow">
                    <label><?php echo  t("Email Body:") ?></label>
                    <div class="fieldCol">  
                		<textarea id="responderBody" style="width:97%; " name="responderBody" cols="50" rows="10"><?php echo  htmlentities($responderBody, ENT_QUOTES, 'UTF-8') ?></textarea>
                    
                        <div class="ccm-note" style="margin-top:4px;">
                            <?php echo  t("Note that you can't use a lot of standard HTML and CSS with HTML emails (%smore info%s).",'<a href="http://articles.sitepoint.com/article/code-html-email-newsletters/" target="_blank">','</a>') ?> 
                            <?php echo  t("Make sure you test in multiple email clients before doing a full mailing."); ?>
                        </div>
                    </div>
                    <div class="ccm-spacer"></div>
                </div>	
                
                <div class="fieldRow">
                    <label><?php echo  t("Template:") ?></label>
                    <div class="fieldCol">  
    					<input name="noResponseTemplate" type="radio" value="0" <?php echo (!$noResponseTemplate)?'checked="checked"':'' ?> />&nbsp;<strong><?php echo  t('Global') ?></strong>
                        <span class="ccm-note"><?php echo  t('Use the same email template that is defined on your settings page.') ?></span>
                        <br/> 
                         
						<input name="noResponseTemplate" type="radio" value="1" <?php echo ($noResponseTemplate)?'checked="checked"':'' ?> />&nbsp;<strong><?php echo  t('None') ?></strong>
                        <span class="ccm-note"><?php echo  t('Use only the html entered above.') ?></span> 
                    </div>
                    <div class="ccm-spacer"></div>
                </div>	
    
    

            
            </div>	
            
		</div>		 	
		
		
		<div style="margin-top:16px; float:right">
			<?php 
			echo $h->submit(t('Save Changes &raquo;'), 'mailing-list-settings-form', 'none', "btn ccm-button success");
			//print $h->button($b1);
			?>
			<br class="clear" /> 
		</div>
	</form>

	
	<div class="ccm-spacer"></div>
	
<?php  if( method_exists( $dashboardHelper, 'getDashboardPaneFooterWrapper') ){ 
	echo $dashboardHelper->getDashboardPaneFooterWrapper(); 
}else{ ?>  
    </div> 
<?php  } ?> 