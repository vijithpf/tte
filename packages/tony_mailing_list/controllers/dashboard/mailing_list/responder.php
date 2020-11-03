<?php 

defined('C5_EXECUTE') or die(_("Access Denied."));


Loader::model('tony_mailing_list','tony_mailing_list'); 


class DashboardMailingListResponderController extends Controller {
	
	var $helpers = array('form');  
	
	public function view(){ 
	
		$this->set('responderEnabled', intval(Config::get('TONY_MAILING_LIST_RESPONDER_ENABLED')) );
		
		$this->set('noResponseTemplate', intval(Config::get('TONY_MAILING_LIST_RESPONDER_NO_TEMPLATE')) );
		
		$senderName = trim($this->post('sender')) ? trim($this->post('sender')) : Config::get('TONY_MAILING_LIST_SENDER_NAME'); 
		$this->set('senderName', $senderName); 
		
		$subject = trim($this->post('subject')) ? trim($this->post('subject')) : Config::get('TONY_MAILING_LIST_AUTO_RESPONDER_SUBJECT'); 
		$this->set('subject', $subject ); 
		
		$responderBody = trim($this->post('responderBody')) ? trim($this->post('responderBody')) : Config::get('TONY_MAILING_LIST_AUTO_RESPONDER_BODY')  ; 
		$this->set('responderBody', $responderBody ); 
		
	}
	
	public function save(){
		
		$token = Loader::helper('validation/token');
		$stringsHelper = Loader::helper('validation/strings');  
		
		$responderBody = trim($this->post('responderBody')); 
		$subject = trim($this->post('subject')); 
		
		if(!$responderBody){
		
			$this->set('errorMsg', 'You must enter some email body text.' );	
		
		}else if(!$subject){
		
			$this->set('errorMsg', 'You must enter an email Subject' );	
		
		}else if ($token->validate("mailing_list_responder")) {  	
			
			Config::save('TONY_MAILING_LIST_RESPONDER_ENABLED', intval($this->post('responderEnabled')) );
			if( intval($this->post('responderEnabled')) ){ 
				Config::save('TONY_MAILING_LIST_AUTO_RESPONDER_BODY', $responderBody );
				
				Config::save('TONY_MAILING_LIST_AUTO_RESPONDER_SUBJECT', $subject );
				
				Config::save('TONY_MAILING_LIST_SENDER_NAME', trim($this->post('senderName')) );
				
				Config::save('TONY_MAILING_LIST_RESPONDER_NO_TEMPLATE', intval($this->post('noResponseTemplate')) );
			}
			
			$this->set('successFlag',1);
			
		}else{
			$this->set('errorMsg', $token->getErrorMessage() );	
		}
		
		
		$this->view();
	}
}

?>