<?php 

defined('C5_EXECUTE') or die(_("Access Denied."));


Loader::model('tony_mailing_list','tony_mailing_list'); 

class DashboardMailingListSendController extends Controller {
	
	
	function __construct(){  
		$html = Loader::helper('html');
		@$this->addHeaderItem($html->css('ccm.filemanager.css'));
		@$this->addHeaderItem($html->javascript('jquery.ui.js'));  
		@$this->addHeaderItem($html->javascript('ccm.filemanager.js')); 
		$this->addHeaderItem('<script type="text/javascript">$(function() { ccm_activateFileManager(\'DASHBOARD\');ccm_setupAdvancedSearchFields();ccm_setupAdvancedSearch("file"); });</script>');		
		@$this->addHeaderItem( $html->javascript('tiny_mce/tiny_mce.js'), 'CONTROLLER');
		@$this->addHeaderItem($html->javascript('mailing_list_send.js','tony_mailing_list')); 
	}
	
	
	public function view( $mlmid=0 ){
		
		if( intval(str_replace(array('M','m'),'',ini_get('memory_limit')))<=256 && !ini_get('safe_mode')  )  
			ini_set('memory_limit', '256M');  	
		
		Loader::model('search/group');  
		$gl = new GroupSearch();
		$gl->updateItemsPerPage(0);
		$gl->sortBy('gName', 'asc');
		$gResults = $gl->getPage(); 		
		$this->set('gResults',$gResults); 
		
		$this->set('allowAllUsersMailing', intval(Config::get('TONY_MAILING_LIST_ALL_USERS_MAILING')) ); 
		
		$this->set('enableMailingsGIDs', explode(',',Config::get('TONY_MAILING_LIST_ENABLE_MAIL_GIDS')) );  
		
		//Load mailing object
		if(!intval($mlmid)) $mlmid = intval($_REQUEST['mlmid']);
		if( $mlmid ){ 
			$this->mailing = TonyMailingListMailing::getById( $mlmid );
			if( !is_object($this->mailing) || !$this->mailing->getId() )
				throw new Exception( t('Mailing not found.') );
			//security check: must be sender or an administrator 
			$u = new User();
			$adminGroup = Group::getbyId(ADMIN_GROUP_ID);
			
			$page = Page::getCurrentPage();
			$permissions = new Permissions($page); 
			
			if( $u->uID != intval($this->mailing->getSenderUID()) && !$u->inGroup($adminGroup) && !$u->isSuperUser() && !$permissions->canWrite() ) 
				throw new Exception( t("You don't have permission to edit this mailing. You must either be in the administrators group or be the sender.") );
		}else{
			$this->mailing = new TonyMailingListMailing();  
			
			if( defined('MAILING_LIST_FROM_DEFAULT') )
				$this->mailing->setSender('other');
		}
		
		if($_REQUEST['template']){ 
			$this->mailing->setId(0);
			
		}elseif( $this->mailing->getStatus()!='pending' && $this->mailing->getStatus()!='draft' ){
			throw new Exception( t("Sorry, but you can't edit mailings that are no longer pending and are not drafts.") );
		}
		
		$this->set( 'mailing', $this->mailing); 
		
	}
	
