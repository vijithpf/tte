<?php     defined('C5_EXECUTE') or die(_("Access Denied."));

class DashboardFormidableController extends Controller {

	public function view() {
		$this->redirect('/dashboard/formidable/forms/');
	}
}