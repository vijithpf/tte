<?php  defined('C5_EXECUTE') or die("Access Denied.");

class UspBlockController extends BlockController {
	
	protected $btName = 'Unique Selling Point';
	protected $btDescription = '';
	protected $btTable = 'btDCUsp';
	
	protected $btInterfaceWidth = "700";
	protected $btInterfaceHeight = "450";
	
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = true;
	protected $btCacheBlockOutputOnPost = true;
	protected $btCacheBlockOutputForRegisteredUsers = false;
	protected $btCacheBlockOutputLifetime = CACHE_LIFETIME;
	
	public function getSearchableContent() {
		$content = array();
		$content[] = $this->field_1_textbox_text;
		$content[] = $this->field_2_textarea_text;
		return implode(' - ', $content);
	}








}
