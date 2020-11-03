<?php  

defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('user_attributes');  

class TonyMailingListMailing extends Object {
	
	protected $mlmID=0; 
	protected $status='pending'; 
	protected $statusTypes=array( 'draft'=>'Draft', 'pending'=>'Pending', 'completed'=>'Completed', 'running'=>'Running', 'interrupted'=>'Interrupted', 'sleeping'=>'Sleeping' );
	protected $status_msg='';  
	protected $recipients='groups'; //groups or all
	protected $recipients_count=0; 
	protected $gIDs=array();
	protected $sender_uID=0;
	protected $sender='this_user'; //or other
	protected $sender_email='';
	protected $sender_name='';
	protected $subject='';
	protected $body=''; 
	protected $sentMLUIDs='';
	protected $sentUIDs='';
	protected $failedMLUIDs='';
	protected $failedUIDs='';	
	protected $sent_count=0;
	protected $whiteListAttrId=0; 
	protected $blackListAttrId=0; 
	protected $attachments=''; 
	protected $stats='';
	
	public function setId($v){ $this->mlmID=intval($v); }
	public function getId(){ return intval($this->mlmID); }
	
	public function incrementSentCount(){ $this->sent_count++; }
	public function getSentCount(){ return intval($this->sent_count); }	
	
	public function setStatus($v){ $this->status=$v; }
	public function getStatus(){ return $this->status; }
	public function setStatusMsg($v){ $this->status_msg=$v; }
	public function getStatusMsg(){ return $this->status_msg; }
	public function getStatusText(){ return $this->statusTypes[$this->status]; } 
	
	public function setRecipients($v){ $this->recipients=$v; }
	public function getRecipients(){ return $this->recipients; }		
	public function setRecipientsCount($v){ $this->recipients_count=intval($v); }
	public function getRecipientsCount(){ 
		if( $this->getSentCount() > intval($this->recipients_count) ) 
			$this->recipients_count = $this->getSentCount();
		return intval($this->recipients_count); 
	} 
	public function setGIDs($v){ 
		if(!$v) return; 
		if( !is_array($v) ) $v = explode(',',$v);
		$this->gIDs=$v; 
	}
	public function getGIDs(){ return $this->gIDs; }	
	public function getWhiteListAttrId(){ return $this->whiteListAttrId; } 
	public function setWhiteListAttrId($v){ $this->whiteListAttrId=intval($v); }
	public function getBlackListAttrId(){ return $this->blackListAttrId; } 
	public function setBlackListAttrId($v){ $this->blackListAttrId=intval($v); }	
	
	public function setSenderUID($v){ $this->sender_uID=intval($v); }
	public function getSenderUID(){ return intval($this->sender_uID); }
	public function setSender($v){ $this->sender=$v; }
	public function getSender(){ return $this->sender; }
	public function setSenderEmail($v){ $this->sender_email=trim($v); }
	public function getSenderEmail(){ return (!$this->sender_email && defined('MAILING_LIST_FROM_DEFAULT')) ? MAILING_LIST_FROM_DEFAULT : $this->sender_email; }
	public function setSenderName($v){ $this->sender_name=trim($v); }
	public function getSenderName(){ return $this->sender_name; }
	
	
	public function setSubject($v){ $this->subject=trim($v); }
	public function getSubject(){ return $this->subject; }	
	public function setBody($v){ 
		$v=tonyMailingList_removeEvilTags( $v, tonyMailingList_getAllowedTags(), tonyMailingList_getStripAttrib() );
		$this->body=trim($v); 
	}
	public function getBody(){ return $this->body; }
	
	public function getSentUIDsArray(){
		if(!$this->sentUIDs) return array();
		return explode(',',$this->sentUIDs);
	} 
	public function getSentMLUIDsArray(){
		if(!$this->sentMLUIDs) return array();
		return explode(',',$this->sentMLUIDs);
	}
	public function getFailedUIDsArray(){
		if(!$this->failedUIDs) return array();
		return explode(',',$this->failedUIDs);
	} 
	public function getFailedMLUIDsArray(){
		if(!$this->failedMLUIDs) return array();
		return explode(',',$this->failedMLUIDs);
	}	
	
	public function refreshSentIDsArrays(){ 
		$db = Loader::db();
		$cols = $db->getRow('SELECT sentUIDs, sentMLUIDs, failedUIDs, failedMLUIDs FROM TonyMailingListMailings WHERE mlmID=?', array($this->getId()) );
		if( !$cols ) return false;   
		$this->sentUIDs = $cols['sentUIDs']; 
		$this->sentMLUIDs = $cols['sentMLUIDs']; 
		$this->failedUIDs = $cols['failedUIDs']; 
		$this->failedMLUIDs = $cols['failedMLUIDs']; 
	}
	
	public function getFailedCount(){  
		//$recips = getRecipientsCount();
		$failed = count(array_unique( $this->getFailedUIDsArray() )) + count(array_unique( $this->getFailedMLUIDsArray() ));
		return $failed;
	}
	
	public function setAttachments($v){ $this->attachments=trim($v); }
	public function getAttachments(){ return $this->attachments; } 
	
	//returns serialize object
	public function getStats(){ return $this->stats; }
	public function setStats($v){ return $this->stats=$v; }
	
