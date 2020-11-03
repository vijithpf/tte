<?php
defined('C5_EXECUTE') or die("Access Denied.");

class ChoosePageBlockController extends BlockController
{

    protected $btTable = 'btChoosePage';
    protected $btInterfaceWidth = "300";
    protected $btInterfaceHeight = "300";
    protected $btCacheBlockRecord = true;
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = true;
    protected $btCacheBlockOutputLifetime = 0;

    public function getBlockTypeDescription()
    {
        return t("Choose any page from the full sitemap.");
    }

    public function getBlockTypeName()
    {
        return t("Choose Page");
    }

    protected function loadBlockInformation()
    {
        $this->set('chosenPageID', intval($this->chosenPageID));
    }

    function add()
    {
        $this->loadBlockInformation();
    }

    function edit()
    {
        $this->loadBlockInformation();
    }

    function view()
    {
        $this->loadBlockInformation();
    }
}