	public function preview(){ 
		
		if( !$_SESSION['MAILING_HTML_PREVIEW'] )
			throw new Exception( t('No preview for this mailing was found') );
		
		echo $_SESSION['MAILING_HTML_PREVIEW'];
		die; 
		
	}	
	
	
	public function submit(){
		
		$this->view();
		
		$strHelper=Loader::helper('validation/strings');
		
		$errorMsgs=array(); 
		$mailing = $this->mailing;
		
		//VALIDATE 
		
		//get email addresses
		$checkedGIDs=array();
		$recipientsList = array();
		$mailing->setRecipients( $_REQUEST['recipients'] );
		$mailing->setWhiteListAttrId( $_REQUEST['whiteListAttrId'] ); 
		$mailing->setBlackListAttrId( $_REQUEST['blackListAttrId'] );  
		
		if( $mailing->getRecipients()=='groups' ){ 
			$mailing->setGIDs( $_POST['gID'] );  
			if( !count( $mailing->getGIDs() )  ){ 
				$errorMsgs[]=t('You must select at least one group, or select the "Registered Users" option'); 
			}else{
				$this->set('checkedGIDs', $mailing->getGIDs() );  
			}
		}elseif( $mailing->getRecipients()=='all' ){ 
			//just registered users  
		}else{
			$errorMsgs[]=t('Invalid Recipient Setting'); 
		}


		//SENDER/FROM EMAIL ADDRESS
		$u = new User();
		if( !intval($mailing->getSenderUID()) ) $mailing->setSenderUID($u->uID);
		$mailing->setSender( $_REQUEST['sender'] );
		$mailing->setSenderEmail( $_REQUEST['sender_other'] ); 
		$mailing->setSenderName( trim($_REQUEST['sender_name']) ); 
		//is the sender specified?
		if( !strlen($mailing->getSender()) ){
			$errorMsgs[]=t('Please specify who this mailing is from'); 
		//if the sender is typing a custom email, then is it valid?
		}elseif( $mailing->getSender()!='this_user' && !$strHelper->email( $mailing->getSenderEmail() ) ){
			$errorMsgs[]=t('Invalid sender email address');
		//add the custom email to 
		}elseif( $mailing->getSender()=='this_user' ){  
			$ui = UserInfo::getById($u->uID);
			$fromEmail = $ui->getUserEmail();	
			$mailing->setSenderEmail( $fromEmail ); 
		}
		
		if(get_magic_quotes_gpc()){
			 $subject = stripslashes($_POST['subject']);
			 $body = stripslashes($_POST['body']);
		}else{ 
			$subject = $_POST['subject'];
			$body = $_POST['body'];
		}

		$mailing->setSubject( $subject );
		if( !strlen( $mailing->getSubject() ) ){
			$errorMsgs[]=t('You must include a subject');  
		}

		$mailing->setBody( $body );
		if( !strlen( $mailing->getBody() ) ){
			$errorMsgs[]=t('You must include some body text'); 
		}	
		 
		if( $_SESSION['mailing_list_last_sent']==( $mailing->getSubject().' '.$mailing->getBody() ) && !$mailing->getId() ){
			$errorMsgs[]=t('A mailing with same subject and body has already been created!'); 
		}
		
		$cleanFIDs=array();
		$dirtyFIDs = $_POST['fileAttachmentFIDs'];
		if(is_array($dirtyFIDs)) foreach($dirtyFIDs as $dirtyFID) $cleanFIDs[]=intval($dirtyFID); 
		$mailing->setAttachments( join(',',$cleanFIDs) );
		
		if($mailing->getStatus()=='draft' || $mailing->getStatus()=='pending' ){
			$newStatus=($_REQUEST['saveDraft'])?'draft':'pending';
			$mailing->setStatus($newStatus);
		}
		
		if( count($errorMsgs) && !$_REQUEST['saveDraft'] ){
			$this->set('errorMsgs',$errorMsgs);
			
		}elseif( $_REQUEST['makeChanges'] && !$_REQUEST['saveDraft'] ){
			//back to edit screen, with no message 
			
		}elseif( !$_REQUEST['preview'] && !$_REQUEST['saveDraft'] ){ 
			$this->set('previewMode', 1);
			
			$previewHTML = TonyMailingList::getHeaderHTML().$mailing->getBody().TonyMailingList::getFooterHTML(); 
			$userAttrReplacedText = TonyMailingListMailing::userAttributeTextReplacement( $previewHTML, $u );
			$absoluteLinksText=TonyMailingListMailing::relativeToAbsoluteLinks( $userAttrReplacedText );
			$_SESSION['MAILING_HTML_PREVIEW']=$absoluteLinksText;
			
		}else{
			
			//load all the email addresses for these groups  
			$recipientEmails = TonyMailingList::getRecipientEmails( $mailing->getRecipients(), $mailing->getGIDs(), $mailing->getWhiteListAttrId(), $mailing->getBlackListAttrId()  ); 
			$recipientCount= count($recipientEmails);
			$mailing->setRecipientsCount($recipientCount);
			
			//save mailing to database   
			$mailing->save();  
			
			//send emails straight away, if configured as such 
			//this option is not available if cURL is't installed, because it can take a super long time, and would be better to give a response. 
			if( $_REQUEST['mode']!='edit' && intval(Config::get('TONY_MAILING_LIST_SEND_ON_CREATE')) && TonyMailingList::cURL_installed() && ($mailing->getStatus()=='pending' || $mailing->getStatus()=='interrupted') )
				$mailing->triggerSendProcess(); 
			
			$_SESSION['mailing_list_last_sent'] = $mailing->getSubject().' '.$mailing->getBody();
			$_POST['subject']='';
			$_POST['body']='';
			
			$mailingsPage = Page::getByPath('/dashboard/mailing_list/mailings'); 
			$cp = new Permissions($mailingsPage);
			if( is_object($cp) && $cp->canRead() ) 
				$this->redirect('/dashboard/mailing_list/mailings/','detail',$mailing->getId(),1 ); 
			else $this->set('noAccessToMailings',1);
			
			//this is left over.  keeping it here for now incase i decide not to do a redirect.
			$this->set( 'saved',1);
			$this->set( 'savedMailingId' , $mailing->getId() );
			$mailing = new TonyMailingListMailing(); 
			$this->set( 'mailing', $mailing);
			$this->set( 'recipientCount', $recipientCount );
			
		}
		
	} 
	