	public function getStatsObj(){
		
		$stats = unserialize($this->getStats());
		
		if( !is_array($stats)) $stats=array(); 
		if( !is_array($stats['trackedUIDs']) ) $stats['trackedUIDs']=array();
		if( !is_array($stats['trackedMLUIDs']) ) $stats['trackedMLUIDs']=array();
		if( !is_array($stats['clickedLinks']) ) $stats['clickedLinks']=array();
		if( !is_array($stats['unsubscribedUIDs']) ) $stats['unsubscribedUIDs']=array();
		if( !is_array($stats['unsubscribedMLUIDs']) ) $stats['unsubscribedMLUIDs']=array();
		if( !is_array($stats['clickThruUserMLUIDs']) ) $stats['clickThruUserMLUIDs']=array();
		if( !is_array($stats['clickThruUserUIDs']) ) $stats['clickThruUserUIDs']=array();		
		
		return $stats;
	}
	
	public function statsEnabled(){ 
		$stats = unserialize($this->stats);
		return (is_array($stats) && $stats['statsEnabled']) ? 1 : 0;
	}
	
	public function getCreated(){ return intval($this->created); }
	
	public function getUpdated(){ return intval($this->updated); } 
	
	public static function getList( $excludeCompleted=false, $mode='rows', $limit='' ){ 
		if( $excludeCompleted ) $notCompletedSQL = 'AND status!="completed"';
		$db = Loader::db(); 
		if( $mode=='count' ){
			return $db->getOne('SELECT count(*) FROM TonyMailingListMailings WHERE 1=1 '.$notCompletedSQL.' ');
		}else{
			if(!$limit) $limit='';
			elseif( !strstr(strtolower($limit),'limit') ) $limit=' LIMIT '.$limit;			
			$rows = $db->getAll('SELECT * FROM TonyMailingListMailings WHERE 1=1 '.$notCompletedSQL.' ORDER BY created DESC'.$limit );
		}
		
		$mailings=array();
		foreach($rows as $row){
			$mailings[]=TonyMailingListMailing::loadData($row);
		}
		return $mailings;
	} 
		
	public static function getById($id=0){ 
		$db = Loader::db(); 
		$cols = $db->getRow('SELECT * FROM TonyMailingListMailings WHERE mlmID=?',array(intval($id)));
		if( !$cols ) return false;
		return TonyMailingListMailing::loadData($cols);
	}
		
	protected function loadData($cols){
		$mailing= new TonyMailingListMailing();
		foreach($cols as $key=>$val){
			if( is_int($key) ) continue; 
			if( strtolower($key)=='gids'){
				$val=explode(',',$val);
			}
			$mailing->$key=$val;
		} 
		return $mailing;
	}
	
	public function save(){
		if( $this->getId() ) $this->update();
		else $this->create();
	}
	
	public function create(){  
	
		//only track stats on new mailings 
		if( !$this->stats ) 
			$this->stats= serialize( array('statsEnabled'=>1) );
	
		$db = Loader::db();  
		$vals = array( $this->status, $this->status_msg, $this->recipients, intval($this->recipients_count),join(',',$this->getGIDs()),
					   intval($this->sender_uID),$this->sender,$this->sender_email,$this->sender_name,$this->subject, $this->body, $this->attachments, intval($this->whiteListAttrId), intval($this->blackListAttrId), $this->stats, time(), time() );
		$result = $db->query( 'INSERT INTO TonyMailingListMailings (status,status_msg,recipients,recipients_count,gIDs,sender_uID,sender,sender_email,sender_name,subject,body,attachments,whiteListAttrId,blackListAttrId,stats,created,updated) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)' , $vals ); 
		if($result) $this->mlmID = $db->Insert_ID();
		return $result;
	}
	 
	public function update(){ 
		$db = Loader::db(); 
		$vals = array( $this->status, $this->status_msg, $this->recipients, intval($this->recipients_count),join(',',$this->getGIDs()),
					   intval($this->sender_uID),$this->sender,$this->sender_email,$this->sender_name,$this->subject, $this->body, $this->attachments, intval($this->whiteListAttrId), intval($this->blackListAttrId), $this->stats, time(), intval($this->mlmID) ); 
		$sql = 'UPDATE TonyMailingListMailings SET status=?, status_msg=?, recipients=?, recipients_count=?, gIDs=?, sender_uID=?, sender=?, sender_email=?, sender_name=?, subject=?, body=?, attachments=?, whiteListAttrId=?, blackListAttrId=?, stats=?, updated=? WHERE mlmID=?';
		return $db->query( $sql , $vals ); 
	}	
	
