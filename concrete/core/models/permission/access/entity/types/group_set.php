<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_GroupSetPermissionAccessEntity extends PermissionAccessEntity
{


    protected $groupset;

    public function getGroupSet()
    {
        return $this->groupset;
    }

    public function getAccessEntityTypeLinkHTML()
    {
        $html = '<a href="' . REL_DIR_FILES_TOOLS_REQUIRED . '/permissions/dialogs/access/entity/types/group_set" dialog-width="400" dialog-height="300" class="dialog-launch" dialog-modal="false" dialog-title="' . t('Add Group Set') . '">' . tc('PermissionAccessEntityTypeName', 'Group Set') . '</a>';

        return $html;
    }

    public static function getAccessEntitiesForUser($user)
    {
        $entities = array();
        $ingids   = array();
        $db       = Loader::db();
        foreach ($user->getUserGroups() as $key => $val) {
            $ingids[] = $key;
        }
        $instr = implode(',', $ingids);
        $peIDs = $db->GetCol('SELECT peID FROM PermissionAccessEntityGroupSets paegs INNER JOIN GroupSetGroups gsg ON paegs.gsID = gsg.gsID WHERE gsg.gID IN (' . $instr . ')');
        if (is_array($peIDs)) {
            foreach ($peIDs as $peID) {
                $entity = PermissionAccessEntity::getByID($peID);
                if (is_object($entity)) {
                    $entities[] = $entity;
                }
            }
        }

        return $entities;
    }

    public function getAccessEntityUsers(PermissionAccess $pa)
    {
        if (!isset($this->groupset)) {
            $this->load();
        }
        $groups = $this->groupset->getGroups();
        $users  = array();
        $ingids = array();
        $db     = Loader::db();
        foreach ($groups as $group) {
            $ingids[] = $group->getGroupID();
        }
        $instr = implode(',', $ingids);
        $r     = $db->Execute('SELECT uID FROM UserGroups WHERE gID IN (' . $instr . ')');
        $users = array();
        while ($row = $r->FetchRow()) {
            $ui = UserInfo::getByID($row['uID']);
            if (is_object($ui)) {
                $users[] = $ui;
            }
        }

        return $users;
    }

    public static function getOrCreate(GroupSet $gs)
    {
        $db    = Loader::db();
        $petID = $db->GetOne('SELECT petID FROM PermissionAccessEntityTypes WHERE petHandle = \'group_set\'');
        $peID  = $db->GetOne('SELECT pae.peID FROM PermissionAccessEntities pae INNER JOIN PermissionAccessEntityGroupSets paeg ON pae.peID = paeg.peID WHERE petID = ? AND paeg.gsID = ?',
                             array($petID, $gs->getGroupSetID()));
        if (!$peID) {
            $db->Execute('INSERT INTO PermissionAccessEntities (petID) VALUES(?)', array($petID));
            $peID = $db->Insert_ID();
            Config::save('ACCESS_ENTITY_UPDATED', time());
            $db->Execute('INSERT INTO PermissionAccessEntityGroupSets (peID, gsID) VALUES (?, ?)', array($peID, $gs->getGroupSetID()));
        }

        return PermissionAccessEntity::getByID($peID);
    }

    public function load()
    {
        $db   = Loader::db();
        $gsID = $db->GetOne('SELECT gsID FROM PermissionAccessEntityGroupSets WHERE peID = ?', array($this->peID));
        if ($gsID) {
            $gs = GroupSet::getByID($gsID);
            if (is_object($gs)) {
                $this->groupset = $gs;
                $this->label    = $gs->getGroupSetDisplayName();
            }
        }
    }
}
