<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_AddSubpagePagePermissionAccess extends PagePermissionAccess
{

    public function duplicate($newPA = false)
    {
        $newPA = parent::duplicate($newPA);
        $db    = Loader::db();
        $r     = $db->Execute('SELECT * FROM PagePermissionPageTypeAccessList WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['permission'], $row['externalLink']);
            $db->Execute('INSERT INTO PagePermissionPageTypeAccessList (peID, paID, permission, externalLink) VALUES (?, ?, ?, ?)', $v);
        }
        $r = $db->Execute('SELECT * FROM PagePermissionPageTypeAccessListCustom WHERE paID = ?', array($this->getPermissionAccessID()));
        while ($row = $r->FetchRow()) {
            $v = array($row['peID'], $newPA->getPermissionAccessID(), $row['ctID']);
            $db->Execute('INSERT INTO PagePermissionPageTypeAccessListCustom  (peID, paID, ctID) VALUES (?, ?, ?)', $v);
        }

        return $newPA;
    }

    public function removeListItem(PermissionAccessEntity $pe)
    {
        parent::removeListItem($pe);
        $db = Loader::db();
        $db->Execute('DELETE FROM PagePermissionPageTypeAccessList WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $this->getPermissionAccessID()));
        $db->Execute('DELETE FROM PagePermissionPageTypeAccessListCustom WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $this->getPermissionAccessID()));
    }

    public function save($args)
    {
        parent::save();
        $db = Loader::db();
        $db->Execute('DELETE FROM PagePermissionPageTypeAccessList WHERE paID = ?', array($this->getPermissionAccessID()));
        $db->Execute('DELETE FROM PagePermissionPageTypeAccessListCustom WHERE paID = ?', array($this->getPermissionAccessID()));
        if (is_array($args['pageTypesIncluded'])) {
            foreach ($args['pageTypesIncluded'] as $peID => $permission) {
                $ext = 0;
                if (!empty($args['allowExternalLinksIncluded'][$peID])) {
                    $ext = $args['allowExternalLinksIncluded'][$peID];
                }
                $v = array($this->getPermissionAccessID(), $peID, $permission, $ext);
                $db->Execute('INSERT INTO PagePermissionPageTypeAccessList (paID, peID, permission, externalLink) VALUES (?, ?, ?, ?)', $v);
            }
        }

        if (is_array($args['pageTypesExcluded'])) {
            foreach ($args['pageTypesExcluded'] as $peID => $permission) {
                $ext = 0;
                if (!empty($args['allowExternalLinksExcluded'][$peID])) {
                    $ext = $args['allowExternalLinksExcluded'][$peID];
                }
                $v = array($this->getPermissionAccessID(), $peID, $permission, $ext);
                $db->Execute('INSERT INTO PagePermissionPageTypeAccessList (paID, peID, permission, externalLink) VALUES (?, ?, ?, ?)', $v);
            }
        }

        if (is_array($args['ctIDInclude'])) {
            foreach ($args['ctIDInclude'] as $peID => $ctIDs) {
                foreach ($ctIDs as $ctID) {
                    $v = array($this->getPermissionAccessID(), $peID, $ctID);
                    $db->Execute('INSERT INTO PagePermissionPageTypeAccessListCustom (paID, peID, ctID) VALUES (?, ?, ?)', $v);
                }
            }
        }

        if (is_array($args['ctIDExclude'])) {
            foreach ($args['ctIDExclude'] as $peID => $ctIDs) {
                foreach ($ctIDs as $ctID) {
                    $v = array($this->getPermissionAccessID(), $peID, $ctID);
                    $db->Execute('INSERT INTO PagePermissionPageTypeAccessListCustom (paID, peID, ctID) VALUES (?, ?, ?)', $v);
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
            $prow = $db->GetRow('SELECT permission, externalLink FROM PagePermissionPageTypeAccessList WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $l->getPermissionAccessID()));
            if (is_array($prow) && $prow['permission']) {
                $l->setPageTypesAllowedPermission($prow['permission']);
                $l->setAllowExternalLinks($prow['externalLink']);
                $permission = $prow['permission'];
            } elseif ($l->getAccessType() == PagePermissionKey::ACCESS_TYPE_INCLUDE) {
                $l->setPageTypesAllowedPermission('A');
                $l->setAllowExternalLinks(1);
            } else {
                $l->setPageTypesAllowedPermission('N');
                $l->setAllowExternalLinks(0);
            }
            if ($permission == 'C') {
                $ctIDs = $db->GetCol('SELECT ctID FROM PagePermissionPageTypeAccessListCustom WHERE peID = ? AND paID = ?', array($pe->getAccessEntityID(), $l->getPermissionAccessID()));
                $l->setPageTypesAllowedArray($ctIDs);
            }
        }

        return $list;
    }
}
