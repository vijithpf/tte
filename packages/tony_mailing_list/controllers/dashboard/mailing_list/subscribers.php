<?php 

defined('C5_EXECUTE') or die(_("Access Denied."));


Loader::model('tony_mailing_list','tony_mailing_list'); 

class DashboardMailingListSubscribersController extends Controller {  
	
	function __construct(){  
		$html = Loader::helper('html');
		$this->addHeaderItem($html->javascript('mailing_list_subscribers.js','tony_mailing_list')); 
	}	
	
	public function view(){  
	
		if( intval(ini_get('max_execution_time'))<=300 && !ini_get('safe_mode')  ) 
			set_time_limit( 300 ); 
			
		if( intval(str_replace(array('M','m'),'',ini_get('memory_limit')))<=256 && !ini_get('safe_mode')  )  
			ini_set('memory_limit', '256M');  	
	
		global $c;	
		
		$page_base=$c->getCollectionPath();
		if( !strlen($page_base) ) $page_base='/?cID='.$c->getCollectionID(); 
		if(!strstr($page_base,'?')) $page_base.='?';	
		
		//get all available groups 
		Loader::model('search/group');  
		$gl = new GroupSearch();
		$gl->updateItemsPerPage(0);
		$gl->sortBy('gName', 'asc');
		$gResults = $gl->getPage(); 		
		$this->set('gResults',$gResults); 
		
		//all enabled mailing list groups
		$enabledMailingLists = explode(',',Config::get('TONY_MAILING_LIST_ENABLE_MAIL_GIDS')); 
		//just requested mailing list groups
		if( $_REQUEST['gID']=='unsubscribers'){ 
			$showUnsubscribers=1;
		}elseif(intval($_REQUEST['gID']) && in_array(intval($_REQUEST['gID']),$enabledMailingLists)){   
			$mailingGIDs=array( intval($_REQUEST['gID']) );
		}else{
			$mailingGIDs=$enabledMailingLists; 
		}
		
		//get recipient list
		if($showUnsubscribers)
			 $recipients = TonyMailingList::getUnsubscribers(); 
		else $recipients = TonyMailingList::getRecipientEmails('groups',$mailingGIDs); 
		
		$alphabeticalRecips=array();
		foreach($recipients as $recipient){ 
			$alphabeticalRecips[ trim($recipient['email']) ]=$recipient; 
		}
		uksort($alphabeticalRecips,"strnatcasecmp"); 
		
		$this->set('recipients',$alphabeticalRecips); 
		$this->set('mailingGIDs',$mailingGIDs); 
		$this->set('enabledMailingLists',$enabledMailingLists); 
		$resultCount=count($alphabeticalRecips);
		$this->set('resultCount',$resultCount); 
		
		//pagination 
		$pageNum=(intval($_REQUEST['pg'])>0)?intval($_REQUEST['pg']):1; 
		$pageSize= (intval($_REQUEST['pageSize'])>0 ) ? intval($_REQUEST['pageSize']) : 20;
		$paging_qstr = '&gID='.urlencode($_REQUEST['gID']).'&pageSize='.$pageSize; 
		$paging_url = $page_base.$paging_qstr; 
		$pagination = Loader::helper('pagination');	 
		$pagination->queryStringPagingVariable='pg';	
		$pagination->init( $pageNum, $resultCount, View::url($paging_url), $pageSize ); 
		$recipientsPage = $pagination->limitResultsToPage($alphabeticalRecips);	
		$this->set('recipientsPage',$recipientsPage); 
		$this->set('paginator',$pagination); 
		$this->set('pageSize',$pageSize); 
		$this->set('showUnsubscribers',$showUnsubscribers); 
		
	}
	
