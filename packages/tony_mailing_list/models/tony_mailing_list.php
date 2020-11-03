<?php  

Loader::model('tony_mailing_list_mailing','tony_mailing_list'); 


class TonyMailingList { 
	
	public static $limitedLogCount=5;

	public static function unsubscribeLink($email='',$gIDs=array(),$uID=0,$mluID=0,$mlmID=0){
		$emailToken = TonyMailingList::subscriptionsToken($email); 
		return BASE_URL.View::url('/manage_subscriptions?mlt='.$emailToken.'&uID='.intval($uID).'&gID='.join(',',$gIDs).'&mluID='.intval($mluID).'&mlm='.intval($mlmID));
	}
	
	
	public static function subscribeLink($email='',$gIDs=array(),$uID=0,$mluID=0,$bID=0){  
		$emailToken = TonyMailingList::subscriptionsToken($email); 
		return BASE_URL.View::url('/manage_subscriptions?subscribe=1&mlt='.$emailToken.'&uID='.intval($uID).'&gID='.join(',',$gIDs).'&mluID='.intval($mluID)).'&bID='.intval($bID);
	}	

	public static function subscriptionsToken($email){
		//encode with randomly generated password string, created during package install   
		$mailingListSalt = Config::get('MAILING_LIST_TOKEN_SALT');
		return md5( $email.':'.$mailingListSalt ); 
	} 
	
	public static function getSubscriptionsFromEmail(){
		$email = Config::get('MAILING_LIST_SUBSCRIPTIONS_EMAIL');  
		if ( strlen($email) ) {
			return $email;
		}elseif( defined('MAILING_LIST_SUBSCRIPTIONS_EMAIL') && strlen(MAILING_LIST_SUBSCRIPTIONS_EMAIL) ){  
			return MAILING_LIST_SUBSCRIPTIONS_EMAIL;
		} else { 
			//get domain from base 
			//$parsedURL = parse_url($_SERVER['HTTP_HOST']);  
			$serverName=$_SERVER['SERVER_NAME']; 
			if(!strstr($serverName,'.')) $serverName.='.com';
			return 'DoNotReply@'. str_replace(array('www.','WWW.'),'',$serverName);
		}
	} 
	
	public static function authorizedGIDs($gIDs=array()){
		$enableMailingsGIDs = explode(',',Config::get('TONY_MAILING_LIST_ENABLE_MAIL_GIDS')); 
		$cleanGIDs=array(); 
		foreach($gIDs as $gID){
			if( !in_array($gID,$enableMailingsGIDs) ) continue;  
			$cleanGIDs[]=intval($gID);
		}
		return $cleanGIDs; 
	}
	
	public static function getRecipientGroups( $gIDs=array() ){
		$groups=array();
		if(is_array($gIDs)) foreach( $gIDs as $gID ){
			if( !intval($gID) ) continue;
			$g = Group::getById( intval($gID) );
			if( is_object($g) ) 
				$groups[]=$g;
		}
		return $groups; 
	}	
	
