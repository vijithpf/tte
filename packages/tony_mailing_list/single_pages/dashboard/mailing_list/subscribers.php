<?php 
defined('C5_EXECUTE') or die(_("Access Denied.")); 

global $c;

$token = Loader::helper('validation/token');
$dashboardHelper = Loader::helper('concrete/dashboard'); 

?>

<style>
.ccm-notification { background:#FFFFCC; color:#555; padding:4px; margin-bottom:16px; border:1px solid #ddd; font-weight:bold; clear:both;  }
table#subscribers_list { width:100%; margin-top:16px; }
table#subscribers_list td { vertical-align:top; padding:4px 8px 4px 2px; border-bottom:1px solid #eee; } 
table#subscribers_list tr.header td { font-weight:bold; background:#F1F1F1; }
table#subscribers_list .selectColumn { width:20px; text-align:center; }
#attributeColumnCOL_NUM_PLACEHOLDER { display:none; }
#attributeColumns .removeColumn { cursor:pointer; font-size:1.2em; }
#removeAttributeColumn { margin-top:4px; display:block; cursor:pointer; }
td.unsubscriber, .unsubscribedGroup { text-decoration:line-through; } 
.unsubscribeDate { color:#999; } 
.ccm-note { font-size:12px; line-height:14px; color:#999;  } 

#subscribersSelectedActions { display:none; padding-top:8px; }  
#subscribersSelectedActions #deleteSelectedSubscribers { background:#900; background:linear-gradient(#911,#700); color:#fff; border:1px solid #300; font-size:11px; }

.ccm-ui input[type="checkbox"], .ccm-ui input[type="radio"] { margin-right:3px; }

.mailing-list-settings-section { margin-bottom:20px; } 
.mailing-list-settings-section .pagination{ text-align:center; color:#bbb; padding-top:4px; height:auto !important; margin:0 !important; } 
.mailing-list-settings-section .pagination a { float:none !important; border:0 none !important; padding:0px !important; line-height:inherit !important;  }
.mailing-list-settings-section .pagination span { padding:0 1px; }
.mailing-list-settings-section .pagination .pageRight{width:20%; float:right; text-align:right}
.mailing-list-settings-section .pagination .pageLeft{width:20%; float:left; text-align:left}
</style> 


<script>
SubscribersHelper.nonUserEditTitle = "<?php echo t('Non-Registered User Details')?>"; 
</script>


<?php   
if( method_exists( $dashboardHelper, 'getDashboardPaneHeaderWrapper') ){  
	echo $dashboardHelper->getDashboardPaneHeaderWrapper(t('Manage Subscribers')); 
}else{ ?> 
    <h1><span><?php echo t('Manage Subscribers')?></span></h1> 
    <div class="ccm-dashboard-inner" > 
<?php  } ?> 


<h3><span><?php echo ($showUnsubscribers) ? t('Unsubscribers') : t('Subscribers') ?></span></h3>

<div class="mailing-list-settings-section" > 

	<?php  if( defined('MAILING_LIST_ENABLE_BULK_DELETE') ){ ?>
		<input id="delete_subscribers_service" name="delete_subscribers_service" type="hidden" value="<?php echo View::url($c->getCollectionPath(),'delete_subscribers') ?>" /> 
        <input id="delete_subscribers_none_msg" name="delete_subscribers_none_msg" type="hidden" value="<?php echo t("You must first select the subscribers that you wish to delete.") ?>" /> 
        <input id="delete_subscribers_confirm_msg" name="delete_subscribers_confirm_msg" type="hidden" value="<?php echo t("Are you sure you want to remove these %subscribers_length% subscribers?") ?>" /> 
	<?php  } ?> 

	<input id="non_user_details_service" name="non_user_details_service" type="hidden" value="<?php echo View::url($c->getCollectionPath(),'non_user_details') ?>" /> 

	<form method="get" action="<?php echo View::url($c->getCollectionPath() )?>" style="float:right;">
	
		<?php echo t('Page Size:') ?>
		<select name="pageSize">
			<?php  foreach(array(5,10,20,50,100,500,1000) as $size){ ?>
			<option value="<?php echo $size ?>" <?php echo ($pageSize==$size)?'selected="selected"':'' ?>><?php echo $size ?></option>
			<?php  } ?>
		</select>&nbsp; 
		
		
		<select name="gID">
			<option value="<?php echo intval($g['gID']) ?>"><?php echo t('All Mailing List Groups') ?></option>
			<?php  foreach ($gResults as $g) {   
				if( !in_array($g['gID'], $enabledMailingLists) ) continue;  
				?>
				<option value="<?php echo intval($g['gID']) ?>" <?php echo ($g['gID']==$_REQUEST['gID'])?'selected="selected"':'' ?>><?php echo $g['gName']?></option>
			<?php  } ?>
             
            <?php  		
			//only show unsubscribe link is attributes are installed
			$unsubscribeDataAttr = UserAttributeKey::getByHandle('unsubscribe_data');
			if( is_object($unsubscribeDataAttr) ){ ?>
            	<option value="unsubscribers"  <?php echo ($_REQUEST['gID']=='unsubscribers')?'selected="selected"':'' ?> ><?php echo t('Unsubscribers Only') ?></option>
            <?php  } ?>
		</select>
        
		<input name="Submit" type="submit" value="<?php echo t('Filter') ?>" class="btn ccm-button" />
		
	</form>
    
     
	<?php  if( $unsubscribed_count ){ ?>  
        <div class="ccm-notification"> 
            <?php echo $unsubscribed_count ?> <?php echo ($unsubscribed_count==1) ? t('subscriber has been removed from all mailing lists.') : t('subscribers have been removed from all mailing lists.') ?>  
        </div>  
    <?php  } ?>  


	<?php  if(!count($recipientsPage)){ ?>
		<div style="margin:32px 0;"><?php echo ($showUnsubscribers) ? t('No unsubscribers found.') : t('No subscribers found.') ?></div>
	<?php  }else{ ?>
		<div style="font-style:italic"> 
			<?php echo $resultCount ?> 
            
            <?php  if($showUnsubscribers){ 
					echo ($resultCount!=1) ? t('Unsubscribers') : t('Unsubscriber'); 
				}else{ 
					echo ($resultCount!=1) ? t('Subscribers') : t('Subscriber'); 
				} 
			?> 
		</div>
		
		<table id="subscribers_list">
			<tr class="header">
            	<?php  if( defined('MAILING_LIST_ENABLE_BULK_DELETE') ){ ?>
					<td class="selectColumn"><input type="checkbox" name="selectAll" id="selectAll" /></td>
            	<?php  } ?>
				<td><?php echo t('Email: ') ?></td>
				<td><?php echo t('User Type: ') ?></td>
				<td><?php echo $showUnsubscribers ? t('Subscriptions: ') : t('Subscribed To: ') ?></td>
			</tr>
			<?php   foreach($recipientsPage as $recipient){ 
				$recipientUID=intval($recipient['uID']);
				?>
				<tr>
                	<?php  if( defined('MAILING_LIST_ENABLE_BULK_DELETE') ){ 
						$subscriberID = ($recipientUID) ? 'uID_'.$recipientUID : 'mluID_'.intval($recipient['mluID']);
						?>
                        <td class="selectColumn"><input type="checkbox" class="subscriberCheckbox" name="subscriberIDs[]" value="<?php echo $subscriberID ?>" /></td>
                    <?php  } ?>
					<td <?php echo ($showUnsubscribers)?'class="unsubscriber"':''?>>
						<?php 
						if( $recipientUID ){ 
							echo '<a href="'.View::url('/dashboard/users/search?uID='.$recipientUID).'">'.$recipient['email'].'</a>'; 
						}else{
							echo '<a href="#" onclick="return SubscribersHelper.nonUserEdit('.intval($recipient['mluID']).');" >'.$recipient['email'].'</a>';
						}
						?>
					</td>
					
					<td><?php echo ($recipientUID) ? t('Registered') : t('Unregistered') ?></td>
					<td>
						<?php  
						$userOnBlacklist=0; 
						if($showUnsubscribers){ 
							if( $recipient['unsubscribeData']['blacklist'] ){
								$userOnBlacklist=1;
								echo '<div>'.t('Blacklisted on ').date('F j, Y',$recipient['unsubscribeData']['blacklist']).'</div>';
							}
							foreach($recipient['unsubscribeData']['groups'] as $unsubscribeGID=>$unsubscribeDate)  
								if(!in_array($unsubscribeGID,$recipient['gIDs'])) 
									$recipient['gIDs'][]=$unsubscribeGID;
						}  
						
						if(is_array($recipient['gIDs']) && is_array($gResults)) foreach ($gResults as $g) {   
							if( !in_array($g['gID'], $recipient['gIDs']) ) continue; 
							
							if($showUnsubscribers)
								$unsubscribedClass = ($userOnBlacklist || array_key_exists($g['gID'],$recipient['unsubscribeData']['groups'])) ? 'unsubscribedGroup' : '';
							?>
							<div>
                            	<span class="<?php echo  $unsubscribedClass ?>"><?php echo $g['gName']?></span>
                                <?php  
								if( strlen($unsubscribedClass) ) { ?>
									<span class="unsubscribeDate">  
                                    	&nbsp;<?php echo  t('Unsubscribed').' '.date('F j, Y',$recipient['unsubscribeData']['groups'][$g['gID']]); ?>
                                    </span>
								<?php  } ?>
                            </div>
						<?php  } ?> 
					</td>
				</tr> 
			<?php  } ?> 
		</table>
        <?php  if( defined('MAILING_LIST_ENABLE_BULK_DELETE') ){ ?>
			<div id="subscribersSelectedActions">
            	<input type="button" id="deleteSelectedSubscribers" name="deleteSelectedSubscribers" value="<?php echo t('Remove') ?>" /> 
            </div>
        <?php  } ?>
	<?php  } ?>
	
	
	<div class="ccm-spacer"></div>
	
	
	<?php  if($paginator && strlen($paginator->getPages())>0){ ?>	
		<div class="pagination">	
			 <span class="pageRight"><?php  echo $paginator->getNext(t('Next &raquo;'))?></span>
			 <span class="pageLeft"><?php  echo $paginator->getPrevious(t('&laquo; Previous'))?></span>
			 <?php  echo $paginator->getPages()?>				 
		</div>	
	<?php  } ?>		
	
</div>



<h3><span><?php echo t('Import Subscribers')?></span></h3>

<div class="mailing-list-settings-section" > 

	<form  enctype="multipart/form-data" id="mailing-list-subscribers-form" action="<?php echo  View::url('/dashboard/mailing_list/subscribers','import') ?>" method="post">
	
		<?php echo $token->output('mailing_list_import')?>
		
		<?php  if( $import_success ){ ?> 
			<div class="ccm-notification">
				<?php echo $usersAdded.' '.t('users successfully subscribed.') ?> 
			</div>
		<?php  }elseif($errorMsg){ ?>
			<div class="ccm-notification">
				<?php echo $errorMsg?> 
			</div>
		<?php  } ?>		
		
		<div>
			<?php echo t('Upload a .csv or .txt file containing email addresses:') ?><br />
			<input type="file" name="emails_file"> 
		</div>
		
		<div style="margin:16px 0px;">
			<?php echo t('Select a subscription group for these users to be placed into:') ?><br />
			<select name="gID">
				<option value="">--------</option>
				<?php  
				$gIDs = explode(',',Config::get('TONY_MAILING_LIST_ENABLE_MAIL_GIDS'));
				foreach($gIDs as $gID){
					if(!intval($gID)) continue; 
					$g = Group::getById($gID);
					if( !is_object($g) ) continue;
					?>
					<option value="<?php echo intval($gID) ?>" <?php echo (intval($_REQUEST['gID'])==$gID)?'selected="selected"':''?>><?php echo  $g->getGroupName() ?></option>
				<?php  } ?>
			</select>
		</div>
		
		<div style="margin:16px 0px;">
			<strong><?php echo t('Import Mode: ')?></strong><br />
			<input name="importAttributes" type="radio" value="0" <?php echo (!$_REQUEST['importAttributes']) ? 'checked="checked"' : '' ?> /><?php echo t('Emails Only')?>&nbsp; 
			<input name="importAttributes" type="radio" value="1" <?php echo ($_REQUEST['importAttributes']) ? 'checked="checked"' : '' ?>  /><?php echo t('Emails & Attributes')?>
		</div>	
		
		<div id="attributeSettings" style="margin:16px 0px; display:<?php echo ($_REQUEST['importAttributes'])?'block':'none'?>">
			<strong><?php echo t('Assign Columns: ')?></strong><br />
			<div id="attributeColumns">
				<?php  
				$columns= (is_array($_REQUEST['attributeColumn'])) ? $_REQUEST['attributeColumn'] : array('email','','','','');
				//offset by one to ignore the hidden placeholder
				$columns= array_merge(array('col_num_placeholder'),$columns);
				$colNum=0;
				foreach( $columns as $colAttr ){  
					$replaceableColNum = ($colNum)?$colNum:'COL_NUM_PLACEHOLDER';
					?>
					<div class="attributeColumn" id="attributeColumn<?php echo $replaceableColNum ?>">
						<?php echo t('Column %s:','<span class="replaceableColNum">'.$replaceableColNum.'</span>') ?> 
						<select name="attributeColumn<?php echo ($replaceableColNum=='COL_NUM_PLACEHOLDER')?'Ignore':''?>[]">
							<option value="" <?php echo (''==$colAttr)?'selected="selected"':'' ?>>
								----------
							</option>
							<option value="email" <?php echo ('email'==$colAttr)?'selected="selected"':'' ?>>
								<?php echo t('Email') ?>
							</option>
							<?php  foreach(UserAttributeKey::getList() as $userAttr){ 
								$attrType = $userAttr->getAttributeType();
								if( !in_array($attrType->getAttributeTypeHandle(),array('text','textarea','number','boolean','date_time')) ) continue;
								?>
								<option value="<?php echo $userAttr->getAttributeKeyHandle() ?>"
									<?php echo ($userAttr->getAttributeKeyHandle()==$colAttr)?'selected="selected"':'' ?> >
									<?php echo $userAttr->getAttributeKeyName() ?>
								</option>
							<?php  } ?>
							<option value="" <?php echo ($colAttr=='ignore')?'selected="selected"':'' ?>>Ignore</option>
						</select> 
						[<a class="removeColumn"><?php echo t('-')?></a>]
					</div>
					<?php  
					$colNum++;
				} ?>
			</div>
			<a id="removeAttributeColumn" onclick="return SubscribersHelper.addAttributeColumn(this)"><?php echo t('Add Another Column') ?></a> 
			
			<div class="ccm-note" style="margin-top:8px; "><?php echo t('Only the following attribute types are available for import: text, textarea, number, boolean, &amp; date_time') ?></div>
				
		</div>						
		
		<div style="margin-bottom:16px">
			<input name="agreed" type="checkbox" value="1" <?php echo  ($_REQUEST['agreed']) ? 'checked="checked"':'' ?> /> 
			<?php echo t('I confirm that i will not send unsolicited mailings.') ?>
		</div>
		<input name="submit" type="submit" value="<?php echo t('Submit &raquo;') ?>" class="btn ccm-button" />
		
	</form>
	
	<div class="ccm-spacer"></div>
	
</div>




 
<h3><span><?php echo t('Subscribe / Unsubscribe Users')?></span></h3>

<div class="mailing-list-settings-section" > 

<a name="subscription_form" id="subscription_form"></a>
	
<form id="mailing-list-subscribers-form" action="<?php echo  View::url('/dashboard/mailing_list/subscribers','unsubscribe') ?>#subscription_form" method="post">
	
		<?php echo $token->output('mailing_list_blacklist')?> 
		
		<?php  if( $subscribe_success ){ ?> 
			<div class="ccm-notification">
				<?php echo t('The email address %s has been subscribed.',$_REQUEST['unsubscribe_email']) ?> 
			</div>
		<?php  
			$_REQUEST['unsubscribe_email']='';
		}elseif( $unsubscribe_success ){ ?> 
			<div class="ccm-notification">
				<?php echo t('The email address %s has been unsubscribed.',$_REQUEST['unsubscribe_email']) ?> 
			</div>
		<?php  
			$_REQUEST['unsubscribe_email']='';
		}elseif($unsubscribeErrorMsg){ ?>
			<div class="ccm-notification">
				<?php echo $unsubscribeErrorMsg?> 
			</div>
		<?php  } ?>			
	
		<strong><?php echo t('Email: ')?></strong><br />
		<input name="unsubscribe_email" type="text" value="<?php echo htmlentities($_REQUEST['unsubscribe_email'], ENT_QUOTES, 'UTF-8') ?>" />&nbsp;  
		
		<input name="mode" type="radio" value="subscribe" <?php echo ($_REQUEST['mode']=='subscribe') ? 'checked="checked"' : '' ?> /><?php echo t('Subscribe')?>&nbsp; 
		<input name="mode" type="radio" value="unsubscribe" <?php echo ($_REQUEST['mode']=='unsubscribe') ? 'checked="checked"' : '' ?>  /><?php echo t('Unsubscribe')?>
		
		
		<div style="margin:8px 0px 12px 0px;">
		<strong><?php echo t('Group: ')?></strong> 
		<select name="unsubscribe_gID">
			<option value="">==========</option>
			<?php  
			foreach($gIDs as $gID){
				if(!intval($gID)) continue; 
				$g = Group::getById($gID);
				if( !is_object($g) ) continue; ?>
				<option value="<?php echo intval($gID) ?>" <?php echo (intval($gID)==intval($_REQUEST['unsubscribe_gID']))?'selected="selected"':''?>><?php echo $g->getGroupName() ?></option>
			<?php  } ?>
			<option value="-1" <?php echo (-1==intval($_REQUEST['unsubscribe_gID']))?'selected="selected"':''?>><?php echo t('Blacklist from All') ?></option>
		</select>
		</div>
		
		
		<div id="new_subscription_not_spam" style="margin-bottom:16px; display:<?php echo ($_REQUEST['mode']=='subscribe')?'block':'none' ?>">
			<input name="agreed" type="checkbox" value="1" <?php echo  ($_REQUEST['agreed']) ? 'checked="checked"':'' ?> /> 
			<?php echo t('I confirm that i will not send unsolicited mailings.') ?>
		</div>
		<input name="submit" type="submit" value="<?php echo t('Submit &raquo;') ?>" class="btn ccm-button" />
			
	</form>
	
	<div class="ccm-spacer"></div>
	
</div> 

<?php  if( method_exists( $dashboardHelper, 'getDashboardPaneFooterWrapper') ){ 
	echo $dashboardHelper->getDashboardPaneFooterWrapper(); 
}else{ ?>  
    </div> 
<?php  } ?> 