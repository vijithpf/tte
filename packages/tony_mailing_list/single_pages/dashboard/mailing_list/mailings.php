<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
$u = new User();
$ui = UserInfo::getById($u->uID);
$ih = Loader::helper('concrete/interface');
$dashboardHelper = Loader::helper('concrete/dashboard');  
?>

<style>
#mailingsTable, #mailingTable { width:100% }
#mailingsTable tr td { border-bottom:1px solid #eee; }
#mailingsTable tr td, #mailingTable tr td { padding:4px 3px; vertical-align: top;   }
#mailingsTable tr.keycell td, #mailingTable tr td.keycell { background:#f1f1f1; font-weight:bold; padding:2px 8px 2px 3px;  }  
#mailingTable tr td.keycell { width:180px; border-bottom:1px solid #fff !important; }

#mailingTable tr td .emailPreviewWrap { overflow:hidden; overflow-x:auto;overflow-y:auto; max-height:250px; width:100%; border:1px solid #ddd; padding:0; }
#mailingTable tr td .emailPreviewWrap h1{ background:none; }
#mailingTable tr td .emailPreviewWrap ul { list-style:disc; } 
#mailingTable tr td .emailPreviewWrap ul,#mailingTable tr td ol { padding-left:32px; margin-left:0px; }
.emailPreviewWrap h1,.emailPreviewWrap h2,.emailPreviewWrap h3 { margin-bottom:16px; padding-bottom:0px; }

.ccm-notification { background:#FFFFCC; color:#555; padding:4px; margin-bottom:8px; border:1px solid #ddd;  }

#mailing_status_text.running { background: url(<?php echo ASSETS_URL_IMAGES?>/dashboard/sitemap/loading.gif) no-repeat right; height:20px; padding-right: 24px;  } 

#mailingTable pre {
 white-space: pre-wrap;       /* css-3 */
 white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
 white-space: -pre-wrap;      /* Opera 4-6 */
 white-space: -o-pre-wrap;    /* Opera 7 */
 word-wrap: break-word;       /* Internet Explorer 5.5+ */
 border:0 none;
 background:none;  
}

#mailingTable td { border:0 none !important; }

#mailingStatsCell { width:400px; text-align:center; }
#mailingStatsCell #mailingStatsImg { float:left; margin-right:20px;  }
#mailingStatsCell #mailingStatsLabels { margin-top:30px; }
#mailingStatsCell .mailingStatsLabel { margin-bottom:4px; text-align:left; font-size:11px; }
#mailingStatsCell .mailingStatsLabelPercent { float:right; text-align:right;  }
#mailingStatsCell .mailingStatsLabelCounts { float:right; color:#bbb; min-width:30px; text-align:left; padding-left:4px; }
#mailingStatsCell .mailingStatsSwatch { width:10px; height:10px; font-size:1px; line-height:1px; float:left; margin-right:8px; border:1px solid #999; } 

#newMailingBtnWrap { margin-bottom:8px; overflow:hidden; min-height:38px; }

.spacer { clear:both; font-size:1px; line-height:1px; }