	public function export(){
		
		$txtHelper = Loader::helper('text'); 
		$tempUserInfo = new UserInfo();
		$tempUserInfo->uID=0; 
		
		if( intval(str_replace(array('M','m'),'',ini_get('memory_limit')))<=256 && !ini_get('safe_mode')  )  
			ini_set('memory_limit', '256M');  	
		
		//get email addresses 
		$checkedGIDs=array();
		if( $_REQUEST['recipients']=='groups' ){
			$checkedGIDs = $_REQUEST['gID'];
			if( !is_array($checkedGIDs) || !count($checkedGIDs)  ){ 
				$errorMsgs[]=t('You must select at least one group');
			}else{
				$this->set('checkedGIDs',$checkedGIDs); 
				//load all the email addresses for these groups 
			}
		}elseif( $_REQUEST['recipients']=='all' && !intval(Config::get('TONY_MAILING_LIST_ALL_USERS_MAILING')) ){
			throw new Exception( t('Mailings to all users is currently disabled.') );
			
		}elseif( $_REQUEST['recipients']!='all' ){
			throw new Exception( t('Invalid Recipient Setting') ); 
		}	
		
		$recipients = TonyMailingList::getRecipientEmails( $_REQUEST['recipients'], $checkedGIDs, intval($_REQUEST['whiteListAttrId']), intval($_REQUEST['blackListAttrId']) );
		
		if( !is_array($recipients) || !count($recipients) ){
		
			echo t('No users found.');		
		
		}elseif(strtolower($_REQUEST['export_mode'])=='excel'){
			
			header("Content-Type: application/vnd.ms-excel");
			header("Cache-control: private");
			header("Pragma: public");
			$date = date('Ymd');
			header("Content-Disposition: inline; filename=mailing_list_export_{$date}.xls"); 
			header("Content-Title: Mailing List Export - Run on {$date}");	
			
			echo "<table>\r\n";		 
			
			$rows = array();
			$userAttrs = array();
			$columnNames = array(); 
			foreach($recipients as $recipient){
				
				$row=array(); 
				$row['email']=$recipient['email'];
				$uID = intval($recipient['uID']); 
				$row['uID']= $uID;
				$mluID = intval($recipient['mluID']); 
				$row['mluID']= $mluID; 
				
				//get user attributes for non-registered users 
				if( $mluID ){ 
					$nonUser = TonyMailingListNonUser::getById( $mluID ); 
					
					foreach($nonUser->getAttrData() as $attributeHandle => $attributeValueData){ 			
						
						if( array_key_exists( $attributeHandle, $userAttrs) ){
							$userAttr = $userAttrs[$attributeHandle];
						}else{
							$userAttr = UserAttributeKey::getByHandle( $attributeHandle ); 
							$userAttrs[$attributeHandle]= $userAttr; 
						}
						
						if( !is_object($userAttr) ) continue; 
						
						//custom attributes
						if(is_array($attributeValueData)){  
							
							$attrType = $userAttr->getAttributeType();
							if( !is_object($attrType) ) continue;  
							$classname = $txtHelper->camelcase( $attrType->getAttributeTypeHandle() ).'AttributeTypeValue'; 
							
							if( class_exists($classname) && method_exists($classname,'__toString')){ 	
								$attributeValueObj = new $classname();
								if(is_object($attributeValueObj)){
									$attributeValueObj->setPropertiesFromArray($attributeValueData);  
									$attributeValue = $attributeValueObj->__toString();
								}
							}else{  
								$_POST['akID'][intval($userAttr->getAttributeKeyID())]=$attributeValueData;
								$userAttr->saveAttributeForm( $tempUserInfo ); 
								$val = $tempUserInfo->getAttributeValueObject($userAttr); 
								if(is_object($val)) $attributeValue = $val->getValue('display'); 
							}	
							
						}elseif(strlen($attributeValueData)){
							//non-user attribute: email address for example 
							$attributeValue = $attributeValueData;
						} 
						
						if(!$attributeValue) continue;  
						
						if( !array_key_exists($attributeHandle,$columnNames) ) 
							$columnNames[$attributeHandle]=$userAttr->getAttributeKeyName();
						
						$row[$attributeHandle]= $attributeValue; 
					}
					
				}elseif($uID && is_object($recipient['ui']) ) {
					
					$recipientUI = $recipient['ui']; 
					
					$attributeKeys = AttributeKey::getList('user');
					foreach($attributeKeys as $ak){  
					
						$attributeHandle = $ak->getAttributeKeyHandle(); 
						
						$attributeValue = $recipientUI->getAttribute( $attributeHandle ); 
						
						if(!$attributeValue) continue;  
						
						if( !array_key_exists($attributeHandle,$columnNames) ) 
							$columnNames[$attributeHandle]=$ak->getAttributeKeyName();
						
						$row[$attributeHandle]= $attributeValue; 
					} 
					
				}				
				
				$rows[]=$row; 
			}
			
			echo '<tr>';
			    echo '<td>'.t('Email').'</td><td>'.t('User ID').'</td><td>'.t('Unregistered User ID').'</td>';
			foreach($columnNames as $columnKey=>$columnName){ 
				echo '<td>' . $columnName . "</td> \r\n";
			} 
			echo '</tr>';
			
			foreach($rows as $row){ 
				echo '<tr>';
				echo '<td>' . $row['email'] . "</td> \r\n";
				echo '<td>' . $row['uID'] . "</td> \r\n";
				echo '<td>' . $row['mluID'] . "</td> \r\n";
				foreach($columnNames as $columnKey=>$columnName){ 
					if( $columnKey=='unsubscribe_data' ) continue; 
					echo '<td>' . $row[$columnKey] . "</td> \r\n";
				} 
				echo '</tr>'; 
			}
			
			echo '</table>';
			
		}else{
			foreach($recipients as $recipient){
				if($notfirst) echo ", <br>\r\n";
				echo $recipient['email'];
				$notfirst=1;
			}
		}
		
		die;
	}
	
