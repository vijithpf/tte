<?php 

defined('C5_EXECUTE') or die(_("Access Denied."));  

/* 

Note: If you want to include the header and footer on plain text mailings, add this to your config file  
define('MAILING_LIST_HEADER_FOOTER_ON_PLAINTEXT',1); 

you can increase the font-size for the unsubscribe link like this: 
define('MAILING_LIST_UNSUBSCRIBE_FONT_SIZE','10px'); 

if the mailing list subscriptions block is being cached 
define('MAILING_LIST_SUBSCRIBE_TOKEN_DISABLED',1); 

to override the date format that's inserted into mailings when using the %date_stamp% tag 
define('MAILING_LIST_DATE_FORMAT','j/n/Y'); 

to specify the default 'from' email address 
define('MAILING_LIST_FROM_DEFAULT','user@domain.com');  

default from email address on auto response from subscriptions 
define('MAILING_LIST_SUBSCRIPTIONS_EMAIL','user@domain.com');  

to show the email field on the subscription box even for logged in users 
define('MAILING_LIST_ALWAYS_SHOW_EMAIL_FIELD',1); 

if you want that email field to be blank for logged in users, set this option
define('MAILING_LIST_SHOW_EMPTY_EMAIL_FIELD',1);  

to enable bulk delete tool on mailing list subscriptions page: */  
define('MAILING_LIST_ENABLE_BULK_DELETE',1);  



class TonyMailingListPackage extends Package {  

	protected $pkgHandle = 'tony_mailing_list';
	protected $appVersionRequired = '5.3.3.1';
	protected $pkgVersion = '2.54';   
	
	public function getPackageDescription() { 
		return t("Send mail to people in any group, and manage subscriptions");  
	}
	
	public function getPackageName() {
		return t("Mailing List"); 
	}
	
	public function upgrade(){
		$result = parent::upgrade();
		$this->configure(); 
		return $result;
	}
	
	public function install() {
		$pkg = parent::install();
		$this->configure();
	}
	