	//mode = groups || all;  
	public static function getRecipientEmails( $mode='groups', $gIDs=array(), $whiteListAttrId=0, $blackListAttrId=0 ){
		
		if( is_int($gIDs) ) $gIDs=array($gIDs);
		if( !is_array($gIDs) ) $gIDs=array(0);
		
		$recipientUIs=array();
		Loader::model('user_list');
		$userList = new UserList(); 
		
		if( defined('MAILING_LIST_SHOW_INACTIVE') && intval(MAILING_LIST_SHOW_INACTIVE) ) 
			$userList->showInactiveUsers=1; 
		
		$userList->sortBy('uEmail', 'desc');
		
		if( $mode=='groups' ){ 
		
			$gIDs=TonyMailingList::authorizedGIDs($gIDs);
			if( !count($gIDs) ) return array(); 
		
			//get to addresses from registered users
			$userList->addToQuery("left join UserGroups ugCustom on ugCustom.uID = u.uID"); 
			$userList->filter(false, 'ugCustom.gID in ('. join(',',$gIDs) .')'); 
			
			//now get the unregistered users email addresses 
			if( !intval($whiteListAttrId) && !intval($blackListAttrId) )
				$nonUsers = TonyMailingListNonUser::getGroupsEmails($gIDs);
				
			
			//is one of the groups the administrators group?
			foreach($gIDs as $gID){
				$g = Group::getById($gID); 
				if( is_object($g) && ($g->getGroupName()==t('Administrators') || strtolower($g->getGroupName())=='administrators') ){
					$sendingToAdmins=1; 
					$adminGroup = $g;
					break;	
				}
			}
		}elseif( $mode=='all' && !intval(Config::get('TONY_MAILING_LIST_ALL_USERS_MAILING')) ){
			return array();
		}elseif( $mode=='all' ){
			
		}elseif( $mode!='all' ){
			throw new Exception('invalid recipients setting');
		} 
		
		$userList->setItemsPerPage(1000000000); 
		$recipientUIs = $userList->get(1000000000);
		
		$whiteListAttrHandle = UserAttributeKey::getByID( $whiteListAttrId );
		$blackListAttrHandle = UserAttributeKey::getByID( $blackListAttrId );
		
		//add registered recipients 
		$recipientEmails=array();
		$uniqueEmails=array();
		foreach( $recipientUIs as $recipientUI ){ 
			//exclude any user on the blacklist
			if( intval($recipientUI->getAttribute('disable_emails')) ) continue; 
			
			if( intval($whiteListAttrId) && !intval($recipientUI->getAttribute( $whiteListAttrHandle )) ) continue; 
			if( intval($blackListAttrId) && intval($recipientUI->getAttribute( $blackListAttrHandle )) ) continue; 
			
			if( in_array( strtolower($recipientUI->getUserEmail()),$uniqueEmails) ) continue;  
			
			//has the user opted out of all the selected groups?
			$optOutStr=$recipientUI->getAttribute('mailing_list_optout_gIDs');
			//test that at least one of the groups is valid
			if( is_array($gIDs) && count($gIDs) && $mode=='groups' && strlen($optOutStr) ){
				$optOutGIDs=explode(',',$optOutStr);
				$validGroupFound=0;
				foreach($gIDs as $gID){
					if( intval($gID) && !in_array(intval($gID),$optOutGIDs) ){
						$validGroupFound=1;
						break;
					} 
				}
				if(!$validGroupFound) continue;
			}
			
			$uniqueEmails[] = strtolower($recipientUI->getUserEmail());
			
			$recipUser = $recipientUI->getUserObject();
			$gIDs=array();
			if( is_array($recipUser->uGroups) ) foreach( $recipUser->uGroups as $gID=>$gName ){ 
				$gIDs[]=$gID; 
			}			
			
			$recipientEmails[]=array( 'email'=>$recipientUI->getUserEmail(), 'name'=>'', 'uID'=>$recipientUI->getUserID(), 'mluID'=>0 , 'ui'=>$recipientUI, 'gIDs'=>$gIDs ); 
		}
		
		//add non registered recipients 
		if(is_array($nonUsers)) foreach($nonUsers as $row){ 
			if( in_array(strtolower($row['email']),$uniqueEmails) ) continue;
			
			$attrData = unserialize($row['attrDataRaw']); 
			if(!is_array($attrData)) $attrData=array(); 
			
			$recipientEmails[]=array( 'email'=>$row['email'], 'name'=>'', 'uID'=>0, 'attrDataRaw'=>$row['attrDataRaw'], 'mluID'=>intval($row['mluID']), 'attrData'=>$attrData, 'gIDs'=>explode(',',$row['gIDs']) ); 
		} 
		
		return $recipientEmails; 
	}
	
	
	static public function getUnsubscribers(  ){
		
		$recipientEmails=array(); 
		
		//get unsubscribed non-registered users 
		$db = Loader::db();
		$nonUsers = $db->getAll('SELECT * FROM TonyMailingListNonUsers WHERE (unsubscribe_data IS NOT NULL AND last_unsubscribe_date IS NOT NULL  AND last_unsubscribe_date != 0) ORDER BY email');
		if(is_array($nonUsers)) foreach($nonUsers as $row){ 
			//if has value for unsubscribed groups attribute, or blacklist attribute
			$unsubscribeData = unserialize($row['unsubscribe_data']);
			if( !count($unsubscribeData['groups']) && !$unsubscribeData['blacklist'] ) continue; 	
			$recipientEmails[]=array( 'email'=>$row['email'], 'name'=>'', 'uID'=>0, 'attrDataRaw'=>$row['attrDataRaw'], 'mluID'=>intval($row['mluID']), 'attrData'=>$attrData, 'gIDs'=>explode(',',$row['gIDs']), 'unsubscribeData'=>$unsubscribeData, 'last_unsubscribe_date'=>$row['last_unsubscribe_date'] ); 
		}
		
		//get unsubscribed registered users 
		Loader::model('user_list');
		$userList = new UserList(); 
		$userList->setItemsPerPage(1000000000); 
		$recipientUIs = $userList->get(1000000000);
		foreach($recipientUIs as $recipientUI){ 
			//if has value for unsubscribed groups attribute, or blacklist attribute 
			$unsubscribeData = unserialize( $recipientUI->getAttribute('unsubscribe_data') ); 
			$last_unsubscribe_date = $recipientUI->getAttribute('last_unsubscribe_date'); 
			
			if( !count($unsubscribeData['groups']) && !$unsubscribeData['blacklist'] ) continue; 
			
			$recipUser = $recipientUI->getUserObject();
			$gIDs=array();
			if( is_array($recipUser->uGroups) ) foreach( $recipUser->uGroups as $gID=>$gName ){ 
				$gIDs[]=$gID; 
			}
			
			$recipientEmails[]=array( 'email'=>$recipientUI->getUserEmail(), 'name'=>'', 'uID'=>$recipientUI->getUserID(), 'mluID'=>0 , 'ui'=>$recipientUI, 'gIDs'=>$gIDs, 'unsubscribeData'=>$unsubscribeData, 'last_unsubscribe_date'=>$last_unsubscribe_date ); 
		}
		
		return $recipientEmails; 
	}
	
	
	static public function getCurlCheckURL($mlid=0) { 
		$auth= TonyMailingList::getServicesAuthKey();
		return BASE_URL . View::url('/tools/packages/tony_mailing_list/services/?mode=curlCheck&auth='.$auth.'&mlid='.intval($mlid)); 
	} 

