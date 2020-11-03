<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_WorkflowType extends Object
{

    public function getWorkflowTypeID()
    {
        return $this->wftID;
    }

    public function getWorkflowTypeHandle()
    {
        return $this->wftHandle;
    }

    public function getWorkflowTypeName()
    {
        return $this->wftName;
    }

    public static function getByID($wftID)
    {
        $db  = Loader::db();
        $row = $db->GetRow('SELECT wftID, pkgID, wftHandle, wftName FROM WorkflowTypes WHERE wftID = ?', array($wftID));
        if ($row['wftHandle']) {
            $wt = new WorkflowType();
            $wt->setPropertiesFromArray($row);

            return $wt;
        }
    }

    public static function getList()
    {
        $db   = Loader::db();
        $list = array();
        $r    = $db->Execute('SELECT wftID FROM WorkflowTypes ORDER BY wftID ASC');

        while ($row = $r->FetchRow()) {
            $list[] = WorkflowType::getByID($row['wftID']);
        }

        $r->Close();

        return $list;
    }

    public static function exportList($xml)
    {
        $wtypes = WorkflowType::getList();
        $db     = Loader::db();
        $axml   = $xml->addChild('workflowtypes');
        foreach ($wtypes as $wt) {
            $wtype = $axml->addChild('workflowtype');
            $wtype->addAttribute('handle', $wt->getWorkflowTypeHandle());
            $wtype->addAttribute('name', $wt->getWorkflowTypeName());
            $wtype->addAttribute('package', $wt->getPackageHandle());
        }
    }

    public function delete()
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM WorkflowTypes WHERE wftID = ?', array($this->wftID));
    }

    public static function getListByPackage($pkg)
    {
        $db   = Loader::db();
        $list = array();
        $r    = $db->Execute('SELECT wftID FROM WorkflowTypes WHERE pkgID = ? ORDER BY wftID ASC', array($pkg->getPackageID()));
        while ($row = $r->FetchRow()) {
            $list[] = WorkflowType::getByID($row['wftID']);
        }
        $r->Close();

        return $list;
    }

    public function getPackageID()
    {
        return $this->pkgID;
    }

    public function getPackageHandle()
    {
        return PackageList::getHandle($this->pkgID);
    }

    public static function getByHandle($wftHandle)
    {
        $db    = Loader::db();
        $wftID = $db->GetOne('SELECT wftID FROM WorkflowTypes WHERE wftHandle = ?', array($wftHandle));
        if ($wftID > 0) {
            return self::getByID($wftID);
        }
    }

    public static function add($wftHandle, $wftName, $pkg = false)
    {
        $pkgID = 0;
        if (is_object($pkg)) {
            $pkgID = $pkg->getPackageID();
        }
        $db = Loader::db();
        $db->Execute('INSERT INTO WorkflowTypes (wftHandle, wftName, pkgID) VALUES (?, ?, ?)', array($wftHandle, $wftName, $pkgID));
        $id  = $db->Insert_ID();
        $est = WorkflowType::getByID($id);

        return $est;
    }
}
