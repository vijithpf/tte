<?php 

defined('C5_EXECUTE') or die(_("Access Denied."));


Loader::model('tony_mailing_list','tony_mailing_list'); 

class DashboardMailingListSettingsController extends Controller {
	
	var $helpers = array('form');  
	
	public function view(){ 
	
		$this->set('sendOnCreate', Config::get('TONY_MAILING_LIST_SEND_ON_CREATE') ); 
		
		$this->set('throttle', Config::get('TONY_MAILING_LIST_THROTTLE') ); 
		$this->set('maxTime', Config::get('TONY_MAILING_LIST_MAX_TIME') ); 
		$this->set('emailsPerSet', Config::get('TONY_MAILING_LIST_EMAILS_PER_SET') ); 
		$this->set('pauseTime', Config::get('TONY_MAILING_LIST_PAUSE_TIME') ); 
		
		$this->set('allowAllUsersMailing', intval(Config::get('TONY_MAILING_LIST_ALL_USERS_MAILING')) );  
		
		$lockDownGIDs = explode(',',Config::get('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS')); 
		$this->set('lockDownGIDs', $lockDownGIDs );
		
		$enableMailingsGIDs = explode(',',Config::get('TONY_MAILING_LIST_ENABLE_MAIL_GIDS')); 
		$this->set('enableMailingsGIDs', $enableMailingsGIDs );		
		
		$this->set('customTemplate', intval(Config::get('TONY_MAILING_LIST_CUSTOM_TEMPLATE')) ); 
		
		$this->set('emailLogging', intval(Config::get('TONY_MAILING_LIST_EMAIL_LOGGING')) ); 
		
		$this->set('autoRestartTime', intval(Config::get('TONY_MAILING_LIST_AUTO_RESTART_TIME')) ); 
		
		$this->set('bounceBackEmail', Config::get('TONY_MAILING_LIST_BOUNCE_BACK_EMAIL')) ;
		
		$this->set( 'blacklistUnsubscribeOn', Config::get('TONY_MAILING_LIST_BLACKLIST_UNSUBSCRIBE') ); 
	}
	
	public function save(){
		
		$token = Loader::helper('validation/token');
		$stringsHelper = Loader::helper('validation/strings'); 
		
		$throttle=intval($this->post('throttle'));
		$maxTime = round(floatval($this->post('maxTime'))*60);
		$pauseTime = intval($this->post('pauseTime'));
		$emailLogging = intval($this->post('emailLogging'));
		
		//disabling this conditional so that it can be intentionally killed after a certain amount of time. 
		if( $throttle && $maxTime < $pauseTime && 1==2 ){
			
			$this->set('errorMsg', t('The max execution time must be larger than the pause time.') );
			
		}elseif ( $this->post('bounceBackEmail') && !$stringsHelper->email($this->post('bounceBackEmail'))  ) {
			
			$this->set('errorMsg', t('Invalid bounce-back email address') );
			
		}elseif ($token->validate("mailing_list_settings")) {
			
			Config::save('TONY_MAILING_LIST_SEND_ON_CREATE', intval($this->post('sendOnCreate')) );
			Config::save('TONY_MAILING_LIST_THROTTLE', $throttle );
			if($throttle){
				Config::save('TONY_MAILING_LIST_MAX_TIME', $maxTime );  
				Config::save('TONY_MAILING_LIST_EMAILS_PER_SET', intval($this->post('emailsPerSet')) );   
				Config::save('TONY_MAILING_LIST_PAUSE_TIME', $pauseTime );  
			}
			
			Config::save('TONY_MAILING_LIST_ALL_USERS_MAILING', intval($this->post('allowAllUsersMailing')) );
			
			Config::save('TONY_MAILING_LIST_BLACKLIST_UNSUBSCRIBE', intval($this->post('blacklistUnsubscribeOn')) );
			
			Config::save('TONY_MAILING_LIST_EMAIL_LOGGING', $emailLogging ); 
			
			Config::save('TONY_MAILING_LIST_AUTO_RESTART_TIME', intval($this->post('autoRestartTime')) );
			
			Config::save('TONY_MAILING_LIST_BOUNCE_BACK_EMAIL', $this->post('bounceBackEmail') );
			
			$lockDownGIDs=$this->post('lockDownGIDs');
			if(!is_array($lockDownGIDs)) $lockDownGIDs=array(); 
			Config::save('TONY_MAILING_LIST_CAN_SUBSCRIBE_GIDS', join(',',$lockDownGIDs) ); 
			
			$enableMailingsGIDs=$this->post('enableMailingsGIDs');
			if(!is_array($enableMailingsGIDs)) $enableMailingsGIDs=array(); 
			Config::save('TONY_MAILING_LIST_ENABLE_MAIL_GIDS', join(',',$enableMailingsGIDs) ); 	
			
			Config::save('TONY_MAILING_LIST_CUSTOM_TEMPLATE', intval($this->post('customTemplate')) );
			if( intval($this->post('customTemplate')) ){
				Config::save('TONY_MAILING_LIST_CUSTOM_TEMPLATE_HEADER', $this->post('headerHTML') );
				Config::save('TONY_MAILING_LIST_CUSTOM_TEMPLATE_FOOTER', $this->post('footerHTML') );
			}
			$this->set('successFlag',1);
			
		}else{
			$this->set('errorMsg', $token->getErrorMessage() );	
		}
		
		$this->view();
	}
}

?>