	static public function getSendPendingURL($mlid=0) {  
		$auth= TonyMailingList::getServicesAuthKey();
		return BASE_URL . View::url('/tools/packages/tony_mailing_list/services/?mode=send&auth='.$auth.'&mlid='.intval($mlid)); 
	} 
	
	static public function getServicesAuthKey(){
		return md5('MAILING_LIST_AUTH:'.Config::get('MAILING_LIST_TOKEN_SALT'));
	}
	
	static public function sendAllMailings(){
		$mailings = TonyMailingListMailing::getList(true);
		$startTime=time();
		$mailingsResults=array();
		foreach( $mailings as $mailing ){ 
			if( $mailing->getStatus()=='draft' ) continue; 
			$result = $mailing->send($startTime);
			$mailingsResults['mailings']=$result;
			if( !$result['success'] ){
				$mailingsResults['success']=0;
				return $mailingsResults; 
			}
			$startTime=$result['startTime'];
			$mailingsResults['success']=1;
		}	
		return $mailingsResults; 
	}
	
	static public function cURL_Installed(){
		//is curl installed? 
		if( !function_exists('curl_version') ) return 0;  
		$curlData=@curl_version(); 
		return (!defined('MAILING_LIST_CURL_DISABLED') && is_array($curlData) && intval($curlData['version_number'])>0) ? 1 : 0;  
	}
	
