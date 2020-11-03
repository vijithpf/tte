<?php 
defined('C5_EXECUTE') or die(_("Access Denied.")); 

$token = Loader::helper('validation/token');
$h = Loader::helper('concrete/interface'); 
$dashboardHelper = Loader::helper('concrete/dashboard');  

?>

<style>
.ccm-notification { background:#FFFFCC; color:#555; padding:4px; margin-bottom:16px; border:1px solid #ddd; font-weight:bold;  }

#mailing-list-settings-form {}

#lockdownGroupsWrap { max-height:150px; overflow:hidden; overflow-x:auto; overflow-y:auto; border:1px solid #ddd; background:#fafafa; padding:4px; margin-top:4px; }

#mailingsGroupAccessTable { width:100% }
#mailingsGroupAccessTable tr td { padding-right:8px;  }
#mailingsGroupAccessTable tr.header td { font-weight:bold;  }

.displayedCodeSample { font-family:'Courier New', Courier, monospace; font-size:11px; color:#79b  }

.fieldRow { margin-top:8px; overflow:hidden; }
.fieldRow label { width:180px; font-weight:bold; color:#666; float:left; display:block; padding:0 8px 0 0; }
.fieldRow .fieldRowField { float:left; }
.fieldRow .warningMsg { color:#bb5555; font-size:10px; }

td.labelCol { width:180px; font-weight:bold; color:#666; } 

.ccm-note { font-size:12px; line-height:14px; color:#999; }
.ccm-ui input[type="checkbox"], .ccm-ui input[type="radio"] { margin-right:3px; } 
</style>
 
<script>
var MailingListSettings = {  
	
	init:function(){ 
		$('input[name=customTemplate]').each(function(i,el){ 
			$(el).click(function(){ MailingListSettings.showCustomTemplate(); });
			$(el).change(function(){ MailingListSettings.showCustomTemplate(); })
		})
	}, 
	
	showCustomTemplate:function(){ 
		var d = ( parseInt($('input[name=customTemplate]:checked').val())==1 ) ? 'block' : 'none'; 
		$('#customHeader').css('display',d);
		$('#customFooter').css('display',d);
		d=(d=='block')?'none':'block';
		$('#defaultHeader').css('display',d);
		$('#defaultFooter').css('display',d);
	},
	
	setThrottling:function(cb){
		var d = (cb.checked)?'block':'none';
		$('#throttlingOptionsWrap').css('display',d);
	}
	
}
$( function(){ MailingListSettings.init(); } )
</script>
 
 
 <?php   
if( method_exists( $dashboardHelper, 'getDashboardPaneHeaderWrapper') ){  
	echo $dashboardHelper->getDashboardPaneHeaderWrapper(t('Mailing List Settings')); 
}else{ ?> 
    <h1><span><?php echo t('Mailing List Settings')?></span></h1> 
    <div class="ccm-dashboard-inner" > 
<?php  } ?>  

	<form id="mailing-list-settings-form" action="<?php echo  View::url('/dashboard/mailing_list/settings','save') ?>" method="post">
	
		<?php echo $token->output('mailing_list_settings')?>
	
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
		
			<div style="float:right">
			<input name="allowAllUsersMailing" type="checkbox" value="1" <?php echo  (intval($allowAllUsersMailing)) ? 'checked="checked"':'' ?> />
			<?php echo  t('Allow mailings to all registered users') ?>
			</div>		
		
			<h2><?php echo  t('Group Access') ?></h2>
			
			<div id="lockdownGroupsWrap">
				<table id="mailingsGroupAccessTable">
				<tr class="header">
					<td style="width:50%"><?php echo t('Group Name') ?></td>
					<td><?php echo t('Allow Subscriptions') ?></td>
					<td><?php echo t('Enable Mailings') ?></td>
				</tr>
				<?php   
				
				Loader::model('search/group');
				$gl = new GroupSearch();
				$gl->updateItemsPerPage(0);
				$gl->sortBy('gName', 'asc');
				$gResults = $gl->getPage(); 
				
				foreach ($gResults as $g) { 
					?>
					<tr>
						<td>
							<a class="mailing-list-group-inner" href="#" onclick="return false;"><?php echo $g['gName']?></a>
							<?php  if( strlen($g['gDescription']) ){ ?>
							<span class="mailing-list-group-description"> - <?php echo $g['gDescription']?></span>
							<?php  } ?>
						</td>
						<td>
							<?php  if( $g['gName']==t('Administrators') || $g['gName']=='Administrators' ){ ?>
							&nbsp;&nbsp;--&nbsp;
							<?php  }else{ ?>
							<input name="lockDownGIDs[]" type="checkbox" value="<?php echo intval($g['gID']) ?>" <?php echo  (in_array(intval($g['gID']),$lockDownGIDs)) ? 'checked="checked"':'' ?> />
							<?php  } ?>
						</td> 
						<td>
							<input name="enableMailingsGIDs[]" type="checkbox" value="<?php echo intval($g['gID']) ?>" <?php echo  (in_array(intval($g['gID']),$enableMailingsGIDs)) ? 'checked="checked"':'' ?> />
							
						</td>
					</tr>
				<?php  } ?>	 

				</table>
			</div>
			
			<div class="ccm-note" style="margin-top:8px;">
			<?php 
			$groupAccessWarning = t('Subscribing and unsubscribing provides an easy way for users to enter & exit selected %sgroups%s.','<a href="'.View::url('/dashboard/users/groups').'">','</a>');
			$groupAccessWarning2 = t('For this reason you only want to enable subscriptions for groups directly related to your mailing lists.'); 
			$groupAccessWarning3 = t("Groups that have subscriptions disabled will still allow for opting-out of future mailings."); 
			$groupAccessWarning4 = t('You can restrict access to this settings page by using the page privledges in the %ssitemap%s','<a href="'.View::url('/dashboard/sitemap').'">','</a>');
			echo $groupAccessWarning.' '.$groupAccessWarning2.' '.$groupAccessWarning3.' '.$groupAccessWarning4; 
			?>
			</div> 
			
			<div style="margin-top:8px;">
				<input name="blacklistUnsubscribeOn" type="checkbox" value="1" <?php echo  (intval($blacklistUnsubscribeOn)) ? 'checked="checked"' : '' ?> /> 
				<?php echo  t('When unsubscribing, display "blacklist" option, which allows people to unsubscribe permanently from all future mailings.') ?>  
			</div>
								
		</div>			



		<div style="margin-top:24px; ">
			<h2><?php echo  t('Send Process Configuration') ?></h2>
			
			<div class="fieldRow">
				<label><?php echo  t('Auto-Send:') ?></label>
				<div class="fieldRowField">
					<input name="sendOnCreate" type="checkbox" value="1" <?php echo  (intval($sendOnCreate)) ? 'checked="checked"':'' ?> />
					<?php echo  t('Start sending emails directly after their creation.') ?>
					<?php  if(!TonyMailingList::cURL_installed()){ ?>
						<div class="warningMsg"><?php echo t('Warning: since cURL is not installed on your server this option will not work.') ?></div>
					<?php  } ?>
				</div>
			</div>	
				
			<div class="fieldRow">
				<label><?php echo  t('Logging:') ?></label>
				<div class="fieldRowField">
					<?php echo  t('Log Sent Emails:') ?>
					<input name="emailLogging" type="radio" value="-1" <?php echo  (intval($emailLogging)==-1) ? 'checked="checked"':'' ?> /><?php echo  t('Off') ?>&nbsp;
					<input name="emailLogging" type="radio" value="0" <?php echo  (intval($emailLogging)==0) ? 'checked="checked"':'' ?> /><?php echo  t('First %s Only',TonyMailingList::$limitedLogCount) ?>&nbsp; 
					<input name="emailLogging" type="radio" value="1" <?php echo  (intval($emailLogging)==1) ? 'checked="checked"':'' ?> /><?php echo  t('All') ?> 
					<div class="ccm-note" style="margin-top:4px;">
						<?php echo t('It is not recommend to log "All" sent emails when sending to large groups of recipients. %sView Log%s &raquo;','<a href="'.View::url('/dashboard/reports/logs').'">','</a>') ?>
					</div>
				</div>
			</div> 
			
			<div class="fieldRow">
				<label><?php echo  t('Send Throttling:') ?></label>
				<div class="fieldRowField">
					<input name="throttle" type="checkbox" value="1" <?php echo  ( intval($throttle)==1 ) ? 'checked="checked"':'' ?> onclick="MailingListSettings.setThrottling(this);" onchange="MailingListSettings.setThrottling(this);" />
					<?php echo  t('Slow down sending of emails (if your server is being overloaded).') ?> 
				</div>
			</div>
			
			<div id="throttlingOptionsWrap" style="display:<?php echo  (intval($throttle)) ? 'block' : 'none' ?>" >
			
				<div class="fieldRow">
					<label><?php echo  t('Maximum Run Time:') ?></label>			
					<?php  if( !ini_get('safe_mode') ){ ?> 
						<input name="maxTime" type="text" value="<?php echo  round(floatval($maxTime)/60,2) ?>" size="6" />
						<?php echo  t('minutes') ?> 
					<?php  }else{ ?>
						<?php echo  t('Disabled, since php Safe Mode is on. It is currently fixed at %s seconds',ini_get('max_execution_time')) ?>
						<input name="maxTime" type="hidden" value="<?php echo  intval(ini_get('max_execution_time'))?intval(ini_get('max_execution_time')):30 ?>" /> 
					<?php  } ?>
				</div>
						
				<div class="fieldRow">
					<label><?php echo  t('Send Rate:') ?></label>			 
					<?php echo  t('After sending') ?> 
					<input name="emailsPerSet" type="text" value="<?php echo intval($emailsPerSet) ?>" size="4" /> 
					<?php echo  t('emails, pause for ') ?> 
					<input name="pauseTime" type="text" value="<?php echo intval($pauseTime) ?>" size="4" /> 
					<?php echo  t('seconds') ?> 
				</div> 

			</div>
			
			<div class="fieldRow">
				<label><?php echo  t('Auto-Restart:') ?></label>			 
				<?php echo  t('If idle or interrupted, restart after ') ?>
				<input name="autoRestartTime" type="text" size="3" value="<?php echo intval($autoRestartTime) ?>"  /> 
				<?php echo  t('minutes') ?> 
			</div>	
			<?php  /*
			<div class="fieldRow">
				<label><?php echo  t('Bounce-Back Email (optional):') ?></label>
				<input name="bounceBackEmail" type="text" size="15" value="<?php echo htmlentities( $bounceBackEmail, ENT_QUOTES, 'UTF-8')  ?>"  />
			</div>						
			*/ ?>
			<div class="fieldRow ccm-note" >
				<?php echo  t("If you would like to set up a cronjob for sending out mailings, you can have your server call this url:"); ?><br />
				<?php echo  TonyMailingList::getSendPendingURL(0) ?>
			</div>
		
		</div>
		
		
		
		<div style="margin-top:24px; ">
			<h2><?php echo  t('Email Design') ?></h2>
		
			<div>
				<table style="width:98%" cellpadding="0" cellspacing="0">
					<tr>
						<td class="labelCol">
							<?php echo  t('Template:') ?>
						</td>
						<td>
							<input name="customTemplate" type="radio" value="0" <?php echo (!$customTemplate)?'checked="checked"':'' ?> />&nbsp;<?php echo  t('Basic') ?>&nbsp;&nbsp;   
							<input name="customTemplate" type="radio" value="1" <?php echo ($customTemplate)?'checked="checked"':'' ?> />&nbsp;<?php echo  t('Custom') ?> 
						</td>
					</tr>
				</table>
			</div>
			
			<div id="customTemplateWrap" style="margin-top:8px;">
				<table style="width:98%" cellpadding="0" cellspacing="0">
					<tr>
						<td class="labelCol">
							<?php echo  t('Header HTML:') ?>
						</td>
						<td>
							<div id="defaultHeader" class="displayedCodeSample" style="margin-bottom:8px; display:<?php echo (!$customTemplate)?'block':'none'; ?>"><?php echo  htmlentities(TonyMailingList::getDefaultHeaderHTML(), ENT_QUOTES, 'UTF-8') ?></div>
							<textarea id="customHeader" style="width:100%;display:<?php echo ($customTemplate)?'block':'none'; ?>" name="headerHTML" cols="50" rows="4"><?php echo  htmlentities(TonyMailingList::getHeaderHTML(), ENT_QUOTES, 'UTF-8') ?></textarea>
						</td>
					<tr>
					<tr>
						<td class="labelCol">
							<?php echo  t('Footer HTML:') ?>
						</td>
						<td> 
							<div id="defaultFooter" class="displayedCodeSample" style="display:<?php echo (!$customTemplate)?'block':'none'; ?>; "><?php echo  htmlentities(TonyMailingList::getDefaultFooterHTML(), ENT_QUOTES, 'UTF-8') ?></div>
							<textarea id="customFooter" style="width:100%;display:<?php echo ($customTemplate)?'block':'none'; ?>" name="footerHTML" cols="50" rows="4"><?php echo  htmlentities(TonyMailingList::getFooterHTML(), ENT_QUOTES, 'UTF-8') ?></textarea>
							
							<div class="ccm-note" style="margin-top:4px;">
								<?php echo  t("Note that you can't use a lot of standard HTML and CSS with HTML emails (%smore info%s).",'<a href="http://articles.sitepoint.com/article/code-html-email-newsletters/" target="_blank">','</a>') ?> 
								<?php echo  t("Make sure you test in multiple email clients before doing a full mailing."); ?>
							</div>							
						</td>
					<tr>					
				</table>		
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