	public function configure() {   
	
		$pkg = Package::getByHandle('tony_mailing_list'); 
		
		Loader::model('user_attributes');
		Loader::model('collection_attributes');
		
		//install mailing list subscription management page  
		Loader::model('single_page');
		$mailingListPage=Page::getByPath('/manage_subscriptions');
		if( !is_object($mailingListPage) || !intval($mailingListPage->getCollectionID()) ){ 
			$mailingListPage=SinglePage::add('/manage_subscriptions', $pkg);
		}
		if( is_object($mailingListPage) && intval($mailingListPage->getCollectionID())  ){
			$mailingListPage->update(array('cName'=>t('Manage Subscriptions'), 'cDescription'=>t("Unsubscribe from mailing list groups")));
			$excludeNavAttr=CollectionAttributeKey::getByHandle('exclude_nav'); 
			if( is_object($excludeNavAttr) ) $mailingListPage->setAttribute('exclude_nav',1);
			$excludePageListAttr=CollectionAttributeKey::getByHandle('exclude_page_list');
			if( is_object($excludePageListAttr) ) $mailingListPage->setAttribute('exclude_page_list',1);
		}else throw new Exception( t('Error: /manage_subscriptions page not created') );
		

		//install dashboard mailing list pages
		$mailingListPage=Page::getByPath('/dashboard/mailing_list');
		if( !is_object($mailingListPage) || !intval($mailingListPage->getCollectionID()) ){ 
			$mailingListPage=SinglePage::add('/dashboard/mailing_list', $pkg); 
		}
		if( is_object($mailingListPage) && intval($mailingListPage->getCollectionID())  ){
			$mailingListPage->update(array('cName'=>t('Mailing List'), 'cDescription'=>t("Send emails to groups & manage mailings")));
		}else throw new Exception( t('Error: /dashboard/mailing_list page not created') );
	
	
		$mailingListPage=Page::getByPath('/dashboard/mailing_list/send');
		if( !is_object($mailingListPage) || !intval($mailingListPage->getCollectionID()) ){ 
			$mailingListPage=SinglePage::add('/dashboard/mailing_list/send', $pkg);
		}
		if( is_object($mailingListPage) && intval($mailingListPage->getCollectionID())  ){
			$mailingListPage->update(array('cName'=>t('Create'), 'cDescription'=>t("Create & edit mailings")));
		}else throw new Exception( t('Error: /dashboard/mailing_list/send page not created') );
		
		
		$mailingListPage=Page::getByPath('/dashboard/mailing_list/mailings');
		if( !is_object($mailingListPage) || !intval($mailingListPage->getCollectionID()) ){ 
			$mailingListPage=SinglePage::add('/dashboard/mailing_list/mailings', $pkg);
		}
		if( is_object($mailingListPage) && intval($mailingListPage->getCollectionID())  ){
			$mailingListPage->update(array('cName'=>t('Mailings'), 'cDescription'=>t("View sent emails")));		
		}else throw new Exception( t('Error: /dashboard/mailing_list/mailings page not created') );
		
		
		$mailingListPage=Page::getByPath('/dashboard/mailing_list/settings');
		if( !is_object($mailingListPage) || !intval($mailingListPage->getCollectionID()) ){ 
			$mailingListPage=SinglePage::add('/dashboard/mailing_list/settings', $pkg);
		}
		if( is_object($mailingListPage) && intval($mailingListPage->getCollectionID())  ){
			$mailingListPage->update(array('cName'=>t('Settings'), 'cDescription'=>t("Configure the mailings list and subscriptions")));	
		}else throw new Exception( t('Error: /dashboard/mailing_list/settings page not created') );
		
		
		$mailingListPage=Page::getByPath('/dashboard/mailing_list/subscribers');
		if( !is_object($mailingListPage) || !intval($mailingListPage->getCollectionID()) ){ 
			$mailingListPage=SinglePage::add('/dashboard/mailing_list/subscribers', $pkg);
		}
		if( is_object($mailingListPage) && intval($mailingListPage->getCollectionID())  ){
			$mailingListPage->update(array('cName'=>t('Manage Subscribers'), 'cDescription'=>t("Manager users")));	
		}else throw new Exception( t('Error: /dashboard/mailing_list/subscribers page not created') );
		

		$mailingListPage=Page::getByPath('/dashboard/mailing_list/responder');
		if( !is_object($mailingListPage) || !intval($mailingListPage->getCollectionID()) ){ 
			$mailingListPage=SinglePage::add('/dashboard/mailing_list/responder', $pkg);
		}
		if( is_object($mailingListPage) && intval($mailingListPage->getCollectionID())  ){
			$mailingListPage->update(array('cName'=>t('Subcriptions Auto-Response'), 'cDescription'=>t("Send new users a welcome email")));	
		}else throw new Exception( t('Error: /dashboard/mailing_list/responder page not created') );
		
		
		// install block
		$mailingListBlockType = BlockType::getByHandle('tony_mailing_list', $pkg); 
		if(!is_object($mailingListBlockType)) 		
			BlockType::installBlockTypeFromPackage('tony_mailing_list', $pkg); 
		
		//add sample groups
		$mailingListGroupName = t("Mailing List");
		$mailingListGroup = Group::getByName($mailingListGroupName);
		if(!is_object($mailingListGroup))
			$mailingListGroup = Group::add( $mailingListGroupName, t("Mailing List Group"));
			
		$newsletterGroupName = t("Newsletter");
		$newsletterGroup = Group::getByName($newsletterGroupName);
		if(!is_object($newsletterGroup))
			$newsletterGroup = Group::add( $newsletterGroupName, t("Mailing List Group"));
			
		$specialsGroupName = t("Specials & Promotions");
		$specialsGroup = Group::getByName($specialsGroupName);
		if(!is_object($specialsGroup))
			$specialsGroup = Group::add( $specialsGroupName, t("Mailing List Group"));
			
		$updatesGroupName = t("Updates");
		$updatesGroup = Group::getByName($updatesGroupName);
		if(!is_object($updatesGroup))
			$updatesGroup = Group::add( $updatesGroupName, t("Mailing List Group"));			
			
			
			
		//add disable_emails user attribute 
		$boolt = AttributeType::getByHandle('boolean');
		$mailingListAttr=UserAttributeKey::getByHandle('disable_emails');
		if( !is_object($mailingListAttr) )
			UserAttributeKey::add($boolt, array('akHandle' => 'disable_emails', 'akName' => t('Never send mailing list emails'), 'akIsSearchable' => false, 'uakProfileEdit' => false, 'uakRegisterEdit' => false, 'akCheckedByDefault' => false));
			
		//add mailing_list_optout_gIDs user attribute 
		$textAreaAT = AttributeType::getByHandle('textarea');
		$optOutAttr=UserAttributeKey::getByHandle('mailing_list_optout_gIDs');
		if( !is_object($optOutAttr) )
			$optOutAttr=UserAttributeKey::add($textAreaAT, array('akHandle' => 'mailing_list_optout_gIDs', 'akName' => t('Opt-Out of Group Mailings'), 'akIsSearchable' => false, 'uakProfileEdit' => false, 'uakRegisterEdit' => false) );
		
		//unsubscribe date attribute
		$dateAT = AttributeType::getByHandle('date');
		$lastUnsubscribeDateAttr=UserAttributeKey::getByHandle('last_unsubscribe_date');
		if( !is_object($lastUnsubscribeDateAttr) )
			UserAttributeKey::add($dateAT, array('akHandle' => 'last_unsubscribe_date', 'akName' => t('Last Mailing List Unsubscription Date'), 'akIsSearchable' => false, 'uakProfileEdit' => false, 'uakRegisterEdit' => false, 'akCheckedByDefault' => false));
					
		
		//unsubscribe info text area attribute
		$textareaAT = AttributeType::getByHandle('textarea');
		$unsubscribeDataAttr=UserAttributeKey::getByHandle('unsubscribe_data');
		if( !is_object($unsubscribeDataAttr) )
			UserAttributeKey::add($textareaAT, array('akHandle' => 'unsubscribe_data', 'akName' => t('Mailing List Unsubscribe Info'), 'akIsSearchable' => false, 'uakProfileEdit' => false, 'uakRegisterEdit' => false, 'akCheckedByDefault' => false));
				
				
			
		//CONFIG VARS	
			
		//generate a unique token salt for unsubscription
		if(!intval(Config::get('MAILING_LIST_TOKEN_SALT'))) 
			Config::save('MAILING_LIST_TOKEN_SALT', rand(10000000,1000000000) );
		
		//mailing list settings page defauls, if not already set
		if( !strlen(Config::get('TONY_MAILING_LIST_SEND_ON_CREATE')) ) 
			Config::save('TONY_MAILING_LIST_SEND_ON_CREATE', 1); 		
		
		if( !strlen(Config::get('TONY_MAILING_LIST_THROTTLE')) ) 
			Config::save('TONY_MAILING_LIST_THROTTLE', 1);
			
		if( !intval(Config::get('TONY_MAILING_LIST_MAX_TIME')) && !strlen(Config::get('TONY_MAILING_LIST_MAX_TIME')) ){
			$defaultTime = 10*60; 
			$maxExecutionTime = ( $defaultTime >ini_get('max_execution_time') ) ? $defaultTime : ini_get('max_execution_time');
				Config::save('TONY_MAILING_LIST_MAX_TIME', $maxExecutionTime );  
		} 
		
		if( !intval(Config::get('TONY_MAILING_LIST_PAUSE_TIME')) && !strlen(Config::get('TONY_MAILING_LIST_PAUSE_TIME')) ) 
			Config::save('TONY_MAILING_LIST_PAUSE_TIME', 20); 
			
		if( !intval(Config::get('TONY_MAILING_LIST_EMAILS_PER_SET')) ) 
			Config::save('TONY_MAILING_LIST_EMAILS_PER_SET', 1000); 	
			
		if( !strlen(Config::get('TONY_MAILING_LIST_BLACKLIST_UNSUBSCRIBE')) ) 
			Config::save('TONY_MAILING_LIST_BLACKLIST_UNSUBSCRIBE', 1);	 
			
		if( !is_int(Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS')) && !strstr(Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS'),',') ) 
			Config::save('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS', $mailingListGroup->getGroupID().','.$newsletterGroup->getGroupID().','.$specialsGroup->getGroupID().','.$updatesGroup->getGroupID() );			
		
		if( !is_int(Config::get('TONY_MAILING_LIST_ENABLE_MAIL_GIDS')) && !strstr(Config::get('TONY_MAILING_LIST_ENABLE_MAIL_GIDS'),',') ) 
			Config::save('TONY_MAILING_LIST_ENABLE_MAIL_GIDS', $mailingListGroup->getGroupID().','.$newsletterGroup->getGroupID().','.$specialsGroup->getGroupID().','.$updatesGroup->getGroupID() );			
			
		if( !strlen(Config::get('TONY_MAILING_LIST_AUTO_RESPONDER_BODY')) ){  
			$siteName = defined('SITE') ? SITE : str_replace('http://','',BASE_URL);  
			Config::save('TONY_MAILING_LIST_AUTO_RESPONDER_BODY',  t('Thanks for subscribing to ').$siteName ); 
		}
			
		if( !strlen(Config::get('TONY_MAILING_LIST_AUTO_RESPONDER_SUBJECT')) ) 
			Config::save('TONY_MAILING_LIST_AUTO_RESPONDER_SUBJECT', 'Welcome'); 
			
	}

}