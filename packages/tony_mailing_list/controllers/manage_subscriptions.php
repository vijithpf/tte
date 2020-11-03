<?php 

defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('tony_mailing_list','tony_mailing_list'); 

Loader::model('tony_mailing_list_stats','tony_mailing_list'); 

class ManageSubscriptionsController extends Controller {
	
	public function view(){
		
		if( $this->validateToken() )
			$this->set('validToken',1);
		
	} 
	
	public function checkBlocksGroups( $gIDs=array(), $bID=0 ){
		
		//this entire process is only necessary for unregistered users
		
		$b = Block::getById($bID);
		if( !is_object($b) ) return array();
		
		$bi = $b->getInstance(); 
		if( !is_object($bi) || !$bi->allowUnregistered ) return array(); 
		
		//if it's not a global block, make sure the user can access that page  
		if( !$b->isGlobal() ){
			$c = $b->getBlockCollectionObject();
			if( !is_object($c) ) return array();
			$cp = new Permissions($c);
			if( !is_object($cp) || !$cp->canRead() ) return array(); 
		}  
		
		//now compare the request gIDs with the block's authorized gIDs 
		$blocksGIDs = explode(',',$bi->gIDs);
		$cleanGIDs = array();
		foreach($gIDs as $gID) 
			if( in_array($gID,$blocksGIDs) ) $cleanGIDs[]=$gID;
		return $cleanGIDs;
	}
	
