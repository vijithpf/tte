<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

$u = new User();

if( intval($mailing->getSenderUID()) ){
	$senderUID=intval($mailing->getSenderUID());
}else{ 
	$senderUID = $u->uID;
}
$sender_ui = UserInfo::getById($senderUID);

$textHelper = Loader::helper('text');
$dashboardHelper = Loader::helper('concrete/dashboard'); 
 
?>

<style> 
#mailing-interface { max-width:1000px }

#mailing-interface #mailing-list-groups { max-height:150px; overflow:hidden; overflow-x:auto; overflow-y:auto; border:1px solid #ddd; background:#fafafa; padding:4px; margin-top:4px; }
#mailing-interface #mailing-list-groups .mailing-list-group-description { color:#777 }

#mailing-interface #mailing-list-all-users-options { margin-top:8px; }

#mailing-interface .fieldPair { margin-bottom:8px; } 
#mailing-interface .fieldPair label { float:left; width:20%; font-weight:bold; color:#777; padding:0 8px 0 0;  }
#mailing-interface .fieldPair .fieldCol { float:left; width:79%; }

#mailing-interface .ccm-notification, .ccm-dialog-window div.ccm-notification { background:#FFFFCC; color:#555; padding:4px; margin-bottom:8px; border:1px solid #ddd;  }