	static function unicodeUpperCase($v){
		if( stristr($v,'http://') || stristr($v,' href=') || stristr($v,' src=') ) return $v; //don't uppercase links, incase it breaks them 		
		if( !function_exists('mb_strtoupper') ) return $v;
		return mb_strtoupper(html_entity_decode($v, ENT_QUOTES, 'UTF-8'),'utf-8');
	}
	
	
	static public function html2text($html){  
	
		$html = preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si', ' ', $html ); 
		
		$tags = array ( 
			"~\r~", //non-legal return character
			"~\n~", //new lines
			"~\t~", //tabs
			'~<h[123][^>]*>(.*?)</h[123]>~ie',
			'~<h(4|5|6)[^>]*>(.*?)</h(4|5|6)>~si',
			'~<img [^>]*src=[\"\']([^"]+)[\"\'][^>]*>~si', 
			'~<table[^>]*>(.*?)</table>~si',
			'~<tr[^>]*>(.*?)</tr>~si',
			'~</td><td[^>]*>~is', 
			'~<li[^>]*>(.*?)</li>~si',
			'~<p[^>]*>(.*?)</p>~si',
			'~<br[^>]*>~si',
			'~<div[^>]+>~si', 
			'~<ul[^>]*>(.*?)</ul>~si',
			'~<ol[^>]*>(.*?)</ol>~si', 
			'~<b[^>]*>(.*?)</b>~ie', 
			'~<strong[^>]*>(.*?)</strong>~ie',   
			"~   ~", //triple spaces
			"~  ~" //double spaces 
		);
		
		$swaps = array (
			'', // \r
			'', // \n
			'',// tabs
			"TonyMailingListMailing::unicodeUpperCase(\"\n\\1\n\n\")", //h123
			"\n\\2\n", //h456
			"\n\n".t('Image')." [ \\1 ]\n\n",    //image
			"\n\\1", //table
			"\\1\n", //tr
			" | ", //td 
			"&nbsp;&nbsp;&nbsp;&nbsp;* \\1\n", //li
			"\n\\1\n\n", //p
			"\n",   //br 
			"\n",  //div
			"\n\\1\n", //ul
			"\n\n\\1\n",  //ol
			"TonyMailingListMailing::unicodeUpperCase(\"\\1\")",   //bold
			"TonyMailingListMailing::unicodeUpperCase(\"\\1\")", //strong  
			' ',
			' '
		);
		
		//remove links from tags, and put them in brackets 
		$html = TonyMailingListMailing::strip_urls($html); 

		// reducing newlines
		//$html = preg_replace('~\n+~s'," ",$html);	
		//$html = str_replace(array("\n","\r")," ",$html);	
		
		$html = preg_replace($tags,$swaps,$html); 
		//$html = preg_replace('~</t(d|h)>\s*<t(d|h)[^>]+>~si',' - ',$html);
		//$html = preg_replace('~<[^>]+>~s','',$html);
		$html = strip_tags($html);
		
		// reducing spaces
		//$html = preg_replace('~ +~s',' ',$html);
		//$html = preg_replace('~^\s+~m','',$html);
		//$html = preg_replace('~\s+$~m','',$html); 
		$html = str_replace('&nbsp;',' ',$html);		
		
		return $html;
	}
	
	public static function strip_urls($text){
		$repPat = "text [link: url]";
		$aimpstr = 'BLARGLING'; 
		$impstr = md5($aimpstr);
		$text = str_replace('</a>', '</a>' . $impstr, $text);
		$text = explode($impstr, $text);
		$n = 0;
		$texta = array();
		$repPat = str_ireplace(array('text', 'url'), array('\\4', '\\2'), $repPat); 
		
		foreach ($text as $text) {
			//$texta[$n] = ereg_replace("<a(.*)href=[\"\']+([^\'\"]*)[\"\']+([^>]*)>(.*)</a>", $repPat, $text);
			$texta[$n] = preg_replace("#<a(.*)href=[\"\']+([^\'\"]*)[\"\']+([^>]*)>(.*)</a>#", $repPat, $text);
			$n++;
		}
		$textb = implode("</a>", $texta); 
		return $textb;
	}
	
	protected static $foundAttrs=array();
	protected static $attrsSearched=0;
	protected static $foundAttrsSubject=array();
	protected static $attrsSearchedSubject=0;	
	protected static $txtHelper;
	protected static $tempUserInfo;
	public static function userAttributeTextReplacement($text,$userInfo,$useFoundAttr=1,$stripHTML=0,$replaceSubject=0){
		
		if(!self::$txtHelper) self::$txtHelper = Loader::helper('text');
		if(!self::$tempUserInfo){
			self::$tempUserInfo = new UserInfo();
			self::$tempUserInfo->uID=0;	
		}
		
		if($replaceSubject){
			$searched=self::$attrsSearchedSubject;
			$foundAttrs=self::$foundAttrs;
		}else{
			$searched=self::$attrsSearched;
			$foundAttrs=self::$foundAttrsSubject;
		}
		
		if( is_object($userInfo) && get_class($userInfo)=='User' ){
			$userInfo= UserInfo::getById($userInfo->uID);
		}
		
		//only figure out which attributes are included in the text just one time 
		if(!$attrsSearched || !$useFoundAttr){
			$searched=1; 
			$userAttributes = UserAttributeKey::getList(); 
			foreach($userAttributes as $userAttr){
				if(stristr($text,'%'.$userAttr->getAttributeKeyHandle().'%')){
					$foundAttrs[$userAttr->getAttributeKeyHandle()]=$userAttr;
				}
			}
			
			foreach(array('user_name','email') as $attrHandle){
				if(stristr($text,'%'.$attrHandle.'%')){
					$foundAttrs[$attrHandle]=$attrHandle;  
				}
			}
		}
		
		$attrDefaults = self::getAttrDefaults();
		
		foreach($foundAttrs as $attributeHandle=>$userAttr){ 
			
			$attributeValue=''; 
			
			//non-registered user attribute value approach
			if( is_array($userInfo) ){ 
				
				$attributeValueData = $userInfo[$attributeHandle];  
				
				//custom attributes
				if(is_array($attributeValueData)){ 
					$attrType = self::getCachedAttrType($userAttr);
					if( !is_object($attrType) ) continue; 
					$classname = self::$txtHelper->camelcase( $attrType->getAttributeTypeHandle() ).'AttributeTypeValue'; 
					if( class_exists($classname) && method_exists($classname,'__toString')){ 	
						$attributeValueObj = new $classname();
						if(is_object($attributeValueObj)){
							$attributeValueObj->setPropertiesFromArray($attributeValueData);  
							$attributeValue = $attributeValueObj->__toString();
						}
					}else{  
						$_POST['akID'][intval($userAttr->getAttributeKeyID())]=$attributeValueData;
						$userAttr->saveAttributeForm(self::$tempUserInfo); 
						$val = self::$tempUserInfo->getAttributeValueObject($userAttr); 
						if(is_object($val)) $attributeValue = $val->getValue('display'); 
					}	 
				}elseif(strlen($attributeValueData)){
					//non-user attribute: email address for example 
					$attributeValue = $attributeValueData;
				}
				
			//registered user attributes
			}elseif(get_class($userInfo)=='UserInfo') { 
				
				if( is_object($userAttr) ){
					$attributeValue = TonyMailingListMailing::relativeToAbsoluteLinks( $userInfo->getAttribute($attributeHandle) );
				}else{
					//non user attribute: username or email
					if($attributeHandle=='user_name') $attributeValue = $userInfo->getUserName();
					elseif($attributeHandle=='email') $attributeValue = $userInfo->getUserEmail();
					else $attributeValue = '';
				} 
			}
			
			$attributeValue = nl2br($attributeValue); 
			
			if($stripHTML) $attributeValue=strip_tags(TonyMailingListMailing::html2text($attributeValue));  
			
			if(!trim($attributeValue)){
				if(is_object($userAttr) ) $attributeValue=$attrDefaults['ua_'.intval($userAttr->getAttributeKeyID()) ];
				else $attributeValue=$attrDefaults['ua_'.$attributeHandle];
			}
			
			$text=str_ireplace('%'.$attributeHandle.'%',$attributeValue,$text); 
		}
		
		
		$text= str_ireplace('%date_stamp%', TonyMailingList::getDateStamp(), $text);
		
		return $text;
	}
	
