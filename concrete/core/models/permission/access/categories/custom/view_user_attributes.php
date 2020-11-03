<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_ViewUserAttributesUserPermissionAccess extends UserPermissionAccess
{

    public function save($args)
    {
        parent::save();
        $db = Loader::db();
        $db->Execute('DELETE FROM UserPermissionViewAttributeAccessList WHERE paID = ?', array($this->getPermissionAccessID()));
        $db->Execute('DELETE FROM UserPermissionViewAttributeAccessListCustom WHERE paID = ?', array($this->getPermissionAccessID()));
        if (is_array($args['viewAttributesIncluded'])) {
            foreach ($args['viewAttributesIncluded'] as $peID => $permission) {
                $v = array($this->getPermissionAccessID(), $peID, $permission);
                $db->Execute('INSERT INTO UserPermissionViewAttributeAccessList (paID, peID, permission) VALUES (?, ?, ?)', $v);
            }
        }

        if (is_array($args['viewAttributesExcluded'])) {
            foreach ($args['viewAttributesExcluded'] as $peID => $permission) {
                $v = array($this->getPermissionAccessID(), $peID, $permission);
                $db->Execute('INSERT INTO UserPermissionViewAttributeAccessList (paID, peID, permission) VALUES (?, ?, ?)', $v);
            }
        }

        if (is_array($args['akIDInclude'])) {
            foreach ($args['akIDInclude'] as $peID => $akIDs) {
                foreach ($akIDs as $akID) {
                    $v = array($this->getPermissionAccessID(), $peID, $akID);
                    $db->Execute('INSERT INTO UserPermissionViewAttributeAccessListCustom (paID, peID, akID) VALUES (?, ?, ?)', $v);
                }
            }
        }

        if (is_array($args['akIDExclude'])) {
            foreach ($args['akIDExclude'] as $peID => $akIDs) {
                foreach ($akIDs as $akID) {
                    $v = array($this->getPermissionAccessID(), $peID, $akID);
                    $db->Execute('INSERT INTO UserPermissionViewAttributeAccessListCustom (paID, peID, akID) VALUES (?, ?, ?)', $v);
                }
            }
        }
    }

    public function duplicate($newPA = false)
    {
        $newPA = parent::duplicate($newPA);
        $db    = Loader::db();
        $r     = $db->Execute('SELECT * FROM UserPermissionViewAttributeAccessList WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['permission']);
            $db->Execute('INSERT INTO UserPermissionViewAttributeAccessList (peID, paID, permission) VALUES (?, ?, ?)', $v);
        }
        $r = $db->Execute('SELECT * FROM UserPermissionViewAttributeAccessListCustom WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['akID']);
            $db->Execute('INSERT INTO UserPermissionViewAttributeAccessListCustom  (peID, paID, akID) VALUES (?, ?, ?)', $v);
        }

        return $newPA;
    }

    public function getAccessListItems($accessType = PermissionKey::ACCESS_TYPE_INCLUDE, $filterEntities = array())
    {
        $db   = Loader::db();
        $list = parent::getAccessListItems($accessType, $filterEntities);
        foreach ($list as $l) {
            $pe = $l->getAccessEntityObject();
            if ($this->permissionObjectToCheck instanceof Page && $l->getAccessType() == PermissionKey::ACCESS_TYPE_INCLUDE) {
                $permission = 'A';
            } else {
                $permission = $db->GetOne('SELECT permission FROM UserPermissionViewAttributeAccessList WHERE paID = ? AND peID = ?', array($l->getPermissionAccessID(), $pe->getAccessEntityID()));
                if ($permission != 'N' && $permission != 'C') {
                    $permission = 'A';
                }
            }
            $l->setAttributesAllowedPermission($permission);
            if ($permission == 'C') {
                $akIDs = $db->GetCol('SELECT akID FROM UserPermissionViewAttributeAccessListCustom WHERE paID = ? AND peID = ?', array($l->getPermissionAccessID(), $pe->getAccessEntityID()));
                $l->setAttributesAllowedArray($akIDs);
            }
        }

        return $list;
    }
}
