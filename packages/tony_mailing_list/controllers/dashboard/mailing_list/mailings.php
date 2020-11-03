<?php 

defined('C5_EXECUTE') or die(_("Access Denied."));


Loader::model('tony_mailing_list','tony_mailing_list'); 
Loader::model('tony_mailing_list_stats','tony_mailing_list'); 

class DashboardMailingListMailingsController extends Controller {
	
	public function view(){ 
	
		$pagination = Loader::helper('pagination');	 
		$pageNum=(intval($_REQUEST['page'])>0)?intval($_REQUEST['page']):1; 		
		$pageSize=(intval($_REQUEST['pageSize'])>0)?intval($_REQUEST['pageSize']):20;
		$pagination->queryStringPagingVariable='page';	
		
		$resultCount = TonyMailingListMailing::getList( false, 'count' );
		$pagination->init( $pageNum, $resultCount, View::url('/dashboard/mailing_list/mailings'), $pageSize );
		
		$mailings = TonyMailingListMailing::getList( false, 'rows', $pagination->getLIMIT() );
		
		$this->set('paginator', $pagination); 
		$this->set('mailings',$mailings);
	}
	
	public function detail( $id=0, $saved=0 ){ 
		
		$mailing = TonyMailingListMailing::getById($id);
		
		if( !is_object($mailing) )
			throw new Exception( t('A mailing with that id was not found') );
		
		$mailing->stillRunningCheck(); 
		
		$calculatedStats = TonyMailingListStats::calculateStats($mailing);
		
		//$mailing->send();
		$this->set('mode','detail');
		$this->set('saved',intval($saved));
		$this->set('mailing',$mailing);
		$this->set('calculatedStats',$calculatedStats);
	}
	
	public function preview(){ 

		$mailing = TonyMailingListMailing::getById(intval($_REQUEST['id']));
		
		if( !is_object($mailing) )
			throw new Exception( t('A mailing with that id was not found') );
		
		$previewHTML = TonyMailingList::getHeaderHTML().$mailing->getBody().TonyMailingList::getFooterHTML(); 
		$userAttrReplacedText = TonyMailingListMailing::userAttributeTextReplacement( $previewHTML, $u );
		$absoluteLinksText=TonyMailingListMailing::relativeToAbsoluteLinks( $userAttrReplacedText );
		echo $absoluteLinksText;
		die; 
		
	}
	
	public function trigger_send_process( $id=0, $force=0 ){
		
		if( intval(str_replace(array('M','m'),'',ini_get('memory_limit')))<=256 && !ini_get('safe_mode')  )  
			ini_set('memory_limit', '256M'); 

		$this->set('startMailingId', intval($id) );
		
		if($_REQUEST['force'] || $_REQUEST['forceFailed']) $force=1; 
		
		$mailing = TonyMailingListMailing::getById($id);
		$mailing->triggerSendProcess( $force ); 
		
		$this->detail( $id );
		 
	}
	
	public function updateStatus( $id=0 ){
		
		$json = Loader::helper('json'); 
		$jsonData=array(); 
		
		$mailing = TonyMailingListMailing::getById($_POST['mlid']);
		//$result = $mailing->send();  
		$jsonData['msg']=$mailing->getStatusMsg() ;
		$jsonData['status']=$mailing->getStatus() ;
		$jsonData['statusText']=$mailing->getStatusText();
		$jsonData['sentCount']=$mailing->getSentCount();
		$jsonData['failedCount']=$mailing->getFailedCount();;
		
		echo $json->encode( $jsonData ); 
		die;
	}
	
	public function delete( $mlmid=0 ){ 
		$mailing = TonyMailingListMailing::getById( $mlmid );
		if( is_object($mailing) && $mailing->getId() ){ 
			//security check: must be sender or an administrator 
			$u = new User();
			$adminGroup = Group::getbyId(ADMIN_GROUP_ID); 
			
			$page = Page::getCurrentPage();
			$permissions = new Permissions($page); 
			
			if( $u->uID != intval($mailing->getSenderUID()) && !$u->inGroup($adminGroup) && !$u->isSuperUser() && !$permissions->canWrite() ) 
				throw new Exception( t("You don't have permission to edit this mailing. You must either be in the administrators group or be the sender.") );	
				
			$mailing->delete();
		}
		
		$this->redirect('/dashboard/mailing_list/mailings/');
	}
}

?>