	static protected $userAttrDefaults;
	static protected function getAttrDefaults(){
		if(is_array(self::$userAttrDefaults)) return self::$userAttrDefaults; 
		self::$userAttrDefaults = unserialize(Config::get('TONY_MAILING_LIST_USER_ATTRIBUTE_DEFAULTS'));
		if(!is_array(self::$userAttrDefaults)) self::$userAttrDefaults=array();		
		return self::$userAttrDefaults;
	}
	static protected $attrKeyTypes=array();
	protected function getCachedAttrType($ak){
		$handle=$ak->getAttributeKeyHandle();
		if(isset(self::$attrKeyTypes[$handle])) return self::$attrKeyTypes[$handle]; 
		self::$attrKeyTypes[$handle]=$ak->getAttributeType();
		return self::$attrKeyTypes[$handle];
	}
	
	public static function relativeToAbsoluteLinks($text){ 
	
		$prefix = BASE_URL; 
		
		$text = str_ireplace(array(' href=" http',' src=" http'),array(' href="http',' src="http'),$text);
		 
		// replace relative urls by absolute (prefix them with $prefix)
		$pattern = '/href=[\'|"](?!http|https|ftp|irc|feed|mailto|#)([\/]?)([^\'|"]*)[\'|"]/i';
		$replace = 'href="'.$prefix.'/$2"';
		$text = preg_replace($pattern, $replace, $text); 
		 
		// replace relative img urls by absolute (prefix them with $prefix)
		$pattern = '/src=[\'|"](?!http|https|ftp|irc|feed|mailto|#)([\/]?)([^\'|"]*)[\'|"]/i';
		$replace = 'src="'.$prefix.'/$2"';
		$text = preg_replace($pattern, $replace, $text); 		
		
		return $text; 
	}
	
	public static function trackableLinks($text,$linkTrackURL,$mode='HTML'){ 
		
		$linkTrackURL=$linkTrackURL.'&url=';
		
		$text = str_ireplace( array(' href=" http'), array(' href="http'),$text);
		 
		// replace relative urls by absolute (prefix them with $prefix)
		if($mode=='TEXT'){ 
			$pattern = '/\[link: (.*?)\]/i';
			$func = 'mailing_list_track_links_urlencode_text';
		}else{
			$pattern = '/href="(.*?)"/i';
			$func = 'mailing_list_track_links_urlencode';
		}
		
		//$replace = 'href="'.$linkTrackURL.'"';
		$text = preg_replace_callback($pattern, $func, $text); 		
		
		$text = str_replace( TRACK_LINK_URL, $linkTrackURL, $text);
		
		return $text; 
	}
	