	static public function getMailerObject(){ 
		//does the mail helper class have a method to get & setup the zend mail?
		$mailHelper = Loader::helper('mail'); 
		
		if( method_exists( $mailHelper,'getMailerObject' ) ){ 
			return MailHelper::getMailerObject();
		}else{ 
			Loader::library('3rdparty/Zend/Mail');
			$response = array();
			$response['mail'] = new Zend_Mail(APP_CHARSET);
		
			if (MAIL_SEND_METHOD == "SMTP") {
				Loader::library('3rdparty/Zend/Mail/Transport/Smtp');
				$username = Config::get('MAIL_SEND_METHOD_SMTP_USERNAME');
				$password = Config::get('MAIL_SEND_METHOD_SMTP_PASSWORD');
				$port = Config::get('MAIL_SEND_METHOD_SMTP_PORT');
				if ($username != '') {
					$config = array('auth' => 'login', 'username' => $username, 'password' => $password);
					if ($port != '') {
						$config['port'] = $port;
					}
					$transport = new Zend_Mail_Transport_Smtp(Config::get('MAIL_SEND_METHOD_SMTP_SERVER'), $config);					
				} else {
					$transport = new Zend_Mail_Transport_Smtp(Config::get('MAIL_SEND_METHOD_SMTP_SERVER'));					
				}
				
				$response['transport']=$transport;
			}	
			return $response;
		}
	}
	
	public static function getHeaderHTML(){
		$header = Config::get('TONY_MAILING_LIST_CUSTOM_TEMPLATE_HEADER');
		if( strlen($header) && intval(Config::get('TONY_MAILING_LIST_CUSTOM_TEMPLATE')) ) return $header;
		return TonyMailingList::getDefaultHeaderHTML();
	}
	
	public static function getFooterHTML(){
		$footer = Config::get('TONY_MAILING_LIST_CUSTOM_TEMPLATE_FOOTER');
		if( strlen($footer) && intval(Config::get('TONY_MAILING_LIST_CUSTOM_TEMPLATE')) ) return $footer;		
		return TonyMailingList::getDefaultFooterHTML();
	}	
	
	public static function getDefaultHeaderHTML(){
		return '<body style="margin:0"><table width="98%" height="auto" border="0" cellpadding="0" cellspacing="0"><tr><td bgcolor="#ffffff" valign="top">'.
			   '<table width="600" border="0" cellpadding="0" cellspacing="0" align="center"><tr><td bgcolor="#ffffff" width="600">';
	}
	
	public static function getDefaultFooterHTML(){
		return '</td></tr></table></td></tr></table></body>';
	}
	
	public static function getResponderBodyHTML(){ 
		return trim(Config::get('TONY_MAILING_LIST_AUTO_RESPONDER_BODY'));
	}
	
	public static function sendOnCreation(){
		return (Config::get('TONY_MAILING_LIST_SEND_ON_CREATE') && TonyMailingList::cURL_installed()) ;	
	}
	
	//just for registered users
	public static function addRegisteredUserToGroup($ui,$g){
		if( !is_object($ui) || !is_object($g) ) return false;
		
		$ui->getUserObject()->enterGroup( $g, 'Mailing List Subscription' );  
							 
		$gID = $g->getGroupID();					 
							 
		 //if user is resubscribing, then we should remove the opt-out flag if there is one.  
		 if( is_object($ui) ){ 
			$optOutGIDs=explode(',',$ui->getAttribute('mailing_list_optout_gIDs')); 
			$cleanOptOutGIDs=array();
			foreach($optOutGIDs as $optOutGID){ 
				if(intval($optOutGID)!=intval($gID))
					$cleanOptOutGIDs[]=intval($optOutGID);
			}
			$ui->setAttribute('mailing_list_optout_gIDs',join(',',$cleanOptOutGIDs));
			
			//if registered user is resubscribing, removing blacklist attribute 
			$ui->setAttribute('disable_emails', 0); 
			
			//update unsubscribe data 
			$unsubscribeData = unserialize($ui->getAttribute( 'unsubscribe_data' ));
			unset($unsubscribeData['blacklist']);
			if(is_array($unsubscribeData['groups'])) 
				unset($unsubscribeData['groups'][$gID]);
			$ui->setAttribute( 'unsubscribe_data', serialize($unsubscribeData) );			
		 }
		 
		 return true;
	}
	