	public function import(){
		
		$token = Loader::helper('validation/token'); 
			
		if ($token->validate("mailing_list_import")) {
			
			$filename=$_POST['emails_file']; 
			$tmpFilePath=$_FILES['emails_file']['tmp_name']; 
			$importAttributes = intval($_REQUEST['importAttributes']);
			
			$allowedGIDs = explode(',',Config::get('TONY_MAILING_LIST_ENABLE_MAIL_GIDS')); 
			$gID = intval($_REQUEST['gID']); 
			
			$attributeColumns=array();
			if($importAttributes){ 
				foreach($_REQUEST['attributeColumn'] as $key=>$val){
					if(in_array($val,$attributeColumns) && strlen($val)){
						$attributesRepeated=1;	
						break;
					}
					$attributeColumns[]=$val;
				}
			}
			
			if( !intval($_REQUEST['agreed']) ){
				$this->set('errorMsg', t("You must agree that you're not using this for spam.") );	
				
			}elseif($importAttributes && $attributesRepeated){
				$this->set('errorMsg', t('Error: Each of your columns must be unique or ignored.') );					
				
			}elseif( !intval($gID) || !in_array($gID,$allowedGIDs) ){ 
				$this->set('errorMsg', t('Please select a group') );
				
			}elseif( !file_exists($tmpFilePath) ){  
				$this->set('errorMsg', t('Error: File not found. Please try again') );	
				
			}else{
				
				//up the max execution time if it's low 
				$maxExecTime =  Config::get('TONY_MAILING_LIST_MAX_TIME');
				if( intval($maxExecTime) && intval($maxExecTime) > intval(ini_get('max_execution_time')) && !ini_get('safe_mode')  ) 
					set_time_limit( $maxExecTime );
				
				//load group
				$g=Group::getById($gID); 
				if( !is_object($g) || !intval($g->getGroupID()) )
					throw new Exception('Invalid Group');
				
 				if($importAttributes) 
					 $success=$this->importEmailsAndAttributes($tmpFilePath,$g,$attributeColumns);
				else $success=$this->importEmailsOnly($tmpFilePath,$g); 	
 
				$this->set('import_success',$success);
				$this->set('usersAdded',$this->usersAdded);
			}	
			
		}else{
			$this->set('errorMsg', $token->getErrorMessage() );	
		}
		
		$this->view();
	}
	protected $usersAdded=0;
	
	protected function importEmailsOnly($tmpFilePath,$g){
		$emails = $this->parseCSV($tmpFilePath);
		$gID=$g->getGroupID();
			
		foreach( $emails as $email ){
			//does this user exist 
			$ui = UserInfo::getByEmail( $email );	
			if( (is_object($ui) && intval($ui->uID)) ){  
				TonyMailingList::addRegisteredUserToGroup( $ui, $g); 
			}else{
				$nonUser = TonyMailingListNonUser::getByEmail($email); 
				if(!$nonUser){
					$nonUser = new TonyMailingListNonUser( array('email'=>$email) ); 
					$nonUser->create();
				} 
				$nonUser->addGroup( $gID ); 
				$nonUser->update(); 
			} 
			$this->usersAdded++;
		} 
		
		return true;
	}
	/*
	//used for automatically generating emails, for testing purposes
	public function generate(){
		for($i=1;$i<=5000;$i++){
			$email=$i.'@test.com';
			$nonUser = new TonyMailingListNonUser( array('email'=>$email) ); 
			$nonUser->create();
			$nonUser->addGroup( 4 ); 
			$nonUser->update(); 
		}	
	}
	*/
	public $csvSeparator = ",";
	protected function importEmailsAndAttributes($filePath,$g,$attributeColumns){
		
		$results=array();
		$gID=$g->getGroupID();
		
		if(!strstr(strtolower($_FILES['emails_file']['name']),'.csv')){
			$this->set('errorMsg', t('Error: Your file must be in .csv format.') );	
			return false;
		}
		
		$usersData=array();
		
		$handle = fopen($filePath, "r"); 
		$stringsHelper = Loader::helper('validation/strings');
		$attributeColumnCount=count($attributeColumns);
		
		while (($data = fgets($handle)) !== FALSE){ 
			
			$dataLine = explode('%NEWLINE%', str_replace( array("\r","\n","<br />"), '%NEWLINE%', nl2br($data) )); 
			
			foreach($dataLine as $dataLine2){ 
			
				$joinedStr= str_replace( $this->csvSeparator, '%%%', $dataLine2); 		
				$joinedStr= mb_convert_encoding( $joinedStr, 'UTF-8') ;
				$data=explode('%%%',$joinedStr);
				
				$userData=array();
				$column=0;				
		
				foreach($data as $vals){
					
					$moreData = explode('%%%',str_replace(array(",","%"),'%%%',$vals));
					
					foreach($moreData as $smallVal){
						$attrKey=$attributeColumns[$column];
						if($attrKey){
							//echo  '::'.$attrKey.' '.$smallVal."<br />\n\r"; 
							$userData[$attrKey]=trim($smallVal);
						}
						$column++;
						if($column+1>$attributeColumnCount) break;
					}
					
					
					
				}
				
				$usersData[]=$userData;
			
			}
			
			
		}  
		fclose($handle);
		
		//loop through imported users
		foreach($usersData as $userData){
			$email = $userData['email'];
			
			if(!$email || !$stringsHelper->email($email)) continue; 
			
			//does this user exist? registered user check
			$ui = UserInfo::getByEmail( $email );	
			if( (is_object($ui) && intval($ui->uID)) ){  
				TonyMailingList::addRegisteredUserToGroup( $ui, $g); 
				//loop through each column, adding the user attributes
				foreach($userData as $key=>$val){
					if(!$key || !$val || $key=='email') continue; 
					$ui->setAttribute($key,$val);
				}
			}else{
				//is this an existing unregistered user? 
				$nonUser = TonyMailingListNonUser::getByEmail($email); 
				if(!$nonUser){
					$nonUser = new TonyMailingListNonUser( array('email'=>$email) ); 
					$nonUser->create();
				} 
				$nonUser->addGroup( $gID ); 
				$userAttrData=$nonUser->getAttrData();
				foreach($userData as $key=>$val){
					$userAttr = UserAttributeKey::getByHandle( $key );			
					if( !is_object($userAttr) ) continue;
					$attrType = $userAttr->getAttributeType();
					if( !is_object($attrType) ) continue;
					if(!$key || !strlen($val) || $key=='email') continue; 
					if($attrType->getAttributeTypeHandle() == 'boolean' ){ 
						if(strtolower($val)=='yes' || strtolower($val)=='true') 
							 $val = 1;
						else $val = intval($val);
					}
					$userAttrData[$key]['value']=$val;
				}
				$nonUser->setAttrData($userAttrData);
				$nonUser->update(); 
			} 
			$this->usersAdded++;
		}
		
		return true; 
	}
	