	public function subscribe(){
		
		//double check the validation token
		if( !$this->validateToken() || !$this->nonUser ){
			$this->view();
			$this->set('validToken',0);
			return;
		} 
		
		$bID=intval($_REQUEST['bID']); 
		
		$lockDownGIDs = explode(',',Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS')); 		
		
		//make sure the groups this user is trying to subscribe to are authorized 
		$gIDs = $this->checkBlocksGroups( $this->getGIDarray(), $bID); 
		
		$hasSubscribed=0; //used for welcome email  
		
		foreach( $gIDs as $gID ){
			$g = Group::getById($gID);
			if( !is_object($g) || $g->getGroupName()==t('Administrators') || $g->getGroupName()=='Administrators' ) continue; 
			
			if( !in_array($gID,$lockDownGIDs) ) continue;  

			//subscribe non-registered user  
			$this->nonUser->blacklist = 0 ;   
			$this->nonUser->addGroup($gID);  		
			
			//this is redundant, but if there's also a registered user with this validated email, add them to the group 
			if( is_object($this->ui) ){ 
				TonyMailingList::addRegisteredUserToGroup( $this->ui, $g);  
			} 
			
			$hasSubscribed = 1; 
		}
		
		$this->nonUser->update(); 
		
		if($hasSubscribed) 
			TonyMailingList::sendAutoRespondEmail( $this->subscriberEmail );  
		
		$this->set('subscribed',1);
	}

	public function unsubscribe(){
		
		//double check the validation token
		if( !$this->validateToken() ){
			$this->view();
			return;
		} 
		
		$lockDownGIDs = explode(',',Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS')); 
		
		$this->nonUser->last_unsubscribe_date = date('U');
		
		foreach( $this->getGIDarray() as $gID ){
			$g = Group::getById($gID);
			
			if( !is_object($g) || $g->getGroupName()==t('Administrators') || $g->getGroupName()=='Administrators' ) continue; 
			
			if( $this->ui ){ 
			
				//for registered users, we only want to remove them from this group if the subscription process is enabled on it
				if( in_array($gID,$lockDownGIDs) ){ 
				
					TonyMailingList::removeRegisteredUserFromGroup( $this->ui, $g);
					
				}else{ //otherwise we'll just add an opt-out flag instead
					$optOutStr=$this->ui->getAttribute('mailing_list_optout_gIDs');
					if( strlen($optOutStr) ) $optOutGIDs = explode(',',$optOutStr);
					else $optOutGIDs = array();
					if( !in_array($gID,$optOutGIDs) ) $optOutGIDs[] = $gID;
					$this->ui->setAttribute('mailing_list_optout_gIDs',join(',',$optOutGIDs)); 
				}
				
				$this->ui->setAttribute( 'last_unsubscribe_date', date("Y-m-d H:i:s") );
				
				$unsubscribeData = unserialize($this->ui->getAttribute( 'unsubscribe_data' ));
				$unsubscribeData['groups'][$gID]=date('U');
				$this->ui->setAttribute( 'unsubscribe_data', serialize($unsubscribeData) );
					
				//just incase there's also a non-registered user record with this email tied to this group
				$this->nonUser = TonyMailingListNonUser::getByEmail( $this->ui->getUserEmail() ); 
			}
			
			if( $this->nonUser ){ 			
			
				//unsubscribe non-registered user    
				$this->nonUser->removeGroup($gID); 
				$this->nonUser->update(); 
			}
		} 
		
		if( intval($_REQUEST['blackListUser']) ){ 
			if( $this->ui ){  
				$this->ui->setAttribute( 'disable_emails', 1 ); 
				$this->ui->setAttribute( 'last_unsubscribe_date', date("Y-m-d H:i:s") );
				$unsubscribeData = unserialize($this->ui->getAttribute( 'unsubscribe_data' ));
				$unsubscribeData['blacklist']=date('U');
				$this->ui->setAttribute( 'unsubscribe_data', serialize($unsubscribeData) );
			}
			if( $this->nonUser /*&& get_class($this->nonUser)=='TonyMailingListNonUser'*/ ){
				
				//keep track of blacklist date
				$unsubscribeData = $this->nonUser->getUnsubscribeData(); 
				$unsubscribeData['blacklist']=date('U');
				$this->nonUser->setUnsubscribeData($unsubscribeData);
				
				$this->nonUser->blacklist=1;
				$this->nonUser->groupIds = array(); 
				$this->nonUser->update(); 
			}
		}
		
		//track unsubscribed action on mailing 
		$mailingStats = new TonyMailingListStats();
		$mailingStats->trackUnsubscribe( intval($_REQUEST['mlm']), intval($_REQUEST['mluID']), intval($_REQUEST['uID']) );
		
		if($mailingStats->errorMsg && $_REQUEST['debug']){ 
			echo $mailingStats->errorMsg;
			die;
		}
		
		$this->set('unsubscribed',1);
	}
	
	public function getGIDarray(){
		$requested_gIDs = $_REQUEST['gID']; 
		if(is_array($requested_gIDs))  $requested_gIDs=join(',',$requested_gIDs);
		if(strstr($requested_gIDs,',')) $requested_gIDs=explode(',',$requested_gIDs);
		if( !is_array($requested_gIDs) ) $requested_gIDs=array($requested_gIDs);
		$cleanGIDs=array();
		foreach($requested_gIDs as $gID) $cleanGIDs[]=intval($gID);	 
		$cleanGIDs = array_unique($cleanGIDs);
		$this->set( 'requested_gIDs', $cleanGIDs); 
		return $cleanGIDs;
	}
	
	public function validateToken(){ 
		$gIDs=$this->getGIDarray();	
	
		if( !count($gIDs) || !$_REQUEST['mlt'] ) return false;	 
		
		$unsubscribeGroups=array();
		foreach($gIDs as $gID){
			$g = Group::getById($gID);
			if( !is_object($g) || $g->getGroupName()==t('Administrators') || $g->getGroupName()=='Administrators' ) continue; 
			$unsubscribeGroups[]=$g; 
		} 
		
		//get registered user email
		if( intval($_REQUEST['uID']) ){  
			$this->ui = UserInfo::getById( intval($_REQUEST['uID']) );
			if( !is_object($this->ui) ) return false; 
			$email=$this->ui->getUserEmail(); 
			
		//get unregistered user email
		}elseif( intval($_REQUEST['mluID']) ){
			$this->nonUser = TonyMailingListNonUser::getById(intval($_REQUEST['mluID'])); 
			$email=$this->nonUser->email; 
				 
			//is there a registered user record with this same email address? 
			$this->ui = UserInfo::getByEmail( $this->nonUser->email ); 			 
			 
		}else return false; 
		
		$emailToken = TonyMailingList::subscriptionsToken($email);
		
		$this->subscriberEmail = $email; 
		
		if($emailToken==$_REQUEST['mlt']) return true; 
		else return false;
	}
	
}

?>