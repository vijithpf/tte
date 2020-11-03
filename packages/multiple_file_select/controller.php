<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

class MultipleFileSelectPackage extends Package
{

    protected $pkgHandle = 'multiple_file_select';
    protected $appVersionRequired = '5.5';
    protected $pkgVersion = '1.3';

    public function getPackageDescription()
    {
        return t("Select multiple files attribute");
    }

    public function getPackageName()
    {
        return t("Multiple files select");
    }

    public function install()
    {
        $pkg = parent::install();
        Loader::model('collection_types');
        Loader::model('collection_attributes');

        $eaku = AttributeKeyCategory::getByHandle('collection');
        $eaku->setAllowAttributeSets(AttributeKeyCategory::ASET_ALLOW_SINGLE);

        $multi_file_select = AttributeType::getByHandle('multiple_file_select');
        if (!is_object($multi_file_select) || !intval($multi_file_select->getAttributeTypeID())) {
            $multi_file_select = AttributeType::add('multiple_file_select', t('Multiple files'), $pkg);
            $eaku->associateAttributeKeyType($multi_file_select);
        }
    }
}
