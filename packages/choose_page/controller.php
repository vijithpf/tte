<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

class ChoosePagePackage extends Package
{

    protected $pkgHandle = 'choose_page';
    protected $appVersionRequired = '5.6.3.4';
    protected $pkgVersion = '1.0';

    public function getPackageDescription()
    {
        return t("A block to choose a page from the full sitemap");
    }

    public function getPackageName()
    {
        return t("Choose Page");
    }

    public function install()
    {
        $pkg = parent::install();

        BlockType::installBlockTypeFromPackage('choose_page', $pkg);
    }
}