.ccm-note { font-size:12px; line-height:14px; color:#999;  }

.real-estate-settings-section .pagination{ text-align:center; color:#bbb; padding-top:4px; height:auto !important; margin:0 !important; } 
.real-estate-settings-section .pagination a { float:none !important; border:0 none !important; padding:0px !important; line-height:inherit !important;  }
.real-estate-settings-section .pagination span { padding:0 1px; }
.real-estate-settings-section .pagination .pageRight{width:20%; float:right; text-align:right}
.real-estate-settings-section .pagination .pageLeft{width:20%; float:left; text-align:left}
</style>



<script> 

var MailingsHelper = {
	
	restartTimeout:0,
	
	init:function(){
		
		this.autoRestart();
		
		$('#startSendProcess').click(function(){
			clearTimeout(MailingsHelper.restartTimeout); 
		})
	},
	
	mlid:0,
	send:function( mlid ){
		this.mlid=mlid;
		<?php  if( TonyMailingList::cURL_installed() && is_object($mailing) && $mailing->getStatus()!='completed' ){ ?>  
			$('#mailing_status_text').html( "<?php echo  t('Running') ?>" );
			$('#mailing_status_msg').css('display','none');
			$('#mailing_status_text').addClass('running'); 
		
			setTimeout('MailingsHelper.updateStatus();',5000); 
		<?php  } ?>
	},
	
	updateStatus:function(){
		$.ajax({ 
			type:'post',					  
			data:'mlid='+parseInt(this.mlid),
			url: "<?php echo  View::url('/dashboard/mailing_list/mailings/','updateStatus') ?>",
			success: function(response){ 
				//alert(response);
				eval('var jObj='+response);
				if( jObj && jObj.error ){ 
					alert(jObj.error); 
				}else{  
					//if( jObj.msg ) alert(jObj.msg)	;
					if(jObj.statusText) $('#mailing_status_text').html(jObj.statusText);
					if(jObj.sentCount) $('#mailing_sent_count').html(jObj.sentCount); 
					if(jObj.failedCount) $('#mailing_failed_count').html(jObj.failedCount); 
					
					
					var d = ( jObj.status=='pending' || jObj.status=='interrupted' ) ? 'inline':'none';
					$('#trigger_send_process_form').css('display',d);
					
					var d = ( (jObj.status=='interrupted' || jObj.status=='sleeping' ) && jObj.msg ) ? 'block':'none'
					$('#mailing_status_msg').html(jObj.msg);
					$('#mailing_status_msg').css('display',d);
					
					if(jObj.status=='running' || jObj.status=='sleeping'){ 
						$('#mailing_status_text').addClass('running');
					}else{
						if( jObj.status=='interrupted' ) $('#mailing_status_text').addClass('interrupted');
						$('#mailing_status_text').removeClass('running');
					}
					
					if(jObj.status=='sleeping' || jObj.status=='running') 
						setTimeout('MailingsHelper.updateStatus();',5000);
				}
			}
		});
	},
	
	confirmDelete:function(){
		if( !confirm("<?php echo str_replace('"','',t("Are you sure you want to permanently delete this mailing?")) ?>") ) return false;
		window.location = $('#deleteMailingLink').attr('href');
	},
	
	lastSendCount:0,
	
	autoRestart:function(){
		
		//is the auto restart feature enabled? 
		this.restartMinutes=$('#autoRestartTime').val(); 
		if(!this.restartMinutes || this.restartMinutes==0) return;
		
		//is the sending process running? 
		if( !$('#mailing_status_text').hasClass('running') ) return; 
		
		this.restartInterval = setInterval(function(){
													
			var sendCount = $('#mailing_sent_count').html(),
				trigger=0;
			
			if( $('#mailing_status_text').hasClass('running') ){
				
				if ( sendCount==MailingsHelper.lastSendCount ) trigger=1;
				else clearTimeout(MailingsHelper.restartTimeout);
				
			}else if( $('#mailing_status_text').hasClass('interrupted')){ 
				trigger=1;
			}
			
			if(trigger){ 
				
				clearInterval(MailingsHelper.restartInterval);
			
				var restartMillis = MailingsHelper.restartMinutes * 60 * 1000;
			
				MailingsHelper.restartTimeout = setTimeout(function(){
					$('#startSendProcess').click(); 
				},restartMillis);
			}
			
			MailingsHelper.lastSendCount=sendCount;
			
		}, 10000);
		
	}
	
}
$(function(){ MailingsHelper.init(); });

<?php  if( is_object($mailing) && ($startMailingId || $mailing->getStatus()=='sleeping' || $mailing->getStatus()=='running' ) ) 
	echo '$(function(){MailingsHelper.send('.$mailing->getId().')});'; ?>


</script>

<?php  if( method_exists( $dashboardHelper, 'getDashboardPaneHeaderWrapper') ){  
	echo $dashboardHelper->getDashboardPaneHeaderWrapper(t('Review Your Mailings')); 
}else{ ?> 
    <h1><span><?php echo t('Review Your Mailings')?></span></h1> 
    <div class="ccm-dashboard-inner" > 
<?php  } ?> 

<div class="real-estate-settings-section" > 

<input id="autoRestartTime" name="autoRestartTime" type="hidden" value="<?php echo  intval(Config::get('TONY_MAILING_LIST_AUTO_RESTART_TIME')) ?>" /> 

	<?php  if($pageMsg){ ?>
		<div><?php echo  $pageMsg ?></div>
	<?php  } ?>


	<?php  if($mode=='detail'){ ?> 
	
		<?php  if(!$mailing) { ?>
		
			<div style="margin-top:16px;">
			<?php echo t('Mailing not found.') ?>
			</div>
			
			<div style="margin-top:16px;">
				<a href="<?php echo View::url('/dashboard/mailing_list/mailings') ?>"><?php echo  t('&laquo; View All Mailings') ?></a>
			</div>					
		
		<?php  }else{ 
		
			$userAttrReplacedText = TonyMailingListMailing::userAttributeTextReplacement( $mailing->getBody(), $u );
			$absoluteLinksText=TonyMailingListMailing::relativeToAbsoluteLinks( $userAttrReplacedText );
			?>
		
			<?php  if( $saved ){ ?>
			<div class="ccm-notification">
				<?php  if( TonyMailingList::sendOnCreation() && $mailing->getStatus()!='pending' && $mailing->getStatus()!='draft' ){ ?>
					<strong><?php echo  t('Your mailing was successfully saved, and the send process has been started for %s users.',$mailing->getRecipientsCount()) ?></strong>
				<?php  }elseif( $mailing->getStatus()=='pending' ){ ?> 
					<strong><?php echo  t('Your mailing was successfully saved, but the send process has not been started!') ?></strong>
					<div><?php echo  t('Initiate the send process, click the "Start" button below') ?></div>
				<?php  }else{ ?>
					<strong><?php echo  t('Changes Saved') ?></strong>
				<?php  } ?>
			</div> 		
			<?php  } ?> 
			
			<table id="mailingTable">
			
				<tr>
					<td class="keycell"><?php echo  t('Status') ?></td>
					<td>
					
						<span id="mailing_status_text" class="<?php echo ($mailing->getStatus()=='running' || $mailing->getStatus()=='sleeping')?'running':'' ?>"><?php echo  t($mailing->getStatusText()) ?></span> 
						
						<?php   
						$sendButtonMsg = t('Resume &raquo;');
						if( $mailing->getStatus()=='pending' ){
							$sendButtonMsg = t('Start &raquo;'); 
						}	
						?>
						
						<?php  if($mailing->getStatus()=='draft'){ ?>
							&nbsp;[ <a href="<?php echo View::url('/dashboard/mailing_list/send/', $mailing->getId() ) ?>"><?php echo t('Edit') ?></a> ]
						<?php  }else{ ?>
						<form id="trigger_send_process_form" style="display:<?php echo ($mailing->getStatus()=='pending' || $mailing->getStatus()=='interrupted')?'inline':'none'?>" action="<?php echo  View::url('/dashboard/mailing_list/mailings','trigger_send_process',$mailing->getId()) ?>">
							<input id="startSendProcess" name="submit" type="submit" value="<?php echo  $sendButtonMsg ?>"  class="btn ccm-button success" />
						</form>
						<?php  } ?>
						
						<div id="mailing_status_msg" class="ccm-note" style="margin-top:2px; display:<?php echo ($mailing->getStatus()=='interrupted')?'block':'none' ?>">
							<?php echo $mailing->getStatusMsg() ?>
						</div>
						
						<?php  if(!TonyMailingList::cURL_installed() && $mailing->getStatus()!='completed'){ ?>
							<div style="margin-top:2px;"><strong><?php echo t('Warning: since cURL is not installed on your server the send process may take a very long time if you are sending thousands of emails.') ?></strong></div>
						<?php  } ?>							
					</td>
					<td id="mailingStatsCell" rowspan="6">
						<?php  if( $mailing->statsEnabled() && $mailing->getSentCount() ){
						
							$chartSrc = TonyMailingListStats::getChart($calculatedStats);
							?> 
							<div id="mailingStatsImg"><img src="<?php echo $chartSrc ?>" alt="Mailing Statistics" title="Mailing Statistics" /></div> 
							
							<div id="mailingStatsLabels">
								<?php  
								$statsColors = TonyMailingListStats::getChartColors(); 
								$statsLabels = TonyMailingListStats::getChartLabels();
								?> 
									<div class="mailingStatsLabel">
										<div class="mailingStatsLabelCounts">
											(<?php echo $calculatedStats['viewedOnly'] ?>)
										</div>									
										<div class="mailingStatsLabelPercent">
											<?php echo $calculatedStats['viewedOnlyPercent'] ?>%
										</div>
										<div class="mailingStatsSwatch" style="background:#<?php echo $statsColors[0] ?>">&nbsp;</div>
										
										<?php echo $statsLabels[0] ?>
										
									</div>
									<div class="mailingStatsLabel">
										<div class="mailingStatsLabelCounts">
											(<?php echo $calculatedStats['clickThrus'] ?>)
										</div>									
										<div class="mailingStatsLabelPercent">
											<?php echo $calculatedStats['clickThrusPercent'] ?>%
										</div>
										<div class="mailingStatsSwatch" style="background:#<?php echo $statsColors[1] ?>">&nbsp;</div>
										
										<?php echo $statsLabels[1] ?>
										
									</div>
									<div class="mailingStatsLabel">
										<div class="mailingStatsLabelCounts">
											(<?php echo $calculatedStats['unsubscribed'] ?>)
										</div>									
										<div class="mailingStatsLabelPercent">
											<?php echo $calculatedStats['unsubscribedPercent'] ?>%
										</div>
										<div class="mailingStatsSwatch" style="background:#<?php echo $statsColors[2] ?>">&nbsp;</div>
										
										<?php echo $statsLabels[2] ?>
										
									</div>
									<div class="mailingStatsLabel">
										<div class="mailingStatsLabelCounts">
											(<?php echo $calculatedStats['unopened'] ?>)
										</div>									
										<div class="mailingStatsLabelPercent">
											<?php echo $calculatedStats['unopenedPercent'] ?>%
										</div>
										<div class="mailingStatsSwatch" style="background:#<?php echo $statsColors[3] ?>">&nbsp;</div>
										
										<?php echo $statsLabels[3] ?>
										
									</div>									
							</div>
							
							<div class="spacer">&nbsp;</div>
							
						<?php  }else echo "&nbsp;" ?> 
					</td>
				</tr>
				<tr>
					<td class="keycell"><?php echo  t('To') ?></td>
					<td>
						<?php  
						if( $mailing->getRecipients()=='groups' ){ 
							$groups=TonyMailingList::getRecipientGroups( $mailing->getGIDs() );
							foreach($groups as $g){
								if($notFirst) echo ', ';
								echo $g->getGroupName() ; 
								$notFirst=1;
							}
						}else{
							echo t('Registered users'); 	
						}
						
						echo '<br>'.t('(%s recipients, %s sent, %s <a href="%s">failed</a>)',$mailing->getRecipientsCount(),'<span id="mailing_sent_count">'.$mailing->getSentCount().'</span>','<span id="mailing_failed_count">'.$mailing->getFailedCount().'</span>',View::url('/dashboard/reports/logs'));
						?>
					</td>
				</tr>
				
				
				<tr>
					<td class="keycell"><?php echo  t("Sender's Email") ?></td>
					<td><?php echo  $mailing->getSenderEmail() ?></td>
				</tr>
				<tr> 
					<td class="keycell"><?php echo  t("Sender's Name") ?></td>
					<td><?php echo  $mailing->getSenderName() ?></td>
				</tr> 
				<tr>
					<td class="keycell"><?php echo  t('Created') ?></td>
					<td>
						<?php 
						$sendingUser = UserInfo::getById( $mailing->getSenderUID() );
						if ( is_object($sendingUser) ) $sendingUserName = $sendingUser->getUserName(); 
						echo date( t('M d, Y g:ia'),$mailing->getCreated()).' ';
						if($sendingUserName ) 
							echo t('by').' <a href="'.View::url('/dashboard/users/search/?uID='.$mailing->getSenderUID()).'">'.$sendingUserName.'</a>';
						?>
					</td>
				</tr>			
				<tr>
					<td class="keycell"><?php echo  t('Subject') ?></td>
					<td><?php echo  strip_tags(TonyMailingListMailing::userAttributeTextReplacement($mailing->getSubject(),$u ,1,0,1)) ?></td>
				</tr>
				<tr>
					<td class="keycell"><?php echo  t('HTML Version') ?></td>
					<td colspan="2"> 
                    	<iframe src="<?php echo  View::url('/dashboard/mailing_list/mailings/-/preview/') ?>?id=<?php echo $mailing->getId() ?>" class="emailPreviewWrap"></iframe>
                    </td>
				</tr>
				<tr>
					<td class="keycell"><?php echo  t('Plain Text Version') ?></td>
					<td colspan="2">
                    	<div class="emailPreviewWrap"><pre>
						<?php  
						$includeHeaderFooter = (defined('MAILING_LIST_HEADER_FOOTER_ON_PLAINTEXT') && MAILING_LIST_HEADER_FOOTER_ON_PLAINTEXT); 
						$headerHTML = (!$includeHeaderFooter) ? '' : TonyMailingListMailing::relativeToAbsoluteLinks(TonyMailingList::getHeaderHTML());  
						$footerHTML = (!$includeHeaderFooter) ? '' : TonyMailingListMailing::relativeToAbsoluteLinks(TonyMailingList::getFooterHTML()); 
						echo TonyMailingListMailing::html2text( $headerHTML . $absoluteLinksText . $footerHTML ); 
						?>
                       	</pre></div>
                	</td>
				</tr>
				
				<tr>
					<td class="keycell"><?php echo  t('Attachments') ?></td>
					<td colspan="2">
						<?php  if(!$mailing->getAttachments()){ 
							echo '&nbsp;';
						}else foreach( explode(',',$mailing->getAttachments()) as $fID ){  
							$file = File::getByID(intval($fID));
							if(!is_object($file) || !$file->getFileID()) continue; 
							$fv = $file->getApprovedVersion();
							?>
							<div class="fileAttachmentRow" id="fileAttachmentRow<?php echo  $file->getFileID() ?>"> 
								<a href="<?php echo  $fv->getRelativePath() ?>" target="_blank"><?php echo  $fv->getTitle() ?></a>
							</div>
						<?php  } ?> 
					</td>
				</tr>													
			
			</table>
			
			<div style="float:right; padding:0px 0 20px 0; overflow:hidden;">
                <a href="<?php echo View::url('/dashboard/mailing_list/send/', $mailing->getId()) ?>?template=1"><input name="template" type="button" value="<?php echo t('Use As Template') ?>"  class="btn ccm-button" /></a>&nbsp;&nbsp; 			
                
                <?php  if( $mailing->getStatus()=='pending' || $mailing->getStatus()=='draft'  ){ ?>
                    <a href="<?php echo View::url('/dashboard/mailing_list/send/', $mailing->getId() ) ?>"><input name="edit" type="button" value="<?php echo t('Edit') ?>"  class="btn ccm-button" /></a>&nbsp;&nbsp; 
                <?php  } ?>	
                
                <?php   										   
                $adminGroup = Group::getbyId(ADMIN_GROUP_ID); 
                if( ($u->uID==intval($mailing->getSenderUID()) || $u->inGroup($adminGroup) || $u->isSuperUser() ) ){ ?>
                    <a id="deleteMailingLink" href="<?php echo View::url('/dashboard/mailing_list/mailings/', 'delete', $mailing->getId() ) ?>" onclick="return MailingsHelper.confirmDelete()"><input name="delete" type="button" value="<?php echo t('Delete') ?>"   class="danger btn ccm-button" /></a> 
                <?php  } ?>
			</div>
				
			<div style="margin-top:16px;">
				<a href="<?php echo View::url('/dashboard/mailing_list/mailings') ?>"><?php echo  t('&laquo; View All Mailings') ?></a>
			</div>	
            
            <div class="spacer"></div>			
			
			<?php  if( $mailing->statsEnabled() && $mailing->getSentCount() ){ ?>
			<div class="ccm-note" style="clear:both;">
				<?php echo t("* Note that the viewed/unopened statistics are estimates, and are intended primarily to be used in making comparisons between your mailings.") ?> 
				<?php echo t("This is because some mail clients render in plain text, or require user consent before images are displayed in mailing." )?> 
				<?php echo t("Therefore your mailing's tracking pixel may not fire for every viewed email, and in most cases the percentage of actual opened emails will be higher.") ?> 
			</div>
			<?php  } ?>
			
		<?php  } ?>	
	
	
	<?php  }else{ ?>
	
		<div id="newMailingBtnWrap" class="ccm-buttons">  
			<?php echo $ih->button(t('Create New Mailing'), $this->url('/dashboard/mailing_list/send'), 'right', "btn ccm-button primary")?>
		</div>	 	
		
		<div class="spacer">&nbsp;</div>
	
	
		<?php  if( !count($mailings) ){ ?>
	
			<div style="margin:16px 0px;">
			<?php echo t('No mailings have yet been created.') ?>
			</div>
	
		<?php  }else{ ?> 
				
			<table id="mailingsTable">
				<tr class="keycell">
					<td><?php echo t('Subject') ?></td>
					<td><?php echo t('To') ?></td> 
					<td><?php echo t('Status') ?></td>
					<td><?php echo t('Opened') ?></td>
					<td><?php echo t('Click-Thrus') ?></td>
					<td><?php echo t('Created') ?></td>
				</tr>
				
				<?php  foreach($mailings as $mailing){ 
				
					$calculatedStats = TonyMailingListStats::calculateStats($mailing);
					?>
		
					<tr>
						<td><a href="<?php echo View::url('/dashboard/mailing_list/mailings','detail',$mailing->getId() ) ?>"><?php echo  $mailing->getSubject(); ?></a></td>
						
						<td>
							<?php  
							$notFirst=0;
							if( $mailing->getRecipients()=='groups' ){ 
								$groups=TonyMailingList::getRecipientGroups( $mailing->getGIDs() );
								foreach($groups as $g){
									if($notFirst) echo ', ';
									echo $g->getGroupName() ; 
									$notFirst=1;
								}
							}else{
								echo t('Registered users'); 	
							}							
							?>
						</td>
						<td>
							<?php  if($mailing->getStatus()=='draft'){ ?>
								<a href="<?php echo View::url('/dashboard/mailing_list/send/', $mailing->getId() ) ?>"><?php echo t('Draft') ?></a>
							<?php  }else{ ?>	
								<?php echo  $mailing->getStatus(); ?>
							<?php  } ?>
						</td>
						<td>
							<?php  if($mailing->statsEnabled() && $mailing->getSentCount()){ ?>
								<?php echo $calculatedStats['openedPercent']  ?>%
							<?php  }else{ ?>	
								<?php echo  t("-"); ?>
							<?php  } ?>
						</td>
						<td>
							<?php  if($mailing->statsEnabled() && $mailing->getSentCount()){ ?>
								<?php echo $calculatedStats['clickThrusPercent'] ?>%
							<?php  }else{ ?>	
								<?php echo  t("-"); ?>
							<?php  } ?>
						</td>												
						<td>
							<?php  
							$sendingUser = UserInfo::getById( $mailing->getSenderUID() ); 
							if ( is_object($sendingUser) ) $sendingUserName = $sendingUser->getUserName(); 
							echo date( t('M d, Y g:ia'), $mailing->getCreated()).' ' ;
							if( $sendingUserName ) 
								echo t('by').' <a href="'.View::url('/dashboard/users/search/?uID='.$mailing->getSenderUID()).'">'.$sendingUserName.'</a>';
							?> 
						</td> 
					</tr>
		
				<?php  } ?>
			
			</table>
			
			<?php    if($paginator && strlen($paginator->getPages())>0){ ?>	
			<div class="pagination" style="margin-top:12px;">	
				 <span class="pageLeft"><?php    echo $paginator->getPrevious(t('&laquo; Previous'))?></span>
				 <?php    echo $paginator->getPages()?>
				 <span class="pageRight"><?php    echo $paginator->getNext(t('Next &raquo;'))?></span>
			</div>	
			<?php    } ?>			
			
		<?php  } ?>
		
		
	<?php  } ?>
	

</div>
		
<?php  if( method_exists( $dashboardHelper, 'getDashboardPaneFooterWrapper') ){ 
	echo $dashboardHelper->getDashboardPaneFooterWrapper(); 
}else{ ?>  
    </div> 
<?php  } ?> 