.emailPreviewWrap { overflow:hidden; overflow-x:auto;overflow-y:auto; max-height:250px; width:100%; border:1px solid #ddd; padding:0; }
.emailPreviewWrap h1{ background:none; }
.emailPreviewWrap ul { list-style:disc; } 
.emailPreviewWrap ul,#mailingTable tr td ol { padding-left:32px; margin-left:0px; }
.emailPreviewWrap h1,.emailPreviewWrap h2,.emailPreviewWrap h3 { margin-bottom:16px; padding-bottom:0px; }

.fileAttachmentRow { margin-bottom:4px; }

.note, .ccm-dialog-window div.note { font-size:11px; line-height:13px; color:#999; }

#userAttributesList { width:100%; margin-top:16px; } 

#attributeReplacementPanel{ float:right; padding-top:4px; font-size:12px; }

#mailingUserAttrsSaved { display:none; }

.ccm-editor-controls-right-cap ul li:last-child { display:none; }

.ccm-note { font-size:12px; line-height:14px; color:#999;  }

.ccm-ui input[type="checkbox"], .ccm-ui input[type="radio"] { margin-right:3px; }
</style>


<script> 

<?php  if( !TonyMailingList::sendOnCreation() ) echo 'MailingList.disableSubmitConfirm=1;' ?> 

MailingList.msgSelectGroup = "<?php echo str_replace('"','',t('Please select at least one group')) ?>";
MailingList.msgConfirmSend = "<?php echo str_replace('"','', t("Are you sure this email is ready to send?") ) ?>";
MailingList.msgViewGroup = "<?php echo str_replace('"','',t('This will only show subscribed registered users, not unregistered subscribers. Use the export feature to see all subscribers. Continue?'))?>";
MailingList.msgNumRecipients= "<?php echo  str_replace('"','',t('(%s recipients)')) ?>"; 
</script>


<input id="exportURL" name="exportURL" type="hidden" value="<?php echo View::url('/dashboard/mailing_list/send/','export') ?>" />
<input id="recipientCountURL" name="recipientCountURL" type="hidden" value="<?php echo View::url('/dashboard/mailing_list/send/','recipient_count') ?>" />
<input id="saveAttrDefaultURL" name="saveAttrDefaultURL" type="hidden" value="<?php echo View::url('/dashboard/mailing_list/send/','save_attr_defaults') ?>" /> 
<input id="dispatcherURL" name="dispatcherURL" type="hidden" value="<?php echo BASE_URL . DIR_REL?>/<?php echo DISPATCHER_FILENAME?>" />


<?php  
$pageTitle = ( $mailing->getId() && !intval($_REQUEST['template']) ) ? t('Edit Mailing') : t('Create Mailing'); 
if( method_exists( $dashboardHelper, 'getDashboardPaneHeaderWrapper') ){  
	echo $dashboardHelper->getDashboardPaneHeaderWrapper($pageTitle); 
}else{ ?> 
    <h1><span><?php echo $pageTitle ?></span></h1> 
    <div class="ccm-dashboard-inner" > 
<?php  } ?>   

	<div id="mailing-interface">

	<form action="<?php echo View::url('/dashboard/mailing_list/send/','submit') ?>" method="post" <?php  if( $previewMode || $mailing->getId() ){ ?>onsubmit="return MailingList.send()"<?php  } ?>> 
		
		<?php  if( count($errorMsgs) ){ ?>
		
			<div class="ccm-notification">
				<strong><?php echo t('There were some problems with your info: ') ?></strong><br /> 
				<?php  foreach($errorMsgs as $errorMsg){ ?> 
					<div><?php echo  $errorMsg ?></div>
				<?php  } ?>
			</div>
			
		<?php  }elseif( $previewMode ){ ?>	
		
			<div class="ccm-notification">
				<strong><?php echo t('Please confirm that the body of email is formatted correctly, and resubmit.') ?></strong> 
			</div>		
		
		<?php  }elseif( $saved ){ ?> 
		
			<div class="ccm-notification">
				<strong><?php echo  t('Changes Saved') ?></strong>
				<?php  if($noAccessToMailings && !TonyMailingList::sendOnCreation() ){ ?>
					<div><?php echo  t("Please notify an administrator to review your mailing and to start the send process.") ?></div>		
				<?php  }else{ ?> 
					<div><?php echo  t("View the %smailing detail page &raquo;%s",'<a href="'.View::url('/dashboard/mailing_list/mailings/','detail', $savedMailingId ).'">','</a>') ?></div>
				<?php  } ?>
			</div>	
		
		<?php  } ?>
		
		<input name="submitted" type="hidden" value="1" />
		<input name="mlmid" type="hidden" value="<?php echo !intval($_REQUEST['template'])?$mailing->getId():0 ?>" /> 
		<input name="mode" type="hidden" value="<?php echo (intval($mailing->getId()) && !intval($_REQUEST['template']))?'edit':'add' ?>" /> 
		<input name="preview" type="hidden" value="<?php echo intval($previewMode) ?>" /> 
		
		<div class="fieldPair">
			<label><?php echo  t('Recipients:') ?></label>
			<div class="fieldCol">

				<div style="float:right">
					<?php echo  t('Export Emails:') ?>
					<a target="_blank" onclick="return MailingList.exportEmails('excel',this)" href="#"><?php echo  t('Excel') ?></a> &nbsp;|&nbsp; 
					<a target="_blank" onclick="return MailingList.exportEmails('csv',this)" href="#"><?php echo  t('CSV') ?></a>
				</div>

				<div style="display:<?php echo ($allowAllUsersMailing)?'block':'none'?>">
					<input name="recipients" type="radio" value="groups" <?php echo  (!$allowAllUsersMailing || $mailing->getRecipients()=='groups' || !$mailing->getRecipients()) ? 'checked="checked"':'' ?> /> <?php echo  t('%sGroups%s','<a href="'.View::url('/dashboard/mailing_list/settings').'">','</a>') ?>&nbsp; 
					<input name="recipients" type="radio" value="all" <?php echo  ($allowAllUsersMailing && $mailing->getRecipients()=='all') ? 'checked="checked"':'' ?> /> <?php echo t('Registered Users Only') ?>
				</div>
				
				<?php echo (!$allowAllUsersMailing)? '<a href="'.View::url('/dashboard/mailing_list/settings').'">'.t('Add Groups &raquo;').'</a>' : '' ?>
				
				<div class="ccm-spacer"></div>

				<div id="mailing-list-groups" style="display:<?php echo  ($mailing->getRecipients()=='groups' || !$mailing->getRecipients())?'block':'none' ?>" >
				<?php  
				/*
				$mailingListGroupName=t('Mailing List');
				$mailingListGroup = Group::getByName(  $mailingListGroupName  ); 
				if( is_object($mailingListGroup) ){ 
					$mailingGID=$mailingListGroup->getGroupID(); 
					$mailingDesc=$mailingListGroup->getGroupDescription(); 
					?>
					<div class="mailing-list-group">
						<input class="mailing_list_gID_checkbox" name="gID[]" type="checkbox" value="<?php echo $mailingGID ?>" <?php echo  (in_array($mailingGID,$checkedGIDs)) ? 'checked="checked"':'' ?> />
						<a class="mailing-list-group-inner" href="<?php echo $this->url('/dashboard/users/search?&gID[]=' . $mailingGID)?>"><?php echo $mailingListGroup->getGroupName() ?></a>
						<?php  if( strlen($mailingDesc) ){ ?>
							<span class="mailing-list-group-description"> - <?php echo $mailingDesc ?></span>
						<?php  } ?>
					</div>
				<?php  }
				*/ 
				
				foreach ($gResults as $g) { 
					//if($g['gName']==$mailingListGroupName) continue;
					if( !in_array($g['gID'],$enableMailingsGIDs) ) continue;  
					?>
					<div class="mailing-list-group">
						<input name="gID[]" type="checkbox" value="<?php echo intval($g['gID']) ?>" <?php echo  (in_array(intval($g['gID']), $mailing->getGIDs() )) ? 'checked="checked"':'' ?> />
						<a class="mailing-list-group-inner" onclick="return MailingList.viewUserGroup()" href="<?php echo $this->url('/dashboard/users/search?&gID[]=' . $g['gID'])?>"><?php echo $g['gName']?></a>
						<?php  if( strlen($g['gDescription']) ){ ?>
						<span class="mailing-list-group-description"> - <?php echo $g['gDescription']?></span>
						<?php  } ?>
					</div>
				
				<?php  } ?>
				</div> 
				
				<div id="mailing-list-all-users-options"> 
					<div style="margin-bottom:4px;"><?php echo  t('Only send to users with the following attribute enabled:') ?></div>
					<select name="whiteListAttrId">
						<option value="0"><?php echo  t('------') ?></option>
						<?php  
						$userAttributes = UserAttributeKey::getList();  
						foreach($userAttributes as $ak){ 
							 $akID = $ak->getAttributeKeyID(); 
							 $attrType = $ak->getAttributeType();
							 //getAttributeKeyHandle 
							 if( !is_object( $attrType) || $attrType->getAttributeTypeHandle()!='boolean' ) continue;
							 if( $ak->getAttributeKeyHandle()=='disable_emails' ) continue;
							 ?>
							<option value="<?php echo $akID ?>" <?php echo  ($mailing->getWhiteListAttrId() == $akID) ? 'selected':''?> >
								<?php echo  $textHelper->shorten($ak->getAttributeKeyName(),70) ?>
							</option>
						<?php  } ?>
					</select> 
					<div class="ccm-note" style="margin-top:2px; "><?php echo t('Boolean/checkbox user-attributes only.  This will limit this mailing to only registered users.') ?></div>
				</div>				
				
			</div>
			<div class="ccm-spacer"></div>
		</div>
		
		<div class="fieldPair">
			<label><?php echo  t("Sender's Email:") ?></label>
			<div class="fieldCol">

				<input name="sender" type="radio" value="this_user" <?php echo  ( $mailing->getSender()=='this_user' || (!$mailing->getSender() && !defined('MAILING_LIST_FROM_DEFAULT') ) ) ? 'checked="checked"':'' ?> /><?php echo  $sender_ui->getUserEmail(); ?>&nbsp; 
				<input name="sender" type="radio" value="other" <?php echo  ( $mailing->getSender()=='other' || (!$mailing->getSender() && defined('MAILING_LIST_FROM_DEFAULT') ) ) ? 'checked="checked"':'' ?> /><?php echo t('Other') ?>&nbsp;

				<span id="mailing-list-sender-other-wrap" style="display:<?php echo  ($mailing->getSender()=='other')?'inline':'none' ?>">
					<input name="sender_other" type="text" value="<?php echo  htmlentities( $mailing->getSenderEmail(), ENT_QUOTES, 'UTF-8') ?>" size="20"/>
				</span> 
				
			</div>
			<div class="ccm-spacer"></div>
		</div>		  
		
		<div class="fieldPair">
			<label><?php echo  t("Sender's Name (Optional):") ?></label>
			<div class="fieldCol">  
				<input name="sender_name" type="text" value="<?php echo  htmlentities( $mailing->getSenderName(), ENT_QUOTES, 'UTF-8') ?>" size="20"/>  
			</div>
			<div class="ccm-spacer"></div>
		</div>		 

		
		
		<?php  if( $previewMode ){
			
			$userAttrReplacedText = TonyMailingListMailing::userAttributeTextReplacement( $mailing->getBody(), $u );
			$absoluteLinksText=TonyMailingListMailing::relativeToAbsoluteLinks( $userAttrReplacedText );
			?>
			
			
			<input name="subject" type="hidden" value="<?php echo htmlentities( $mailing->getSubject(), ENT_QUOTES, 'UTF-8') ?>" />
			<div class="fieldPair">
				<label><?php echo  t('Subject:') ?></label>
				<div class="fieldCol">
					<?php echo  strip_tags(TonyMailingListMailing::userAttributeTextReplacement($mailing->getSubject(),$u)) ?>
				</div>
				<div class="ccm-spacer"></div>
			</div>			
		
		
			<input name="body" type="hidden" value="<?php echo htmlentities( $mailing->getBody(), ENT_QUOTES, 'UTF-8') ?>" />
			
			<div class="fieldPair">
				<label><?php echo  t('HTML Version:') ?></label>
				<div class="fieldCol">
                    <iframe src="<?php echo  View::url('/dashboard/mailing_list/send/-/preview/') ?>" class="emailPreviewWrap"></iframe>
				</div>
				<div class="ccm-spacer"></div>
			</div> 

			<div class="fieldPair">
				<label><?php echo  t('Plain Text Version:') ?></label>
				<div class="fieldCol"> 
					<div class="emailPreviewWrap"><pre>
					<?php  
					$includeHeaderFooter = (defined('MAILING_LIST_HEADER_FOOTER_ON_PLAINTEXT') && MAILING_LIST_HEADER_FOOTER_ON_PLAINTEXT); 
					$headerHTML = (!$includeHeaderFooter) ? '' : TonyMailingListMailing::relativeToAbsoluteLinks(TonyMailingList::getHeaderHTML());  
					$footerHTML = (!$includeHeaderFooter) ? '' : TonyMailingListMailing::relativeToAbsoluteLinks(TonyMailingList::getFooterHTML()); 
					echo TonyMailingListMailing::html2text( $headerHTML . $absoluteLinksText . $footerHTML ); 
					?>
                    </pre></div>
				</div>
				<div class="ccm-spacer"></div>
			</div> 			
			
		<?php  }else{ ?>
		
			<div class="fieldPair">
				<label><?php echo  t('Subject:') ?></label>
				<div class="fieldCol">
					<input name="subject" type="text" value="<?php echo htmlentities( $mailing->getSubject(), ENT_QUOTES, 'UTF-8') ?>" style="width:100%" />
				</div>
				<div class="ccm-spacer"></div>
			</div>		
		
			<div class="fieldPair">
				<label><?php echo  t('Body:') ?></label>
				<div class="fieldCol"> 
					<?php  Loader::element('editor_controls'); ?>
					
					<textarea id="mailing-body" name="body" class="advancedEditor ccm-advanced-editor" cols="50" rows="5" style="width:100%; height:300px;" ><?php echo htmlentities( $mailing->getBody(), ENT_QUOTES, 'UTF-8') ?></textarea>
					
					<a dialog-width="750" dialog-height="350" id="attributeReplacementPanel" href="<?php echo View::url('/dashboard/mailing_list/send/','text_replacement')?>" dialog-title="User Attribute Replacement">User Attributes [+]</a>
					<div class="ccm-spacer"></div>
				</div>
				<div class="ccm-spacer"></div> 
			</div> 			
			
		<?php  } ?>
		
		


		<div class="fieldPair">
			<label><?php echo  t('Attachments (Optional):') ?></label>
			<div class="fieldCol"> 
				<div id="attachedFilesList">
					<?php  
					foreach( explode(',',$mailing->getAttachments()) as $fID ){  
						$file = File::getByID(intval($fID));
						if(!is_object($file) || !$file->getFileID()) continue; 
						$fv = $file->getApprovedVersion();
						?>
						<div class="fileAttachmentRow" id="fileAttachmentRow<?php echo  $file->getFileID() ?>">
							<input name="fileAttachmentFIDs[]" type="checkbox" checked="checked" value="<?php echo  $file->getFileID() ?>" /> 
							<a class="fileAttachmentTitle" href="<?php echo  $fv->getRelativePath() ?>" target="_blank"><?php echo  $fv->getTitle() ?></a>
							
						</div>
					<?php  } ?>
				</div>				
				<input name="lastAttachment" type="hidden" value="0" />
				<a onclick="return MailingList.clickAddAttachment(this)" href="#"><?php echo t('Add File') ?></a> 
				<div class="ccm-note"><?php echo t('Note: You should keep your total attachment size under 2MB.') ?></div> 
			</div>
			<div class="ccm-spacer"></div>
		</div> 		
		
		 
		 
		<div class="fieldPair">
			<label>&nbsp;</label>
			<div class="fieldCol" >
				
				<?php 
				if( !$previewMode ){ 
					$saveButtonTxt = t('Preview &raquo;');
				}elseif( TonyMailingList::sendOnCreation() ){  
					$saveButtonTxt = t('Send &raquo;'); 
				}else{ 
					$saveButtonTxt = t('Save As Pending &raquo;');  
				} 
				?>
			
				<input name="Submit" type="submit" value="<?php echo  $saveButtonTxt ?>" style="float:right" class="success btn ccm-input-submit"  />
				
				<input name="saveDraft" type="submit" value="<?php echo t('Save As Draft') ?>" style="float:right; margin-right:10px;" class="btn ccm-button primary"  onclick="MailingList.disableSubmitConfirm=1;" /> 
				
				<?php  if($mailing->getId()){ ?> 
					<a href="<?php echo View::url('/dashboard/mailing_list/mailings/','detail', $mailing->getId() ) ?>"><input name="Cancel" type="button" value="<?php echo t('&laquo; Cancel') ?>"  class="btn ccm-input-submit" /></a> 
				<?php  }elseif($previewMode){ ?>
					<input name="makeChanges" type="submit" value="<?php echo t('&laquo; Edit') ?>"  onclick="MailingList.disableSubmitConfirm=1;" class="btn ccm-input-submit" /> 
				<?php  } ?>
				<div class="ccm-note" style="display:none; " ><?php echo t('Rich Text:') ?> <a href="#" onclick="MailingList.toggleEditor('body'); return false;"><?php echo t('On/Off')?></a></div>
				<div class="ccm-spacer"></div>
			</div>
			<div class="ccm-spacer"></div>
		</div>  
	
	</form> 

	</div> 

<?php  if( method_exists( $dashboardHelper, 'getDashboardPaneFooterWrapper') ){ 
	echo $dashboardHelper->getDashboardPaneFooterWrapper(); 
}else{ ?>  
    </div> 
<?php  } ?> 