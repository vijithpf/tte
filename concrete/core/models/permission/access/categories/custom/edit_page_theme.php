<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_EditPageThemePagePermissionAccess extends PagePermissionAccess
{

    public function duplicate($newPA = false)
    {
        $newPA = parent::duplicate($newPA);
        $db    = Loader::db();
        $r     = $db->Execute('SELECT * FROM PagePermissionThemeAccessList WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['permission']);
            $db->Execute('INSERT INTO PagePermissionThemeAccessList (peID, paID, permission) VALUES (?, ?, ?)', $v);
        }
        $r = $db->Execute('SELECT * FROM PagePermissionThemeAccessListCustom WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['ptID']);
            $db->Execute('INSERT INTO PagePermissionThemeAccessListCustom  (peID, paID, ptID) VALUES (?, ?, ?)', $v);
        }

        return $newPA;
    }

    public function save($args)
    {
        parent::save();
        $db = Loader::db();
        $db->Execute('DELETE FROM PagePermissionThemeAccessList WHERE paID = ?', array($this->getPermissionAccessID()));
        $db->Execute('DELETE FROM PagePermissionThemeAccessListCustom WHERE paID = ?', array($this->getPermissionAccessID()));
        if (is_array($args['themesIncluded'])) {
            foreach ($args['themesIncluded'] as $peID => $permission) {
                $v = array($this->getPermissionAccessID(), $peID, $permission);
                $db->Execute('INSERT INTO PagePermissionThemeAccessList (paID, peID, permission) VALUES (?, ?, ?)', $v);
            }
        }

        if (is_array($args['themesExcluded'])) {
            foreach ($args['themesExcluded'] as $peID => $permission) {
                $v = array($this->getPermissionAccessID(), $peID, $permission);
                $db->Execute('INSERT INTO PagePermissionThemeAccessList (paID, peID, permission) VALUES (?, ?, ?)', $v);
            }
        }

        if (is_array($args['ptIDInclude'])) {
            foreach ($args['ptIDInclude'] as $peID => $ptIDs) {
                foreach ($ptIDs as $ptID) {
                    $v = array($this->getPermissionAccessID(), $peID, $ptID);
                    $db->Execute('INSERT INTO PagePermissionThemeAccessListCustom (paID, peID, ptID) VALUES (?, ?, ?)', $v);
                }
            }
        }

        if (is_array($args['ptIDExclude'])) {
            foreach ($args['ptIDExclude'] as $peID => $ptIDs) {
                foreach ($ptIDs as $ptID) {
                    $v = array($this->getPermissionAccessID(), $peID, $ptID);
                    $db->Execute('INSERT INTO PagePermissionThemeAccessListCustom (paID, peID, ptID) VALUES (?, ?, ?)', $v);
                }
            }
        }
    }

    public function getAccessListItems($accessType = PagePermissionKey::ACCESS_TYPE_INCLUDE, $filterEntities = array())
    {
        $db   = Loader::db();
        $list = parent::getAccessListItems($accessType, $filterEntities);
        $list = PermissionDuration::filterByActive($list);
        foreach ($list as $l) {
            $pe   = $l->getAccessEntityObject();
            $prow = $db->GetRow('SELECT permission FROM PagePermissionThemeAccessList WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $l->getPermissionAccessID()));
            if (is_array($prow) && $prow['permission']) {
                $l->setThemesAllowedPermission($prow['permission']);
                $permission = $prow['permission'];
            } elseif ($l->getAccessType() == PagePermissionKey::ACCESS_TYPE_INCLUDE) {
                $l->setThemesAllowedPermission('A');
            } else {
                $l->setThemesAllowedPermission('N');
            }
            if ($permission == 'C') {
                $ptIDs = $db->GetCol('SELECT ptID FROM PagePermissionThemeAccessListCustom WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $l->getPermissionAccessID()));
                $l->setThemesAllowedArray($ptIDs);
            }
        }

        return $list;
    }
}
