<?php  

defined('C5_EXECUTE') or die(_("Access Denied.")); 

Loader::model('file_attributes'); 
Loader::model('tony_mailing_list','tony_mailing_list'); 

class TonyMailingListBlockController extends BlockController { 

	protected $btTable = 'btTonyMailingList';
	protected $btInterfaceWidth = "500";
	protected $btInterfaceHeight = "350"; 
	
	protected $btCacheBlockRecord=1; 
	protected $btCacheBlockOutput=0;
	protected $btCacheBlockOutputTimeout=43200;
	protected $btCacheBlockOutputForRegisteredUsers=0;
	
	/** 
	 * Used for localization. If we want to localize the name/description we have to include this
	 */
	public function getBlockTypeDescription() {
		return t("Allow people to subscribe/unsubscribe from mailing list");
	}
	
	public function getBlockTypeName() {
		return t("Mailing Subscriptions");
	}
	
	public function getJavaScriptStrings() {
		return array();
	} 
	
	public function save($args) { 
		$db = Loader::db();  
		
		$args['allowUnregistered']=intval($args['allowUnregistered']);
		
		$args['showCheckboxes']=intval($args['showCheckboxes']); 
		
		$args['validateEmail']=intval($args['validateEmail']); 
		
		$args['attrsRequired']=intval($args['attrsRequired']);  
		
		if(!is_array($args['gID'])) $args['gIDs']='';
		else $args['gIDs']=join(',',$args['gID']);
		
		$userAttrs=$args['userAttrs'];

		if(!is_array($userAttrs)) $userAttrs=array();
		$cleanUserAttrs=array();
		foreach($userAttrs as $userAttr){
			$cleanUserAttrs[]=intval($userAttr);
		} 
		$args['userAttrs']=join(',',$cleanUserAttrs);
		
		parent::save($args); 
	} 
	
	function on_page_view(){ 
		$htmlHelper = Loader::helper('html');
		foreach(explode(',',$this->userAttrs) as $attrID){
			$attrKey = UserAttributeKey::getByID(intval($attrID)); 
			if(!is_object($attrKey)) continue; 
			$attrType = $attrKey->getAttributeType();  
			$attrTypeController = $attrType->getController(); 
			if($attrType->getAttributeTypeHandle()=='address'){
				$this->addHeaderItem($htmlHelper->javascript($attrTypeController->attributeType->getAttributeTypeFileURL('country_state.js')));
				$this->addHeaderItem($htmlHelper->javascript($attrTypeController->getView()->action('load_provinces_js')));
			}elseif($attrType->getAttributeTypeHandle()=='date_time'){
				$this->addHeaderItem($htmlHelper->javascript('jquery.ui.js'));
				$this->addHeaderItem($htmlHelper->css('jquery.ui.css'));
				$this->addHeaderItem($htmlHelper->css('ccm.calendar.css'));
			}
		} 
	}
	