	protected function parseCSV($filePath){		
		$results=array();
		
		$isCSV=strstr(strtolower($_FILES['emails_file']['name']),'.csv');
		
		$stringsHelper = Loader::helper('validation/strings');  
		if( $isCSV ){ 
			$handle = fopen($filePath, "r"); 
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE){ 
				$joinedStr=join('%%%',$data);		
				$joinedStr= mb_convert_encoding( $joinedStr, 'UTF-8') ;
				$data=explode('%%%',$joinedStr);  
				foreach($data as $val){
					$moreData = explode('%%%',str_replace(array(" ",",","\r","\n","%"),'%%%',$val));
					foreach($moreData as $smallVal){
						$smallVal=trim($smallVal); 
						if( $stringsHelper->email($smallVal)){
							$results[]=$smallVal;
						}
					}
				}
			}  	
			fclose($handle);
		}else{
			$data = file_get_contents($filePath);  
			//$encoding = mb_detect_encoding($data);			
			//$data= mb_convert_encoding( $data, 'UTF-8') ;//,$encoding ); 
			$moreData = explode('%%%',str_replace(array("\\","\\r","\\n"," ",",","\r","\n","%"),'%%%',$data));
			foreach($moreData as $smallVal){ 
				$smallVal=trim($smallVal); 
				if( $stringsHelper->email($smallVal)){
					$results[]=$smallVal;
				}
			} 
		}
		