	function recipient_count(){
		
		//get email addresses 
		try{
			$checkedGIDs=array();
			if( $_REQUEST['recipients']=='groups' ){
				$checkedGIDs = $_REQUEST['gID'];
				if( !is_array($checkedGIDs) || !count($checkedGIDs)  ){ 
					$errorMsgs[]=t('You must select at least one group');
				}else{
					$this->set('checkedGIDs',$checkedGIDs); 
					//load all the email addresses for these groups 
				}
			}elseif( $_REQUEST['recipients']=='all' ){
				//load every email address
			}else{
				$jsonData['error'] = t('Invalid Recipient Setting'); 
			}	
			
			$recipients = TonyMailingList::getRecipientEmails( $_REQUEST['recipients'], $checkedGIDs, intval($_REQUEST['whiteListAttrId']), intval($_REQUEST['blackListAttrId']) );
		}catch(Exception $e){
			$jsonData['error'] = $e->getMessage();
		}		
		
		$json = Loader::helper('json'); 
		$jsonData['count']=count($recipients);
		echo $json->encode( $jsonData );
		die;
	}
	
	public function text_replacement(){
		
		$userAttrDefaults = unserialize(Config::get('TONY_MAILING_LIST_USER_ATTRIBUTE_DEFAULTS'));
		if(!is_array($userAttrDefaults)) $userAttrDefaults=array();
		
		echo '<div style="margin-bottom:16px;"><form id="mailingUserAttributeForm" onsubmit="return false;">';
		
		echo '<div id="mailingUserAttrsSaved" class="ccm-notification"><a style="float:right;" onclick="jQuery.fn.dialog.closeTop();">'.t('Close Window [X]').'</a>'.t('Changes Saved!').'</div>';
		
		echo '<div class="note" style="font-size:12px; line-height:16px;">';
		echo t('To insert user attributes (first name, last name, etc) into your mailings, type the attribute handle between two percentage signs (shown below) into the body of your mailing. ');
		echo t('You can also enter default values to use if there is no value for a particular user. ');
		echo t('To add new user attributes, visit the %susers section%s of the dashboard.','<a target="_blank" href="'.View::url('/dashboard/users/attributes/').'">','</a>');
		echo '</div>';
		
		echo '<table id="userAttributesList" >';
			echo '<tr style="font-weight:bold">';
				echo '<td style="width:70%">User Attribute / <span class="note">Handle</span></td>';
				echo '<td>Default Value</td>';
			echo '</tr>';			
			
			foreach( UserAttributeKey::getList() as $userAttr){ 
				echo '<tr>';
					echo '<td>'.$userAttr->getAttributeKeyName().'';
					echo '<div class="note">%'.$userAttr->getAttributeKeyHandle().'%</div></td>';
					$val = htmlentities( $userAttrDefaults['ua_'.$userAttr->getAttributeKeyID()], ENT_QUOTES, 'UTF-8');
					echo '<td><input name="ua_'.$userAttr->getAttributeKeyID().'" type="text" value="'.$val.'" /></td>';
				echo '</tr>';
			} 
			
			
			echo '<tr>';
				echo '<td>'.t('Email').'';
				echo '<div class="note">%email%</div></td>';
				$val = htmlentities( $userAttrDefaults['ua_email'], ENT_QUOTES, 'UTF-8');
				echo '<td><input name="ua_email" type="text" value="'.$val.'" /></td>';
			echo '</tr>';				
			
			echo '<tr>';
				echo '<td>'.t('User Name').'';
				echo '<div class="note">%user_name%</div></td>';
				$val = htmlentities( $userAttrDefaults['ua_user_name'], ENT_QUOTES, 'UTF-8');
				echo '<td><input name="ua_user_name" type="text" value="'.$val.'" /></td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>'.t('Date Stamp').'';
				echo '<div class="note">%date_stamp%</div></td>'; 
				echo '<td>' . TonyMailingList::getDateStamp() . '</td>';
			echo '</tr>';

		echo '</table>';
		
		echo '</form></div>';
		
		$jsh = Loader::helper('concrete/interface'); 
		echo '<div class="ccm-spacer"></div>';
		
		print $jsh->button_js(t('Save'), 'MailingList.saveAttributes()', 'right');
		print $jsh->button_js(t('Close'), 'jQuery.fn.dialog.closeTop()', 'left');
		
		die;
	}
	
	public function save_attr_defaults(){ 
		$userAttrDefaults = unserialize(Config::get('TONY_MAILING_LIST_USER_ATTRIBUTE_DEFAULTS')); 
		if(!is_array($userAttrDefaults)) $userAttrDefaults=array();
		
		foreach($_POST as $key=>$val){
			if( !strstr($key,'ua_') ) continue;
			$key = preg_replace("/[^a-z0-9\-\_]/i", '', str_replace('ua_','',$key) );
			$userAttrDefaults['ua_'.$key]=$val; 	
		}
		
		Config::save('TONY_MAILING_LIST_USER_ATTRIBUTE_DEFAULTS', serialize($userAttrDefaults) );  
		
		$json = Loader::helper('json'); 
		$jsonData['success']=1;
		echo $json->encode( $jsonData );
		die; 
	}
	
}

?>