	function view(){ 
		$b = $this->getBlockObject();  
		
		//error_reporting(E_ALL ^ E_NOTICE); 
		//ini_set('display_errors',1);
		
		$token = Loader::helper('validation/token'); 
		
		if( (!defined('MAILING_LIST_SUBSCRIBE_TOKEN_DISABLED') || !MAILING_LIST_SUBSCRIBE_TOKEN_DISABLED) && 
			isset($_POST['mailing_list_subscribe']) && intval($_POST['mailing_list_subscribe'])==intval($this->bID) && !$token->validate("mailing_list_subscribe") ){
		
			$this->set('errorMsg',  $token->getErrorMessage()  );
		
		}elseif( isset($_POST['mailing_list_subscribe']) && intval($_POST['mailing_list_subscribe'])==intval($this->bID) ){
			
			$txt = Loader::helper('text');
			$stringsHelper = Loader::helper('validation/strings'); 
			$subscribeGIDs = $_POST['subscribeGIDs']; 
			if( !is_array( $subscribeGIDs) ) $subscribeGIDs = array(); 
			$gIDs=explode(',',$this->gIDs); 
			
			$u=new User(); 
			if( is_object($u) &&  intval($u->uID) ) $ui = UserInfo::getById($u->uID);
			
			//load attribute keys from post
			$postedAttrsData=$_POST['akID'];
			$invalidAttrs=array();
			$nonUserAttrData=array();  
			
			//loop through block's collected user attributes 
			foreach(explode(',',$this->userAttrs) as $akID){  
				$attrKey = UserAttributeKey::getByID(intval($akID)); 
				if(!is_object($attrKey)) continue; 
				$attrType = $attrKey->getAttributeType();
				$attrTypeController = $attrType->getController();
				$postedAttrData=$postedAttrsData[$akID];
				//$attrTypeController->setRequestArray($_POST["akID"] );
				$attrTypeController->setAttributeKey($attrKey); 
				//validate attribute data 
				if(!$this->attrsRequired || in_array($attrType->getAttributeTypeHandle(),array('boolean','date_time')) || !method_exists($attrTypeController,'validateForm') || $attrTypeController->validateForm($postedAttrData)){
					if(is_object($ui)){
						$attrKey->saveAttributeForm($ui);
					}else{
						$nonUserAttrData[$attrKey->getAttributeKeyHandle()]=$postedAttrData;
					}
				}else{ 
					
					$invalidAttrs[]=$akID; 
				}
			}  
			
			
			$submittedEmail = trim($_REQUEST['email']);  
			if($submittedEmail || (defined('MAILING_LIST_ALWAYS_SHOW_EMAIL_FIELD') && MAILING_LIST_ALWAYS_SHOW_EMAIL) ) $u=null;
			
			if(  (!is_object($u) || !intval($u->uID))  && !$this->allowUnregistered ){
				$this->set('errorMsg', t('Please login to subscribe'));
				
			}elseif( (!is_object($u) || !intval($u->uID))  && !$stringsHelper->email($submittedEmail) ){
				$this->set('errorMsg', t('Invalid email address'));
				
			}elseif( count($invalidAttrs) ){
				$this->set('errorMsg', t('Please complete all required fields.'));
				
			}else{ 
			
				//if this is an unregistered user, load nonUser object
				if( (!is_object($u) || !intval($u->uID)) ){  
					$nonUser = TonyMailingListNonUser::getByEmail($submittedEmail);  
					if(!$nonUser){
						$nonUser = new TonyMailingListNonUser( array('email'=>$submittedEmail,'attrData'=>$nonUserAttrData) ); 
						$nonUser->create();
					}else{
						$oldAttrData = $nonUser->getAttrData();
						foreach($nonUserAttrData as $key=>$val)
							$oldAttrData[$key]=$val; 
						$nonUser->setAttrData($oldAttrData); 
						$nonUser->update();
					}
					
					//is there a registered user record with this same email address? 
					$ui = UserInfo::getByEmail( $submittedEmail );
				} 			
				
			
				//only only entrance into groups that are allowed by this block. 
				$nonUserUnsubscribeGIDs = array(); 
				$lockDownGIDs = explode(',',Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS'));  
				
				$hasSubscribedEmail = ''; //used for welcome email 
				
				foreach($gIDs as $gID){ 
				
					$g=Group::getById($gID); 
					
					 //options set on the mailing list settings page
					if( !in_array($gID,$lockDownGIDs) ) continue;					
					
					//and make sure they can't get in the administrators group!
					if( !is_object($g) ||  $g->getGroupName()==t('Administrators') || strtolower($g->getGroupName())=='administrators' ) continue;  
					
					$hasValidGroup=1; 
				
					//is this a logged in user, or is email validation disabled 
					if( (is_object($u) && intval($u->uID)) || (is_object($ui) && !$this->validateEmail) ){ 
					
						if( in_array($gID,$subscribeGIDs) ){ 
						
							TonyMailingList::addRegisteredUserToGroup( $ui, $g);
							
							$hasSubscribedEmail = $ui->getUserEmail(); 
							 
						}else{
							
							TonyMailingList::removeRegisteredUserFromGroup( $ui, $g);
						}
						
					}
					
					if($nonUser){ 
						if( in_array($gID,$subscribeGIDs) ){ 
						
							//does this block require that non-user emails be validated on mailing list sign-up? 
							
							//if so, check that there isn't a registered user with the same email address already subscribed.
							if( is_object($existingEmailUser) && $existingEmailUser->inGroup($g) ){  
								
								$registerUserInGroup=1; 
								$this->set( 'registerUserInGroup', $registerUserInGroup); 
							
							//require validation is that option is on, or if the user has previously been blacklisted 
							}elseif( $this->validateEmail || $nonUser->blacklist ){ 
								$nonUserSubscribeGIDs[]=$gID;
								$nonUserSubscribeGroupNames[]=$g->getGroupName();
								
							}else{
								$nonUser->addGroup($gID);
								 
								$hasSubscribedEmail = $submittedEmail; 
							}
							 	 
						}elseif( in_array($gID,$nonUser->groupIds) ){ 
							//keep track of what groups the user is trying to unsubscribe from (see below)
							$nonUserUnsubscribeGIDs[]=$gID;
							$nonUserUnsubscribeGroupNames[]=$g->getGroupName();
						}
					}
					
				} //end loop
				
				
				if(!$hasValidGroup){
					$this->set('errorMsg',t('Oops, no groups were selected.'));
				}else{			
				
				
					if($nonUser){
						
						//save the added groups
						$nonUser->update(); 
							
						$fromAddress = TonyMailingList::getSubscriptionsFromEmail();
						if( !$stringsHelper->email($fromAddress) ) 
							throw new Exception( t('Invalid email address set for the global variable MAILING_LIST_SUBSCRIPTIONS_EMAIL: ').$fromAddress ); 							
						
						//SUBSCRIBE 
						
						//do we need to send out an email to validate the non-user's email address?				
						if( count($nonUserSubscribeGIDs)  ){ 
							//instead of just adding the group, we send out an email with the subscribe link, to authenticate the user
							$subscribeLink = TonyMailingList::subscribeLink( $nonUser->email, $nonUserSubscribeGIDs, 0, $nonUser->mluID, intval($this->bID) ); 
							
							$subscribeText = t('To subscribe to the groups "') . join('", "',$nonUserSubscribeGroupNames) . '" ';
							$subscribeText .= t('visit the link:'). "\r\n". $subscribeLink;
							
							$mh = Loader::helper('mail');
							$mh->to(  $nonUser->email );
							$mh->from( TonyMailingList::getSubscriptionsFromEmail() );  
							$mh->setSubject( t('Subscribe to mailing list') );
							$mh->setBody( $subscribeText  ); 
							$mh->sendMail();	
							
							$this->set('nonUserSubscribing',1);
							
						} 
						
						
						//UNSUBSCRIBE 
						//is the user unsubscribing from some groups? 
						if( count($nonUserUnsubscribeGIDs) ){ 
							//instead of just removing the group, we send out an email with the unsubscribe link, to authenticates the user
							$unsubscribeLink = TonyMailingList::unsubscribeLink( $nonUser->email, $nonUserUnsubscribeGIDs, 0, $nonUser->mluID ); 
							
							$unsubscribeText = t('To unsubscribe from the groups "') . join('", "',$nonUserUnsubscribeGroupNames) . '" ';
							$unsubscribeText .= t('visit the link:'). "\r\n". $unsubscribeLink;
							
							$mh = Loader::helper('mail');
							$mh->to(  $nonUser->email );
							$mh->from( TonyMailingList::getSubscriptionsFromEmail() );  
							$mh->setSubject( t('Unsubscribe from mailing list') );
							$mh->setBody( $unsubscribeText  ); 
							$mh->sendMail();	
							
							$this->set('nonUserUnsubscribing',1);
						}
					}
					
					
					//Send Subscription Auto-Responder Welcome Email 
					if( $hasSubscribedEmail ) 	
						TonyMailingList::sendAutoRespondEmail( $hasSubscribedEmail ); 
							
				
					$this->set('subscribed',1);
				}
			
			}
			
		} 
		
	}
}

?>
