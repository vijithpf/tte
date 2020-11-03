<?php 

defined('C5_EXECUTE') or die(_("Access Denied."));


Loader::model('tony_mailing_list','tony_mailing_list'); 

class DashboardMailingListController extends Controller {
	
	public function view(){ 
		
		$this->redirect('/dashboard/mailing_list/send');
		
	}
	
}

?>