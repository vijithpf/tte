<?php   
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('search/group');
$gl = new GroupSearch();
$gl->updateItemsPerPage(0);
$gl->sortBy('gName', 'asc');
$gResults = $gl->getPage(); 

$checkedGIDs = explode(',',$controller->gIDs); 
?> 

<style>
#ccm-block-fields { font-size:13px; line-height:18px; } 
#ccm-block-fields h2 { font-size:16px; line-height:21px; font-weight:bold; margin-bottom:8px; padding-bottom:0; }

#ccm-tony-mailing-list-pane #mailing-list-groups { max-height:150px; overflow:hidden; overflow-x:auto; overflow-y:auto; border:1px solid #ddd; background:#fafafa; padding:4px; margin-top:4px; }

.ccm-blockEditPane { margin-top:12px; } 
</style>


<div id="ccm-tony-mailing-list-pane" class="ccm-tony-mailing-list-pane"> 


    <ul id="ccm-blockEditPane-tabs" class="ccm-dialog-tabs">
        <li class="ccm-nav-active"><a id="ccm-blockEditPane-tab-settings" href="javascript:void(0);"><?php   echo t('Settings') ?></a></li>
        <li class=""><a id="ccm-blockEditPane-tab-attributes"  href="javascript:void(0);"><?php   echo t('User Attributes')?></a></li>  
    </ul>

   	<div id="ccm-blockEditPane-settings" class="ccm-blockEditPane">

	 
		<div class="ccm-block-field-group">
			<h2><?php  echo t('Title')?></h2>
			<input name="signupTitle" type="text" value="<?php echo htmlentities($controller->signupTitle, ENT_QUOTES, 'UTF-8') ?>" />&nbsp;
			<select name="titleWeight">
				<option value=""><?php echo t('Default') ?></option>
				<option value="Bold" <?php echo ($controller->titleWeight=='Bold')?'selected="selected"':'' ?>><?php echo t('Bold') ?></option>
				<option value="H1" <?php echo ($controller->titleWeight=='H1')?'selected="selected"':'' ?>><?php echo t('H1') ?></option>
				<option value="H2" <?php echo ($controller->titleWeight=='H2')?'selected="selected"':'' ?>><?php echo t('H2') ?></option>
				<option value="H3" <?php echo ($controller->titleWeight=='H3' || !$controller->titleWeight)?'selected="selected"':'' ?>><?php echo t('H3') ?></option>
				<option value="H4" <?php echo ($controller->titleWeight=='H4')?'selected="selected"':'' ?>><?php echo t('H4') ?></option>
				<option value="H5" <?php echo ($controller->titleWeight=='H5')?'selected="selected"':'' ?>><?php echo t('H5') ?></option> 
			</select>&nbsp;		
		</div> 
	
	 
		<div class="ccm-block-field-group">
			<h2><?php  echo t('Signup Text')?></h2>
			<textarea name="signupText" cols="50" rows="2" style="width:100%; height:50px; "><?php echo htmlentities($controller->signupText, ENT_QUOTES, 'UTF-8') ?></textarea>
		</div>
		
		
		<div class="ccm-block-field-group">
			<h2><?php  echo t('Successful Subscription Message')?></h2>
			<textarea name="subscribedMsg" cols="50" rows="2" style="width:100%; height:50px; "><?php echo htmlentities($controller->subscribedMsg, ENT_QUOTES, 'UTF-8') ?></textarea>
		</div>	 
		
		
		
	 
		<div class="ccm-block-field-group">
			<h2><?php  echo t('Choose Mailing List Group(s)')?></h2>
			
				
			<div id="mailing-list-groups"> 
			
				<?php  
				$selectedGroups = array();
				$unselectedGroups = array();
				foreach($checkedGIDs as $gID){  
					foreach( $gResults as $g ){  
						if( intval($gID)==intval($g['gID']) ){ 
							$selectedGroups[]=$g; 
							break;
						}
					}
				}
				foreach( $gResults as $g ){ 
					if( !in_array($g['gID'],$checkedGIDs) ) $unselectedGroups[]=$g; 
				}
				$orderedGroups = array_merge($selectedGroups,$unselectedGroups); 
				$lockDownGIDs = explode(',',Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS'));
				
				$enabledGroups=0;
				if( is_array($orderedGroups)) foreach($orderedGroups as $g) { 
					
					//options set on the mailing list settings page
					if( !in_array($g['gID'],$lockDownGIDs) ) continue;
				
					if( $g['gName']==t('Administrators') || $g['gName']=='Administrators' ) continue;
					
					$enabledGroups++;
					?>
					<div class="mailing-list-group">
						<input name="gID[]" type="checkbox" value="<?php echo intval($g['gID']) ?>" <?php echo  (in_array(intval($g['gID']),$checkedGIDs)) ? 'checked="checked"':'' ?> />
						<a class="mailing-list-group-inner" href="#" onclick="return false;"><?php echo $g['gName']?></a>
						<?php  if( strlen($g['gDescription']) ){ ?>
						<span class="mailing-list-group-description"> - <?php echo $g['gDescription']?></span>
						<?php  } ?>
					</div> 
				<?php  } 
				
				if( !intval($enabledGroups) ) echo '<div style="margin:8px 0px">'.t('No groups are enabled.').'</div>';  
				?>	
			</div>
			
			<div class="ccm-note" style="margin-top:2px"><?php echo t('These are the group(s) the user will enter/exit when subscribing/unsubscribing. Add more through the %sMailing List Settings%s page.','<a target="_blank" href="'.View::url('/dashboard/mailing_list/settings').'">','</a>') ?></div>
			
			<div style="margin-top:12px"> 
				<input name="showCheckboxes" type="checkbox" value="1" <?php echo  ( $controller->showCheckboxes ) ? 'checked="checked"' : '' ?> /> 
				<?php  echo t('Show mailing list group checkboxes')?>
			</div>		
	
		</div>     
		   
		   
		   
		<div class="ccm-block-field-group">
			<h2><?php echo  t('Let Unregistered Users Subscribe')?></h2>
			<input name="allowUnregistered" type="checkbox" onclick="tonyMailingListEdit.showEmailValidateWrap(this)" onchange="tonyMailingListEdit.showEmailValidateWrap(this)" value="1" <?php echo  ( $controller->allowUnregistered ) ? 'checked="checked"' : '' ?> /> 
				<?php echo  t('Yes')?>
				
			<div id="validateEmailWrap" style="margin-top:8px; display:<?php echo ($controller->allowUnregistered)?'block':'none' ?>">
			<input name="validateEmail" type="checkbox" value="1" <?php echo  ( $controller->validateEmail ) ? 'checked="checked"' : '' ?> /> 
				<?php echo  t('Require email validation check for unregistered users')?>		
			</div>
		</div> 
			
	</div>
	
	
	
	<div id="ccm-blockEditPane-attributes" class="ccm-blockEditPane" style="display:none">
	
		<div style="margin-bottom:8px; "><?php echo  t('Do you want to collect any of these attributes from subscribers?') ?></div>
	
		<?php  
		$userAttrs = explode(',',$controller->userAttrs);
		foreach(UserAttributeKey::getList() as $userAttr){ ?> 
			<div>
				<input name="userAttrs[]" value="<?php echo intval($userAttr->getAttributeKeyID()) ?>" type="checkbox" <?php echo (in_array(intval($userAttr->getAttributeKeyID()),$userAttrs))?'checked="checked"':'' ?>  />
				<?php echo $userAttr->getAttributeKeyName() ?>
			</div> 
		<?php  } ?>
		
		<div style="margin-top:16px; margin-bottom:16px;">
			<input name="attrsRequired" value="1" type="checkbox" <?php echo ($controller->attrsRequired)?'checked="checked"':'' ?>  />
			<?php echo t('Make attributes required fields') ?>
		</div>
	
	</div>

</div> 