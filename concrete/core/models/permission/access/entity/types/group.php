<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_GroupPermissionAccessEntity extends PermissionAccessEntity
{

    protected $group = false;

    public function getGroupObject()
    {
        return $this->group;
    }

    public function getAccessEntityUsers(PermissionAccess $pa)
    {
        return $this->group->getGroupMembers();
    }

    public function getAccessEntityTypeLinkHTML()
    {
        $html = '<a href="' . REL_DIR_FILES_TOOLS_REQUIRED . '/select_group?include_core_groups=1" class="dialog-launch" dialog-modal="false" dialog-title="' . t('Add Group') . '">' . tc('PermissionAccessEntityTypeName', 'Group') . '</a>';

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
        $peIDs = $db->GetCol('SELECT pae.peID FROM PermissionAccessEntities pae INNER JOIN PermissionAccessEntityTypes paet ON pae.petID = paet.petID INNER JOIN PermissionAccessEntityGroups paeg ON pae.peID = paeg.peID WHERE petHandle = \'group\' AND paeg.gID IN (' . $instr . ')');
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

    public static function getOrCreate(Group $g)
    {
        $db    = Loader::db();
        $petID = $db->GetOne('SELECT petID FROM PermissionAccessEntityTypes WHERE petHandle = \'group\'');
        $peID  = $db->GetOne('SELECT pae.peID FROM PermissionAccessEntities pae INNER JOIN PermissionAccessEntityGroups paeg ON pae.peID = paeg.peID WHERE petID = ? AND paeg.gID = ?',
                             array($petID, $g->getGroupID()));
        if (!$peID) {
            $db->Execute('INSERT INTO PermissionAccessEntities (petID) VALUES(?)', array($petID));
            $peID = $db->Insert_ID();
            Config::save('ACCESS_ENTITY_UPDATED', time());
            $db->Execute('INSERT INTO PermissionAccessEntityGroups (peID, gID) VALUES (?, ?)', array($peID, $g->getGroupID()));
        }

        return PermissionAccessEntity::getByID($peID);
    }

    public function load()
    {
        $db  = Loader::db();
        $gID = $db->GetOne('SELECT gID FROM PermissionAccessEntityGroups WHERE peID = ?', array($this->peID));
        if ($gID) {
            $g = Group::getByID($gID);
            if (is_object($g)) {
                $this->group = $g;
                $this->label = $g->getGroupDisplayName();
            } else {
                $this->label = t('(Deleted Group)');
            }
        }
    }
}