	public static function removeRegisteredUserFromGroup($ui,$g){
		
		$gID = $g->getGroupID();
		
		$u = $ui->getUserObject(); 
		
		if( is_array($u->uGroups) && array_key_exists($gID, $u->uGroups) ){ 
		
			$ui->setAttribute( 'last_unsubscribe_date', date("Y-m-d H:i:s") );
			
			$unsubscribeData = unserialize($ui->getAttribute( 'unsubscribe_data' ));
			$unsubscribeData['groups'][$gID]=date('U');
			$ui->setAttribute( 'unsubscribe_data', serialize($unsubscribeData) );	
			
			$u->exitGroup($g); 
		}
	}
	
	static public function getDateStamp(){ 
		return defined('MAILING_LIST_DATE_FORMAT') ? date(MAILING_LIST_DATE_FORMAT) : date(DATE_APP_GENERIC_MDY); 
	}
	
	
	static public function sendAutoRespondEmail( $emailAddress ){  
		
		
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		//if($_SESSION['emailSent']) 
		//	return false; 
		
		//is the auto responder enabled 
		if( !intval(Config::get('TONY_MAILING_LIST_RESPONDER_ENABLED')) ){  
			return false; 
		}
		
		//get user data 
		$nonUser = TonyMailingListNonUser::getByEmail(  $emailAddress); 
		if(is_object($nonUser)){ 
			$mluID = intval($nonUser->mluID);
		}else{ 
			$ui = UserInfo::getByEmail( $emailAddress ); 
			$uID = is_object($ui) ? $ui->getUserID() : 0; 
		}
		if(!$mluID && !$uID) return false; 
		
		//get autoresponder email content 
		$siteName = defined('SITE') ? SITE : str_replace('http://','',BASE_URL);  
		$subject = trim(Config::get('TONY_MAILING_LIST_AUTO_RESPONDER_SUBJECT'));  
		$emailBodyHTML = TonyMailingList::getResponderBodyHTML();  
		if(!$emailBodyHTML || !$emailAddress || !$subject) return false; 
		
		
		$senderEmail = TonyMailingList::getSubscriptionsFromEmail(); 
		$senderName = Config::get('TONY_MAILING_LIST_SENDER_NAME'); 
		$bounceBackEmail = Config::get('TONY_MAILING_LIST_BOUNCE_BACK_EMAIL');
		
		$noTemplate = intval(Config::get('TONY_MAILING_LIST_RESPONDER_NO_TEMPLATE')); 
		$templateUsed = !$noTemplate ? 'Yes' : 'No'; 
		$includeHeaderFooterOnPlainText = (defined('MAILING_LIST_HEADER_FOOTER_ON_PLAINTEXT') && MAILING_LIST_HEADER_FOOTER_ON_PLAINTEXT); 
		$unsubscribeTextFontSize = defined('MAILING_LIST_UNSUBSCRIBE_FONT_SIZE') ? MAILING_LIST_UNSUBSCRIBE_FONT_SIZE : '9px';  
		
		$loggingOn = intval(Config::get('TONY_MAILING_LIST_EMAIL_LOGGING'))>=0;  
		
		$emailBodyHTML = TonyMailingListMailing::relativeToAbsoluteLinks( $emailBodyHTML );
		$emailBodyText = TonyMailingListMailing::html2text( $emailBodyHTML ); 
		
		//generate the unsubscribe html & text
		$lockDownGIDs = explode(',',Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS'));
		$unsubscribeLink = TonyMailingList::unsubscribeLink( $emailAddress, $lockDownGIDs, intval($uID), intval($mluID) ); 
		$unsubscribeText = "\r\n\r\n".t('You may unsubscribe from this mailing list by visiting this link:'."\r\n". $unsubscribeLink ); 
		$unsubscribeTextHTML = "\r\n\r\n".t('You may %sunsubscribe%s from this mailing list at any time.'."\r\n", '<a target="_blank" href="'.$unsubscribeLink.'" >', '</a>' ); 
		
		if( !$noTemplate ){ 
			$emailHeaderHTML = TonyMailingListMailing::relativeToAbsoluteLinks(TonyMailingList::getHeaderHTML());
			$emailFooterHTML = TonyMailingListMailing::relativeToAbsoluteLinks(TonyMailingList::getFooterHTML()); 
			
			$emailHeaderText = $includeHeaderFooterOnPlainText ? TonyMailingListMailing::html2text($emailHeaderHTML) : '';
			$emailFooterText = $includeHeaderFooterOnPlainText ? TonyMailingListMailing::html2text($emailFooterHTML) : '';
		}
		
		//add in header and footer
		$emailBodyText = $emailHeaderText . $emailBodyText . $unsubscribeText . $emailFooterText;
		$emailBodyHTML = $emailHeaderHTML . $emailBodyHTML . '<div style="font-size:'.$unsubscribeTextFontSize.'; margin-top:16px;">'.str_replace(array("\r","\n"),array("<br>",""),$unsubscribeTextHTML).'</div>'.$emailFooterHTML; 
		
		//use just one SMTP connection 
		$zendMailData = TonyMailingList::getMailerObject(); 
		$transport=(isset($zendMailData['transport']))?$zendMailData['transport']:NULL;	 
		if( is_object($transport) ){   
			Zend_Mail::setDefaultTransport($transport);
			if( !method_exists($transport,'connect') && MAIL_SEND_METHOD == "SMTP" ){ 
				//really not sure what's happening here, but some servers can still send email even if the connect method fails 
			}else{  
				$transport->connect();
			} 
		}
		
		//create the mail object  
		$mail = new Zend_Mail(APP_CHARSET);
		$mail->setFrom( $senderEmail, $senderName );
		$mail->addTo( $emailAddress );
		$mail->setSubject( $subject );
		$mail->setBodyHTML( $emailBodyHTML ); 
		$mail->setBodyText( $emailBodyText ); 
		if( $bounceBackEmail && method_exists( $mail, 'setReturnPath') ){ 
			$mail->setReturnPath( $bounceBackEmail ); 
		}
		
		try {
			//default transport was set above, so not using $mail->send($transport);
			if(strstr($emailSubject,'..')){
				$invalidEmail=1;
				throw new Exception('Invalid Email Address: '.$emailSubject);
			} 
			$mail->send(); 
			
			$_SESSION['emailSent']=1; 
			
			// add email to log
			if ( $loggingOn ) { 
				$l = new Log(LOG_TYPE_EMAILS, true, true);
				if (ENABLE_EMAILS) {
					$l->write('**' . t('EMAILS ARE ENABLED. THIS EMAIL WAS SENT TO mail()') . '**');
				} else {
					$l->write('**' . t('EMAILS ARE DISABLED. THIS EMAIL WAS LOGGED BUT NOT SENT') . '**');
				}
				$l->write(t('Template Used') . ': ' . $templateUsed);
				$l->write(t('To') . ': ' . $emailAddress );
				$l->write(t('From') . ': ' . $senderEmail );
				$l->write(t('Subject') . ': ' . $subject );
				$l->write(t('Body') . ': ' . $emailBodyText ); 
				$l->close(); 
			}	
			
		} catch( Exception $e ){ 
			
			//was this using php's default mail() function, instead of SMTP mailing? 
			//if so no valuable debugging info will be available. 
			if( stristr($e->getFile(),'Transport/Sendmail.php') && !$invalidEmail ){ 
				$trySMTPmsg=t("Unknown php mail() error.  Please try switching your concrete site to use SMTP mail.");
			}
			
			$l = new Log(LOG_TYPE_EXCEPTIONS, true, true);
			$l->write(t('Mail Exception Occurred. Unable to send auto responder mail: ') . $e->getMessage());
			$l->write('line '.$e->getLine().' within '.$e->getFile());					
			$l->write($e->getTraceAsString());
			$l->write($trySMTPmsg);
			$l->write(t('Template Used') . ': ' . $templateUsed);
			$l->write(t('To') . ': ' . $emailAddress );
			$l->write(t('From') . ': ' . $senderEmail );
			if( $loggingOn ) {
				$l->write(t('Subject') . ': ' . $subject);
				$l->write(t('Body') . ': ' . $emailBodyText );
			}				
			$l->close();  
		}
		
	}
	
}




class TonyMailingListNonUser extends Object {

