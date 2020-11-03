<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

global $c;

$mode = ($_REQUEST['subscribe']) ? 'subscribe' : 'unsubscribe';

$confirmBlackListMsg = str_replace('"','',t('Are you sure you want to permanently unsubscribe from all future mailings from this site?'));
?>


<script>
var ManageSubscriptions = {
	
	submitForm:function(){ 
		if( $('#blackListUser').attr('checked') && !confirm("<?php echo  $confirmBlackListMsg ?>") ){
			$('#blackListUser').attr('checked',0);
			return false; 
		}
		return true;
	},
	
	blacklist:function(cb){ 
		if( cb.checked ){ 
			$('.groupIdCheckbox').each( function(i,el){ $(el).attr('checked','checked'); });
		}
	}
	
}
</script>


<div style="margin:32px;">

	
	<h2><?php echo  ($mode=='subscribe') ? t('Subscribe to Mailing List') : t('Unsubscribe from Mailing List') ?></h2>
	
	<div style="margin-top:16px;">
		
		<?php  if( $subscribed ){ ?>
		
			<div>
				<p><?php echo  t('You have been subscribed to this mailing list. ') ?></p>
				
				<div style="margin-top:16px">
				<a href="<?php echo View::url('/')?>"><?php echo  t('Return to Homepage') ?></a>
				</div> 
			</div>
		
		<?php  }elseif( $unsubscribed ){ ?>
		
			<div>
				<p><?php echo  t('You have been unsubscribed. ') ?></p>
				
				<div style="margin-top:16px">
				<a href="<?php echo View::url('/')?>"><?php echo  t('Return to Homepage') ?></a>
				</div> 
			</div>
		
		<?php  }elseif( !$validToken ){ ?> 
			
			<?php  if( !$_REQUEST['mlt']){ ?>
			
				<p><?php echo  t('To unsubscribe, please click the unsubscribe link from the bottom of a mailing list email.') ?></p> 
					
			<?php  }elseif( $_REQUEST['manage_subscription'] && (!isset($_REQUEST['gID']) || !count($_REQUEST['gID']) )){ ?>			
					
				<p><?php echo  t('You must select at least one mailing list group.') ?></p> 	
					
			<?php  }else{ ?>
			
				<p><?php echo  t('Have you already unsubscribed? The validation token is invalid. ') ?></p> 
				
			<?php  } ?>
			
			
		<?php  }else{ 
		
		?> 
		<form action="<?php echo  View::url( $c->getCollectionPath(), $mode ) ?>" onsubmit="return ManageSubscriptions.submitForm()" method="post" >
		
			<input name="manage_subscription" type="hidden" value="1" /> 
			<input name="debug" type="hidden" value="<?php echo intval($_REQUEST['debug'])?>" /> 
			<input name="mlm" type="hidden" value="<?php echo intval($_REQUEST['mlm'])?>" /> 
			<input name="uID" type="hidden" value="<?php echo intval($_REQUEST['uID'])?>" /> 
			<input name="bID" type="hidden" value="<?php echo intval($_REQUEST['bID'])?>" /> 
			<input name="mluID" type="hidden" value="<?php echo intval($_REQUEST['mluID'])?>" /> 
			<input name="mlt" type="hidden" value="<?php echo  htmlentities( $_REQUEST['mlt'], ENT_QUOTES, 'UTF-8') ?>" /> 
			<input name="subscribe" type="hidden" value="<?php echo intval($_REQUEST['subscribe'])?>" /> 
			
			<?php  if($_REQUEST['subscribe']){ ?>
				<p><?php echo  t('To subscribe to this mailing list, just click the subscribe button below.') ?></p> 

				<?php  foreach($requested_gIDs as $gID){ ?> 
					<input name="gID[]" type="hidden" value="<?php echo $gID ?>" /> 
				<?php  } ?>		
							
			<?php  }else{ ?>
			
				<p><?php echo  t('Click the checkbox next to each mailing list you want to unsubscribe from: ') ?></p>
				
				<?php  
				$validGIDs=0;
				$lockDownGIDs = explode(',',Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS')); 
				foreach($requested_gIDs as $gID){ 
					$g = Group::getById(intval($gID));
					if( !is_object($g) || $g->getGroupName()==t('Administrators') || $g->getGroupName()=='Administrators' ) continue; 
					$validGIDs++;
					?> 
					<div> 
						<input class="groupIdCheckbox" name="gID[]" type="checkbox" value="<?php echo intval($gID) ?>" />
						<?php 
						if( in_array($gID,$lockDownGIDs) ){
							echo t('Unsubscribe from');
						}else{
							echo t('Opt-out of mailings from ');
						} 
						echo ' '.t('"').$g->getGroupName().t('"');
						?>
					</div>
				<?php  } ?>	
				
				<?php  if( intval(Config::get('TONY_MAILING_LIST_BLACKLIST_UNSUBSCRIBE')) ){ ?>
				<div style="margin-top:16px; <?php  if($validGIDs){ ?>opacity:.75; filter: alpha(opacity=75); <?php  } ?>">
					<input id="blackListUser" name="blackListUser" type="checkbox" value="1" onclick="ManageSubscriptions.blacklist(this)" onchange="ManageSubscriptions.blacklist(this)" /> 
					<?php echo  t("Unsubscribe me permanently from all future mailings from this site.") ?>
				</div> 
				<?php  } ?>
										
			<?php  } ?>		
			
			
			
			
			<div style="margin-top:24px;">
				<a href="<?php echo View::url('/') ?>"><input name="cancel" type="button" value="<?php echo t('&laquo; Cancel') ?>" /></a> &nbsp;
				<input name="submit" type="submit" value="<?php echo  ($_REQUEST['subscribe']) ? t('Subscribe &raquo;'):t('Unsubscribe &raquo;') ?>" />
			</div>
		</form>
		<?php  } ?>
		
	</div>

</div>