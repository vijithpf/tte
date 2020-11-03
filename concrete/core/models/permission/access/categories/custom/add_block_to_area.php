<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_AddBlockToAreaAreaPermissionAccess extends AreaPermissionAccess
{

    public function duplicate($newPA = false)
    {
        $newPA = parent::duplicate($newPA);
        $db    = Loader::db();
        $r     = $db->Execute('SELECT * FROM AreaPermissionBlockTypeAccessList WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['permission']);
            $db->Execute('INSERT INTO AreaPermissionBlockTypeAccessList (peID, paID, permission) VALUES (?, ?, ?)', $v);
        }
        $r = $db->Execute('SELECT * FROM AreaPermissionBlockTypeAccessListCustom WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['btID']);
            $db->Execute('INSERT INTO AreaPermissionBlockTypeAccessListCustom  (peID, paID, btID) VALUES (?, ?, ?)', $v);
        }

        return $newPA;
    }

    public function getAccessListItems($accessType = AreaPermissionKey::ACCESS_TYPE_INCLUDE, $filterEntities = array())
    {
        $db   = Loader::db();
        $list = parent::getAccessListItems($accessType, $filterEntities);
        $pobj = $this->getPermissionObjectToCheck();
        foreach ($list as $l) {
            $pe = $l->getAccessEntityObject();
            if ($pobj instanceof Page) {
                $permission = $db->GetOne('SELECT permission FROM BlockTypePermissionBlockTypeAccessList WHERE paID = ?', array($l->getPermissionAccessID()));
            } else {
                $permission = $db->GetOne('SELECT permission FROM AreaPermissionBlockTypeAccessList WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $l->getPermissionAccessID()));
            }
            if ($permission != 'N' && $permission != 'C') {
                $permission = 'A';
            }
            $l->setBlockTypesAllowedPermission($permission);
            if ($permission == 'C') {
                if ($pobj instanceof Area) {
                    $btIDs = $db->GetCol('SELECT btID FROM AreaPermissionBlockTypeAccessListCustom WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $l->getPermissionAccessID()));
                } else {
                    $btIDs = $db->GetCol('SELECT btID FROM BlockTypePermissionBlockTypeAccessListCustom WHERE paID = ?', array($l->getPermissionAccessID()));
                }
                $l->setBlockTypesAllowedArray($btIDs);
            }
        }

        return $list;
    }

    public function save($args)
    {
        $db = Loader::db();
        parent::save();
        $db->Execute('DELETE FROM AreaPermissionBlockTypeAccessList WHERE paID = ?', array($this->getPermissionAccessID()));
        $db->Execute('DELETE FROM AreaPermissionBlockTypeAccessListCustom WHERE paID = ?', array($this->getPermissionAccessID()));
        if (is_array($args['blockTypesIncluded'])) {
            foreach ($args['blockTypesIncluded'] as $peID => $permission) {
                $v = array($this->getPermissionAccessID(), $peID, $permission);
                $db->Execute('INSERT INTO AreaPermissionBlockTypeAccessList (paID, peID, permission) VALUES (?, ?, ?)', $v);
            }
        }

        if (is_array($args['blockTypesExcluded'])) {
            foreach ($args['blockTypesExcluded'] as $peID => $permission) {
                $v = array($this->getPermissionAccessID(), $peID, $permission);
                $db->Execute('INSERT INTO AreaPermissionBlockTypeAccessList (paID, peID, permission) VALUES (?, ?, ?)', $v);
            }
        }

        if (is_array($args['btIDInclude'])) {
            foreach ($args['btIDInclude'] as $peID => $btIDs) {
                foreach ($btIDs as $btID) {
                    $v = array($this->getPermissionAccessID(), $peID, $btID);
                    $db->Execute('INSERT INTO AreaPermissionBlockTypeAccessListCustom (paID, peID, btID) VALUES (?, ?, ?)', $v);
                }
            }
        }

        if (is_array($args['btIDExclude'])) {
            foreach ($args['btIDExclude'] as $peID => $btIDs) {
                foreach ($btIDs as $btID) {
                    $v = array($this->getPermissionAccessID(), $peID, $btID);
                    $db->Execute('INSERT INTO AreaPermissionBlockTypeAccessListCustom (paID, peID, btID) VALUES (?, ?, ?)', $v);
                }
            }
        }
    }
}