	public $mluID=0;
	public $email='';
	public $groupIds=array();
	public $blacklist=0; 
	public $attrDataRaw='';
	public $last_unsubscribe_date=0;
	public $unsubscribe_data='';	
	
	public $errorMsgs=array();
	
	function __construct( $data=array() ){
		$this->email = $data['email'];
		$attrData = (is_array($data['attrData'])) ? $data['attrData'] : array(); 
		$this->attrDataRaw=serialize($attrData);
		$unsubscribe_data = (is_array($data['unsubscribe_data'])) ? $data['unsubscribe_data'] : array(); 
		$this->unsubscribeDataRaw=serialize($unsubscribe_data);
	}
	
	public static function getGroupsEmails( $gIDs=array() ){
		if( !$gIDs ) return false;
		if( !is_array($gIDs) ) $gIDs=array($gIDs);
		$db = Loader::db(); 
		$gID_like_strs=array();
		foreach($gIDs as $gID){ 
			if(!intval($gID)) return false;
			$gID_like_strs[] = ' gIDs like "%,'.intval($gID).',%"';
		}
		if(!count($gID_like_strs)) return false; 
		return $db->getAll('SELECT email, mluID, attrDataRaw, gIDs FROM TonyMailingListNonUsers WHERE (blacklist=0 || blacklist IS NULL) AND ('.join(' OR ',$gID_like_strs).') ORDER BY email');
	}
	