		return $results;
	} 	
	
	public function unsubscribe(){
		
		$token = Loader::helper('validation/token'); 
		
		if( !$_REQUEST['mode'] ){
			
			$this->set('unsubscribeErrorMsg', t('Are you subscribing or unsubscribing this user?') );	 
			
		}elseif( $_REQUEST['mode']=='subscribe' && !intval($_REQUEST['agreed'])  ){
			
			$this->set('unsubscribeErrorMsg', t("You must agree that you're not using this for spam.") );	
		
		}elseif ($token->validate("mailing_list_blacklist")) {
			
			$unsubscribeGID= $_REQUEST['unsubscribe_gID'];
			$enabled_gIDs = explode(',',Config::get('TONY_MAILING_LIST_ENABLE_MAIL_GIDS'));
			
			$submittedEmail= $_REQUEST['unsubscribe_email'];
			$stringsHelper = Loader::helper('validation/strings');
			
			if( $unsubscribeGID!=-1 && !in_array($unsubscribeGID,$enabled_gIDs) ){
				$this->set('unsubscribeErrorMsg', t('Invalid mailing list group') );	
			}elseif(!$stringsHelper->email($submittedEmail)){
				$this->set('unsubscribeErrorMsg', t('Invalid email address') );	
			}else{
			
				//SUBSCRIBE
				if( $_REQUEST['mode']=='subscribe' ){ 
					
					$ui = UserInfo::getByEmail( $submittedEmail ); 
					if( is_object($ui) ){
						$g=Group::getById($unsubscribeGID);  
						TonyMailingList::addRegisteredUserToGroup( $ui, $g);
					}else{  
						$nonUser = TonyMailingListNonUser::getByEmail($submittedEmail); 	
						if(!$nonUser){ 
							$nonUser = new TonyMailingListNonUser( array('email'=>$submittedEmail,'attrData'=>array() ) ); 
							$nonUser->create(); 
						} 
						$nonUser->addGroup($unsubscribeGID); 
						$nonUser->blacklist=0;
						$nonUser->update(); 
						
						
						//install a bunch of test email addresses
						/*
					    for($i=0;$i<10000;$i++){ 
						    $submittedEmail = date('U').'_'.rand(100,1000000).'@'.rand(100,1000000).''.rand(100,1000000).'.com';   
							$nonUser = TonyMailingListNonUser::getByEmail($submittedEmail); 	
							if(!$nonUser){  
								$nonUser = new TonyMailingListNonUser( array('email'=>$submittedEmail,'attrData'=>array() ) ); 
								$nonUser->create(); 
							} 
							$nonUser->addGroup($unsubscribeGID); 
							$nonUser->update(); 
						} 
						*/ 
						
					} 
					$this->set('subscribe_success',1);
					
				
				//UNSUBSCRIBE 
				}else{ 
					
					$ui = UserInfo::getByEmail( $submittedEmail );
					if( is_object($ui) ){  
						if($unsubscribeGID==-1){
							$ui->setAttribute('disable_emails',1); 
						}else{ 
							$g = Group::getById($unsubscribeGID);
							if( is_object($g) ) 
								TonyMailingList::removeRegisteredUserFromGroup( $ui, $g);
						}
					} 
					
					$nonUser = TonyMailingListNonUser::getByEmail($submittedEmail);  
					if( $nonUser ){
						if($unsubscribeGID==-1){
							$nonUser->blacklist=1;
							$nonUser->groupIds = array(); 
						}else{
							$nonUser->removeGroup($unsubscribeGID); 
						}
						$nonUser->update(); 
					}
				}
				
				if( !is_object($ui) && !is_object($nonUser)){
					$this->set('unsubscribeErrorMsg', t('Subscriber not found') );	
				}else{ 
					$this->set('unsubscribe_success',1); 
					$_REQUEST['agreed']=0;
				}
			}
			
		}else{
			$this->set('unsubscribeErrorMsg', $token->getErrorMessage() );	
		}
		
		$this->view();
	}
	
	
	public function non_user_details(){ 
		
		$nuID = intval($_REQUEST['nuID']); 
		Loader::model('user_attributes');  
		Loader::model('tony_mailing_list','tony_mailing_list'); 
		$nonUser = TonyMailingListNonUser::getById($nuID); 
		$txtHelper = Loader::helper('text');
		$tempUserInfo = new UserInfo();
		$tempUserInfo->uID=0;
		
		echo '<div>'.t('Email').': '.$nonUser->email.'</div>';
		
		echo '<div>'.t('Created').': '.date(t('F j, Y, g:i a T'),$nonUser->created).'</div>';
		
		
		if(count($nonUser->groupIds)){
			echo '<div>'.t('Groups').': '; 
			$groupNames=array();
			foreach($nonUser->groupIds as $gid){ 
				$g = Group::getByID($gid); 
				if(!is_object($g)) continue;
				$groupNames[]= $g->getGroupName();
			}
			echo join(', ',$groupNames);
			echo '</div>';
		}
		
				
		foreach($nonUser->getAttrData() as $attributeHandle => $attributeValueData){ 			
				
			$userAttr = UserAttributeKey::getByHandle( $attributeHandle );
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
			
			echo '<div>'.$userAttr->getAttributeKeyName().': '.$attributeValue.'</div>';
			
			//echo $userAttr->render('form', $attributeValue, true); 
		} 
		
		die;	
	}	
	
	
	
	// MAILING_LIST_ENABLE_BULK_DELETE 
	public function delete_subscribers(){  
	
		if( !defined('MAILING_LIST_ENABLE_BULK_DELETE') ) return false;  
		
		$ids = $_REQUEST['ids'] ? explode(',',$_REQUEST['ids']) : array();  
		
		$count=0; 
		
		foreach($ids as $val){   
			if( strstr($val,'mluID_') ){ 
				$nonRegisteredID= intval(str_replace('mluID_','',$val)); 
				$nonUser = TonyMailingListNonUser::getById($nonRegisteredID); 
				if( $nonUser ){  
					/*
					$nonUser->blacklist=1; 
					$nonUser->groupIds = array();  
					$nonUser->update(); 
					*/ 
					$nonUser->delete(); 
					$count++; 
				} 
			}elseif( strstr($val,'uID_') ){ 
				$registeredID= intval(str_replace('uID_','',$val)); 
				
				$ui = UserInfo::getByID( $registeredID );
				if( is_object($ui) ){  
					$ui->setAttribute('disable_emails',1);  
					$count++; 
				} 
			} 
		} 
		
		$this->set('unsubscribed_count',$count); 
		
		$this->view(); 
	}
	
	
	
} 