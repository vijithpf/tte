<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_AccessUserSearchUserPermissionAccess extends PermissionAccess
{

    public function duplicate($newPA = false)
    {
        $db    = Loader::db();
        $newPA = parent::duplicate($newPA);
        $r     = $db->Execute('SELECT * FROM ' . $this->dbTableAccessList . ' WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['permission']);
            $db->Execute('INSERT INTO ' . $this->dbTableAccessList . ' (peID, paID, permission) VALUES (?, ?, ?)', $v);
        }
        $r = $db->Execute('SELECT * FROM ' . $this->dbTableAccessListCustom . ' WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['gID']);
            $db->Execute('INSERT INTO ' . $this->dbTableAccessListCustom . ' (peID, paID, gID) VALUES (?, ?, ?)', $v);
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
                $permission = $db->GetOne('SELECT permission FROM ' . $this->dbTableAccessList . ' WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $l->getPermissionAccessID()));
                if ($permission != 'N' && $permission != 'C') {
                    $permission = 'A';
                }
            }
            $l->setGroupsAllowedPermission($permission);
            if ($permission == 'C') {
                $gIDs = $db->GetCol('SELECT gID FROM ' . $this->dbTableAccessListCustom . ' WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $l->getPermissionAccessID()));
                $l->setGroupsAllowedArray($gIDs);
            }
        }

        return $list;
    }

    protected $dbTableAccessList       = 'UserPermissionUserSearchAccessList';
    protected $dbTableAccessListCustom = 'UserPermissionUserSearchAccessListCustom';

    public function save($args)
    {
        parent::save();
        $db = Loader::db();
        $db->Execute('DELETE FROM ' . $this->dbTableAccessList . ' WHERE paID = ?', array($this->getPermissionAccessID()));
        $db->Execute('DELETE FROM ' . $this->dbTableAccessListCustom . ' WHERE paID = ?', array($this->getPermissionAccessID()));
        if (is_array($args['groupsIncluded'])) {
            foreach ($args['groupsIncluded'] as $peID => $permission) {
                $v = array($peID, $this->getPermissionAccessID(), $permission);
                $db->Execute('INSERT INTO ' . $this->dbTableAccessList . ' (peID, paID, permission) VALUES (?, ?, ?)', $v);
            }
        }

        if (is_array($args['groupsExcluded'])) {
            foreach ($args['groupsExcluded'] as $peID => $permission) {
                $v = array($peID, $this->getPermissionAccessID(), $permission);
                $db->Execute('INSERT INTO ' . $this->dbTableAccessList . ' (peID, paID, permission) VALUES (?, ?, ?)', $v);
            }
        }

        if (is_array($args['gIDInclude'])) {
            foreach ($args['gIDInclude'] as $peID => $gIDs) {
                foreach ($gIDs as $gID) {
                    $v = array($peID, $this->getPermissionAccessID(), $gID);
                    $db->Execute('INSERT INTO ' . $this->dbTableAccessListCustom . ' (peID, paID, gID) VALUES (?, ?, ?)', $v);
                }
            }
        }

        if (is_array($args['gIDExclude'])) {
            foreach ($args['gIDExclude'] as $peID => $gIDs) {
                foreach ($gIDs as $gID) {
                    $v = array($peID, $this->getPermissionAccessID(), $gID);
                    $db->Execute('INSERT INTO ' . $this->dbTableAccessListCustom . ' (peID, paID, gID) VALUES (?, ?, ?)', $v);
                }
            }
        }
    }
}