	public static function getById($id=0){ 
		$db = Loader::db(); 
		$cols = $db->getRow('SELECT * FROM TonyMailingListNonUsers WHERE mluID=?',array(intval($id)));
		if( !$cols ) return false;
		return TonyMailingListNonUser::loadNonUser($cols);
	}	
	
	public static function getByEmail($email=''){ 
		$db = Loader::db(); 
		$cols = $db->getRow('SELECT * FROM TonyMailingListNonUsers WHERE email=?',array($email));
		if( !$cols ) return false;
		return TonyMailingListNonUser::loadNonUser($cols);
	}
	
	protected function loadNonUser($cols){
		$nonUser = new TonyMailingListNonUser();
		foreach($cols as $key=>$val){
			if( is_int($key) ) continue; 
			if( $key=='gIDs' ){  
				$nonUser->groupIds = explode(',',$val);  
			}
			$nonUser->$key=$val;
		} 
		return $nonUser;
	}
	
	public function getAttrData(){
		$data = unserialize($this->attrDataRaw);
		return (is_array($data)) ? $data : array();
	}
	
	public function setAttrData($attrData){
		$this->attrDataRaw=serialize($attrData);
	}	
	
	public function getUnsubscribeData(){
		$data = unserialize($this->unsubscribe_data);
		return (is_array($data)) ? $data : array();
	}
	
	public function setUnsubscribeData($data){
		$this->unsubscribe_data=serialize($data);
	}		
	