	public function send( $startTime=0 ){ 
		
		$loggingMode = intval(Config::get('TONY_MAILING_LIST_EMAIL_LOGGING'));
		$loggingOn = ($loggingMode==1 || ( $loggingMode==0 && $this->getSentCount < TonyMailingList::$limitedLogCount)) ? 1 : 0;
		
		if( intval($_REQUEST['forceFailed']) ) $forceFailed=1; 
		
		$includeHeaderFooterOnPlainText = (defined('MAILING_LIST_HEADER_FOOTER_ON_PLAINTEXT') && MAILING_LIST_HEADER_FOOTER_ON_PLAINTEXT); 

		try{ 
		
			//don't start a second process if it's already going
			if( $this->status=='draft' ){
				throw new Exception( 'Cannot send draft mailings.' ); 
			}
		
			//don't start a second process if it's already going
			if( $this->status=='running' || $this->status=='sleeping' ){
				throw new Exception( 'This send process is already running.' ); 
			}
			
			if($this->status=='pending')
				$this->updateStatus( 'running', t('Send process is running') ); 			
		
			$throttle = intval(Config::get('TONY_MAILING_LIST_THROTTLE'));
			$pauseTime = intval(Config::get('TONY_MAILING_LIST_PAUSE_TIME'));
			$emailsPerSet = Config::get('TONY_MAILING_LIST_EMAILS_PER_SET'); 
			$bounceBackEmail = Config::get('TONY_MAILING_LIST_BOUNCE_BACK_EMAIL');
		
			//up the max execution time if it's low 
			$maxExecTime =  Config::get('TONY_MAILING_LIST_MAX_TIME');
			if( $throttle && intval($maxExecTime) && !ini_get('safe_mode')  ) 
				set_time_limit( $maxExecTime );
			else $maxExecTime = ini_get('max_execution_time');  
			
			//start time can be passed in by TonyMailingList::sendAllMailings(), to make sure all run mailings don't exceed max execution
			if(!$startTime) $startTime = time();
			
			$recipientEmails = $this->getRecipientsData();
			
			$emailBodyHTML = TonyMailingListMailing::relativeToAbsoluteLinks($this->getBody());
			
			//the width of 98% for the outer table is a yahoo mail requirement, and the inner width of 600 is the standard size otherwise
			$emailHeaderHTML = TonyMailingListMailing::relativeToAbsoluteLinks(TonyMailingList::getHeaderHTML());
			$emailFooterHTML = TonyMailingListMailing::relativeToAbsoluteLinks(TonyMailingList::getFooterHTML()); 
			
			//add tracking pixel 
			$trackingPixelRoot = BASE_URL.View::url('/tools/packages/tony_mailing_list/services/?mode=stats&mlm='.$this->getId() );
			$trackingLinkRoot = BASE_URL.View::url('/tools/packages/tony_mailing_list/services/?mode=link&mlm='.$this->getId() );
			
			$emailBodyText = TonyMailingListMailing::html2text($emailBodyHTML); 
			
			$canSubscribeGIDs = explode(',',Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS')); 
			$unsubscribableGIDs=array();
			//if any of the recipient groups are not unsubscribable, then send the opt out message for registered users in those groups 
			foreach( $this->getGIDs() as $gID ){
				if( !in_array($gID,$canSubscribeGIDs) ){
					$containsUnsubscribaleGroups=1;	
					$unsubscribableGIDs[] = $gID;
				}
			}
			
			$unsubscribeTextFontSize = defined('MAILING_LIST_UNSUBSCRIBE_FONT_SIZE') ? MAILING_LIST_UNSUBSCRIBE_FONT_SIZE : '9px'; 
			
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

			$this->updateStatus( 'running', t('Send process is running') );  
			
			//send 
			$failed=0; 
			$mailsSendSincePause=0;
			foreach($recipientEmails as $recipientEmail){
				
				if( $loggingMode==0 && $this->getSentCount() >= TonyMailingList::$limitedLogCount ) $loggingOn=0;  
				
				if( $throttle && $pauseTime && $emailsPerSet && $mailsSendSincePause>=$emailsPerSet ){  
					$this->updateStatus( 'sleeping', 'If the mail process is sleeping for too long, change the pause value on the settings page.' );
					
					sleep($pauseTime); 
					
					$this->updateStatus( 'running', '' ); 
					$mailsSendSincePause=0; 
				} 
				
				//are we approaching the max script execution time limit?  
				$timeDiff = ($maxExecTime<15) ? 1:5; 
				$runTime=time()-$startTime;
				if( $runTime>0 && $runTime > ($maxExecTime-$timeDiff) ){  
					//if so, then fail gracefully
					$failed=1;
					$this->updateStatus( 'interrupted', t('Send process ran into max execution time limit.') ); 
					break;
				}
				
				//the send user id arrays should be as fresh as possible incase two instances might have somehow started 
				//(althought there are also checks to stop another instance of the send process from starting if one is already underway)  
				$this->refreshSentIDsArrays(); 
				
				$sentUIDs=$this->getSentUIDsArray();
				$sentMLUIDs=$this->getSentMLUIDsArray();
				$failedUIDs=$this->getFailedUIDsArray();
				$failedMLUIDs=$this->getFailedMLUIDsArray(); 
				
				//has this mailing been sent to this user?
				if( intval($recipientEmail['mluID']) && in_array($recipientEmail['mluID'],$sentMLUIDs) ) continue;
				if( intval($recipientEmail['uID']) && in_array($recipientEmail['uID'], $sentUIDs) ) continue;	
				if( !intval($recipientEmail['mluID']) && !intval($recipientEmail['uID']) ) continue;
				
				//has this email address failed on previous attempts? and is the force override off? 
				if( !$forceFailed && intval($recipientEmail['mluID']) && in_array($recipientEmail['mluID'], $failedMLUIDs) ) continue; 
				if( !$forceFailed && intval($recipientEmail['uID']) && in_array($recipientEmail['uID'], $failedUIDs) ) continue;			
				
				//tracking url 
				$trackingPixelUser = $trackingPixelRoot.'&mlu='.intval($recipientEmail['mluID']).'&u='.intval($recipientEmail['uID']); 
				$trackingLinkUser = $trackingLinkRoot.'&mlu='.intval($recipientEmail['mluID']).'&u='.intval($recipientEmail['uID']); 
				
				//add trackable links 
				$usersEmailHeaderHTML = TonyMailingListMailing::trackableLinks($emailHeaderHTML,$trackingLinkUser); 
				$usersEmailFooterHTML = TonyMailingListMailing::trackableLinks($emailFooterHTML,$trackingLinkUser); 
				$usersEmailBodyHTML = TonyMailingListMailing::trackableLinks($emailBodyHTML,$trackingLinkUser); 
				
				$usersEmailBodyText = TonyMailingListMailing::trackableLinks($emailBodyText,$trackingLinkUser,'TEXT'); 
				
				$unsubscribeText=''; 
				$unsubscribeTextHTML='';
				
				//text replacement, for user attributes like first_name
				if( is_object($recipientEmail['ui']) ){
					$userDataObj = $recipientEmail['ui'];
				}else{
					$recipientEmail['attrData']['email']=$recipientEmail['email']; 
					$userDataObj = $recipientEmail['attrData'];  
				}
				$usersEmailHeaderHTML = TonyMailingListMailing::userAttributeTextReplacement($usersEmailHeaderHTML,$userDataObj,1,0);
				$usersEmailFooterHTML = TonyMailingListMailing::userAttributeTextReplacement($usersEmailFooterHTML,$userDataObj,1,0);				
				$usersEmailBodyHTML = TonyMailingListMailing::userAttributeTextReplacement($usersEmailBodyHTML,$userDataObj,1,0);
				$usersEmailBodyText = TonyMailingListMailing::userAttributeTextReplacement($usersEmailBodyText,$userDataObj,1,1);
				$emailSubject = strip_tags(TonyMailingListMailing::userAttributeTextReplacement($this->getSubject(),$userDataObj,1,0,1)); 
				
				//add tracking pixel to body
				$usersEmailBodyHTML .= '<div><img src="'.$trackingPixelUser.'"></div>';				
				
				
				//generate the unsubscribe link & text   
				$unsubscribeLinkURL = TonyMailingList::unsubscribeLink( $recipientEmail['email'], $this->getGIDs(), $recipientEmail['uID'], $recipientEmail['mluID'],$this->getId());
				$unsubscribeLink = $trackingLinkUser.'&uns=1&url='.urlencode($unsubscribeLinkURL);
				
				//unsubscribe feature when sending to a group
				if( $this->getRecipients() =='groups' ){ 
					
					//if this is sending to an administrator, exclude the unsubscribe message 
					$noUnsubscribeMsg=0;
					if( $sendingToAdmins ){  
						if( is_object($recipientEmail['ui']) && $recipientEmail['ui']->getUserObject()->inGroup($adminGroup) ){ 
							$noUnsubscribeMsg=1;
						}
					}
					
					$useOptOutMsg=0;
					if( $containsUnsubscribaleGroups && is_object($recipientEmail['ui']) ){
						//get user's groups
						$usersGroups = $recipientEmail['ui']->getUserObject()->getUserGroups(); 
						//test to see if user belongs to unsubscribable group 
						if(is_array($usersGroups) && is_array($unsubscribableGIDs)){ 
							foreach($unsubscribableGIDs as $unsubscribableGID){
								if( array_key_exists( $unsubscribableGID ,$usersGroups ) ){
									$useOptOutMsg=1; 
									break; 
								}
							}
						}
					}
					
					if($noUnsubscribeMsg){
						$unsubscribeText = "\r\n\r\n".t('(Unsubscribe disabled when sending to site administrators)') ; 
					}else{ 
						//generate the unsubscribe html & text
						$unsubscribeText = "\r\n\r\n".t('You may unsubscribe from this mailing list by visiting this link:'."\r\n". $unsubscribeLink ); 
						$unsubscribeTextHTML = "\r\n\r\n".t('You may %sunsubscribe%s from this mailing list at any time.'."\r\n", '<a target="_blank" href="'.$unsubscribeLink.'" >', '</a>' ); 

					}
					
				}elseif( $this->getRecipients() == 'all' ){  	
			 
					$unsubscribeText = "\r\n\r\n".t('You may unsubscribe from future mailings from this site by visiting this link:'."\r\n". $unsubscribeLink ); 
					$unsubscribeTextHTML = "\r\n\r\n".t('You may %sunsubscribe%s from future mailings from this site at any time.'."\r\n", '<a target="_blank" href="'.$unsubscribeLink.'" >', '</a>' ); 
				}
				
				//add unsubscribe links 
				if( !strlen($unsubscribeTextHTML) ) $unsubscribeTextHTML = $unsubscribeLink; 
				$usersEmailHeaderText = $includeHeaderFooterOnPlainText ? TonyMailingListMailing::html2text($usersEmailHeaderHTML) : '';
				$usersEmailFooterText = $includeHeaderFooterOnPlainText ? TonyMailingListMailing::html2text($usersEmailFooterHTML) : '';
				$emailBodyTextUnsubscribe = $usersEmailHeaderText . $usersEmailBodyText . $unsubscribeText . $usersEmailFooterText;
				$emailBodyHTMLUnsubscribe = $usersEmailHeaderHTML.$usersEmailBodyHTML.'<div style="font-size:'.$unsubscribeTextFontSize.'; margin-top:16px;">'.str_replace(array("\r","\n"),array("<br>",""),$unsubscribeTextHTML).'</div>'.$usersEmailFooterHTML;
				
				
				//create the mail object  
				$mail = new Zend_Mail(APP_CHARSET);
				$mail->setFrom( $this->getSenderEmail(), trim($this->getSenderName()) );
				$mail->addTo( $recipientEmail['email'], $recipientEmail['name']);
				$mail->setSubject( $emailSubject );
				$mail->setBodyHTML( $emailBodyHTMLUnsubscribe );
				$mail->setBodyText( $emailBodyTextUnsubscribe );
				if( $bounceBackEmail && method_exists( $mail, 'setReturnPath') ){ 
					$mail->setReturnPath( $bounceBackEmail );
				}
				
				//add the attachments
				foreach( explode(',',$this->getAttachments()) as $fID ){  
					$file = File::getByID(intval($fID)); 
					if(!is_object($file) || !$file->getFileID()) continue; 
					$fv = $file->getApprovedVersion();
					$fileContents = file_get_contents( $fv->getPath() );
					$attachment = $mail->createAttachment($fileContents);
					$attachment->filename = $fv->getFileName(); 
				} 
				
				//send the email 
				$invalidEmail=0;
				try {
					//default transport was set above, so not using $mail->send($transport);
					if(strstr($recipientEmail['email'],'..')){
						$invalidEmail=1;
						throw new Exception('Invalid Email Address: '.$recipientEmail['email']);
					}
					$mail->send();  
				} catch( Exception $e ){ // Zend_Mail_Transport_Exception $e) {
					
					//was this using php's default mail() function, instead of SMTP mailing? 
					//if so no valuable debugging info will be available. 
					if( stristr($e->getFile(),'Transport/Sendmail.php') && !$invalidEmail ){ 
						$trySMTPmsg=t("Unknown php mail() error.  Please try switching your concrete site to use SMTP mail.");
					}
					
					$l = new Log(LOG_TYPE_EXCEPTIONS, true, true);
					$l->write(t('Mail Exception Occurred. Unable to send mail: ') . $e->getMessage());
					$l->write('line '.$e->getLine().' within '.$e->getFile());					
					$l->write($e->getTraceAsString());
					$l->write($trySMTPmsg);
					$l->write(t('Template Used') . ': ' . $this->template);
					$l->write(t('To') . ': ' . $recipientEmail['email'], $recipientEmail['name'] );
					$l->write(t('From') . ': ' . $this->getSenderEmail() );
					if ($loggingOn) {
						$l->write(t('Subject') . ': ' . $emailSubject);
						$l->write(t('Body') . ': ' . $emailBodyTextUnsubscribe );
					}				
					$l->close();
					
					//mark user as failed
					if( intval($recipientEmail['uID']) ){
						$failedUIDs[]=intval($recipientEmail['uID']);
						$this->markUserAsFailed(intval($recipientEmail['uID']));
					}elseif( intval($recipientEmail['mluID']) ){
						$failedMLUIDs[]=intval($recipientEmail['mluID']);
						$this->markNonUserAsFailed(intval($recipientEmail['mluID']));
					}	
					
					if($invalidEmail){
						continue;
					}else{
						$l->write($trySMTPmsg); 
						throw new Exception( $e->getMessage().' '.$trySMTPmsg );
					}
				}	
				
				// add email to log
				if ($loggingOn) { 
					$l = new Log(LOG_TYPE_EMAILS, true, true);
					if (ENABLE_EMAILS) {
						$l->write('**' . t('EMAILS ARE ENABLED. THIS EMAIL WAS SENT TO mail()') . '**');
					} else {
						$l->write('**' . t('EMAILS ARE DISABLED. THIS EMAIL WAS LOGGED BUT NOT SENT') . '**');
					}
					$l->write(t('Template Used') . ': ' . $this->template);
					$l->write(t('To') . ': ' . $recipientEmail['email'], $recipientEmail['name'] );
					$l->write(t('From') . ': ' . $this->getSenderEmail() );
					$l->write(t('Subject') . ': ' . $emailSubject );
					$l->write(t('Body') . ': ' . $emailBodyTextUnsubscribe ); 
					$l->close(); 
				}	
				
				/*
				$mh = Loader::helper('mail'); 
				$mh->to(  $recipientEmail['email'],  $recipientEmail['name']  );
				$mh->from( $this->getSenderEmail() );  
				$mh->setSubject(  $this->getSubject() );
				$mh->setBody(  $this->getBody().$unsubscribeText  ); 
				$mh->sendMail();
				*/ 
				
				//add this user to a sent users array
				if( intval($recipientEmail['uID']) ){
					$sentUIDs[]=intval($recipientEmail['uID']);
					$this->markUserAsSent(intval($recipientEmail['uID']));
				}elseif( intval($recipientEmail['mluID']) ){
					$sentMLUIDs[]=intval($recipientEmail['mluID']);
					$this->markNonUserAsSent(intval($recipientEmail['mluID']));
				}
				
				$mailsSendSincePause++;
				
			} 
			
			if( !$failed ){ 
				$this->updateStatus( 'completed', '' ); 
			}
			
		}catch(Exception $e){
			$failed=1; 
			$this->updateStatus( 'interrupted', $e->getMessage() ); 
		}  
		
		//close zend mail SMTP transport 
		if($transport && method_exists($transport,'disconnect')) $transport->disconnect();

		return array( 'success'=>!$failed, 'status'=>$this->status, 'msg'=>$this->status_msg, 'startTime'=>$startTime );
	}
	
	
	public function stillRunningCheck(){
		//is this has crashed, we know by checking the last updated time	
		$maxPauseTime= 30 + intval(Config::get('TONY_MAILING_LIST_PAUSE_TIME')); 
		if( ($this->status=='running' || $this->status=='sleeping') && $this->getUpdated() < (time()-$maxPauseTime) )
			$this->updateStatus( 'interrupted', t('Send process crashed. Unknown error.') );
	}
	
	public function updateStatus( $status='', $status_msg='' ){
		
		if( !array_key_exists( $status , $this->statusTypes ) )
			throw new Exception( t('Invalid mailing-list mailing status code, in TonyMailingListMailing::updateStatus()') ); 
		
		$this->status=$status;
		$this->status_msg=$status_msg;
		
		$db = Loader::db();  
		$vals = array( $status, $status_msg, time(), intval($this->mlmID) ); 
		$sql = 'UPDATE TonyMailingListMailings SET status=?, status_msg=?, updated=? WHERE mlmID=?';
		return $db->query( $sql , $vals );	
	}
	
	public function markUserAsSent($uiD=0){
		$db = Loader::db(); 
		$failedUIDsArray = $this->getFailedUIDsArray();
		$newFailedUIDsArray=array();
		foreach($failedUIDsArray as $val)
			if($uiD!=$val) $newFailedUIDsArray[]=$val;
		$this->failedUIDs=join(',',$newFailedUIDsArray);
		$this->sentUIDs = $this->sentUIDs.','.intval($uiD);
		$this->incrementSentCount(); 
		$vals = array( $this->sentUIDs, $this->failedUIDs, $this->getSentCount(), $this->getRecipientsCount(), time(), intval($this->mlmID) ); 
		$sql = 'UPDATE TonyMailingListMailings SET sentUIDs=?, failedUIDs=?, sent_count=?, recipients_count=?, updated=? WHERE mlmID=?';
		return $db->query( $sql , $vals );
	}

	public function markNonUserAsSent($mluiD=0){
		$db = Loader::db(); 
		$failedMLUIDsArray = $this->getFailedMLUIDsArray(); 
		$newFailedMLUIDsArray=array();
		foreach($failedMLUIDsArray as $val)
			if($mluiD!=$val) $newFailedMLUIDsArray[]=$val;
		$this->failedMLUIDs=join(',',$newFailedMLUIDsArray);
		$this->sentMLUIDs = $this->sentMLUIDs.','.intval($mluiD);
		$this->incrementSentCount(); 
		$vals = array( $this->sentMLUIDs, $this->failedMLUIDs, $this->getSentCount(), $this->getRecipientsCount(), time(), intval($this->mlmID) ); 
		$sql = 'UPDATE TonyMailingListMailings SET sentMLUIDs=?, failedMLUIDs=?, sent_count=?, recipients_count=?, updated=? WHERE mlmID=?';
		return $db->query( $sql , $vals );
	}
	
	public function markUserAsFailed($uID=0){
		$db = Loader::db(); 
		$failedUIDsArray = $this->getFailedUIDsArray();
		if(intval($uID)) $failedUIDsArray[]=intval($uID); 
		$this->failedUIDs = join(',',$failedUIDsArray); 
		$vals = array( $this->failedUIDs , $this->getSentCount(), $this->getRecipientsCount(), time(), intval($this->mlmID) ); 
		$sql = 'UPDATE TonyMailingListMailings SET failedUIDs=?, sent_count=?, recipients_count=?, updated=? WHERE mlmID=?';
		return $db->query( $sql , $vals );
	}

	public function markNonUserAsFailed($mluID=0){
		$db = Loader::db(); 
		$failedMLUIDsArray = $this->getFailedMLUIDsArray();
		if(intval($mluID)) $failedMLUIDsArray[]=$mluID;
		$this->failedMLUIDs = join(',',$failedMLUIDsArray);
		$vals = array( $this->failedMLUIDs , $this->getSentCount(), $this->getRecipientsCount(), time(), intval($this->mlmID) ); 
		$sql = 'UPDATE TonyMailingListMailings SET failedMLUIDs=?, sent_count=?, recipients_count=?, updated=? WHERE mlmID=?';
		return $db->query( $sql , $vals );
	}	
	
	public function getRecipientsData(){ 
		return TonyMailingList::getRecipientEmails( $this->getRecipients(), $this->getGIDs(), $this->getWhiteListAttrId(), $this->getBlackListAttrId()  );
	}
	
	public function triggerSendProcess( $force=0 ){
	
		$this->stillRunningCheck();  
		if( ($this->getStatus()!='completed' && $this->getStatus()!='running' && $this->getStatus()!='sleeping') || intval($force)  && $this->getStatus()!='draft' ){ 
			
			if( TonyMailingList::cURL_installed() && (!defined('MAILING_LIST_INLINE_SEND') || !MAILING_LIST_INLINE_SEND) ){   
				
				//is curl working? 
				$ch = curl_init( TonyMailingList::getCurlCheckURL( $this->getId() ) );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HEADER, 0); 
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,20);  
				curl_setopt($ch, CURLOPT_TIMEOUT,20);
				$result = curl_exec($ch); 
				curl_close($ch);  
				
				//could curl make a connection?
				if(trim($result)=='connected'){  
					//let the server start a new process with cUrl, so it doesn't tie up the user's assigned process   
					$ch = curl_init( TonyMailingList::getSendPendingURL( $this->getId() ) );
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HEADER, 0); 
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,1);  
					curl_setopt($ch, CURLOPT_TIMEOUT,1);
					$result = curl_exec($ch); 
					curl_close($ch); 				
					return true; 
				}else{
					$this->cUrlError=1; 
				} 
			}
				
			//incase cUrl is off, or if it couldn't connect  
			if( $this->getStatus()!='running' )  $this->send(); 
		}	 	
		
		return true; 
	
	}
	
	public function delete(){
		$db = Loader::db(); 
		$vals = array( intval($this->mlmID) ); 
		$sql = 'DELETE FROM TonyMailingListMailings WHERE mlmID=?';
		return $db->query( $sql , $vals );
	}
}


function mailing_list_track_links_urlencode($a){
	return 'href="TRACK_LINK_URL'.urlencode($a[1]).'"';
}

function mailing_list_track_links_urlencode_text($a){
	return '[link: TRACK_LINK_URL'.urlencode($a[1]).' ]';
}

?>