	public function create(){   
		$db = Loader::db(); 
		
		$stringsHelper = Loader::helper('validation/strings'); 
		
		//make sure the email's valid 
		if( !$stringsHelper->email($this->email) ){ 
			$this->errorMsgs[]=t('Invalid email address'); 
			return false; 
		}
		
		//check email doesn't exist
		$nonUser = TonyMailingListNonUser::getByEmail( $this->email );
		
		if($nonUser){
			$this->errorMsgs[]=t('Email already exists'); 
			return false;
		}
		
		$vals = array( $this->email, $this->getGroupIdsStr(), intval($this->blacklist), $this->attrDataRaw, $this->unsubscribe_data, $this->last_unsubscribe_date, time() );
		$db->query( 'INSERT INTO TonyMailingListNonUsers (email,gIDs,blacklist,attrDataRaw,unsubscribe_data,last_unsubscribe_date,created) VALUES (?,?,?,?,?,?,?)' , $vals ); 
		$this->mluID=$db->Insert_ID();
	}
	 
	public function update(){ 
		$db = Loader::db(); 
		$vals = array( $this->getGroupIdsStr(), intval($this->blacklist), $this->attrDataRaw, $this->unsubscribe_data, $this->last_unsubscribe_date, $this->email ); 
		$sql = 'UPDATE TonyMailingListNonUsers SET gIDs=?, blacklist=?, attrDataRaw=?, unsubscribe_data=?, last_unsubscribe_date=? WHERE email=?';
		$db->query( $sql , $vals ); 
	}
	
	public function delete(){
		$db = Loader::db(); 
		$vals = array( $this->email ); 
		$sql = 'DELETE FROM TonyMailingListNonUsers WHERE email=?';
		$db->query( $sql , $vals ); 
	}
	
	public function getGroupIdsStr(){
		return ','.join(',',$this->groupIds).',';
	}
	
	public function addGroup($gID=0){ 
		if(!intval($gID)) return false; 
		$this->groupIds[]=intval($gID);
		$this->groupIds=array_unique($this->groupIds);
		
		//update unsubscribe data 
		$unsubscribeData = $this->getUnsubscribeData();
		//remove group unsubscribe date
		if(is_array($unsubscribeData['groups'])) 
			unset($unsubscribeData['groups'][$gID]);
		//remove blacklist date
		unset($unsubscribeData['blacklist']);
		$this->setUnsubscribeData($unsubscribeData);		
	}
	
	public function removeGroup($gID=0){ 
		$newGIDs=array();
		foreach($this->groupIds as $oldGID){
			if(intval($oldGID) && intval($oldGID)!=intval($gID) )
				$newGIDs[]=$oldGID;
		}
		$this->groupIds=$newGIDs;
		
		//keep track of blacklist date
		$unsubscribeData = $this->getUnsubscribeData();
		$unsubscribeData['groups'][$gID]= date('U');
		$this->last_unsubscribe_date=date('U');
		$this->setUnsubscribeData($unsubscribeData);		
	} 
	
}

//MISCELLANEOUS FUNCTIONS 

// Allow these tags
function tonyMailingList_getAllowedTags(){ return '<h1><h2><h3><h4><h5><p><b><i><a><ul><ol><li><pre><hr><blockquote><img><strong><em><u><div><br><center><font><span><table><tbody><th><td><tr>'; }
$tonyMailingList_allowedTags = tonyMailingList_getAllowedTags();
// Disallow these attributes/prefix within a tag
function tonyMailingList_getStripAttrib(){return 'javascript:|onclick|ondblclick|onmousedown|onfocus|onblur|onmouseup|onmouseover|'.
			   'onmousemove|onmouseout|onkeypress|onkeydown|onkeyup';}
$tonyMailingList_stripAttrib = tonyMailingList_getStripAttrib();
// Strip forbidden tags and delegate tag-source check to removeEvilAttributes(), allowedTags=string
function tonyMailingList_removeEvilTags($source,$allowedTags,$stripAttrib){
   $source = strip_tags($source, $allowedTags);
   return preg_replace('/<(.*?)>/ie', "'<'.tonyMailingList_removeEvilAttributes('\\1',\$stripAttrib).'>'", $source);
}
// Strip forbidden attributes from a tag - stripAttrib=string
function tonyMailingList_removeEvilAttributes($tagSource,$stripAttrib){
   return stripslashes(preg_replace("/$stripAttrib/i", 'forbidden', $tagSource));
}

?>