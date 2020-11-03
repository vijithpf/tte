<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_GroupCombinationPermissionAccessEntity extends PermissionAccessEntity
{


    protected $groups = array();

    public function getGroups()
    {
        return $this->groups;
    }

    public function getAccessEntityTypeLinkHTML()
    {
        $html = '<a href="' . REL_DIR_FILES_TOOLS_REQUIRED . '/permissions/dialogs/access/entity/types/group_combination" dialog-width="400" dialog-height="300" class="dialog-launch" dialog-modal="false" dialog-title="' . t('Add Group Combination') . '">' . tc('PermissionAccessEntityTypeName', 'Group Combination') . '</a>';

        return $html;
    }

    public static function getAccessEntitiesForUser($user)
    {
        // finally, the most brutal one. we find any combos that this group would specifically be in.
        // first, we look for any combos that contain any of the groups this user is in. That way if there aren't any we can just skip it.
        $db     = Loader::db();
        $ingids = array();
        $db     = Loader::db();
        foreach ($user->getUserGroups() as $key => $val) {
            $ingids[] = $key;
        }
        $instr    = implode(',', $ingids);
        $entities = array();
        if ($user->isRegistered()) {
            $peIDs = $db->GetCol('SELECT DISTINCT pae.peID FROM PermissionAccessEntities pae INNER JOIN PermissionAccessEntityTypes paet ON pae.petID = paet.petID INNER JOIN PermissionAccessEntityGroups paeg ON pae.peID = paeg.peID WHERE petHandle = \'group_combination\' AND paeg.gID IN (' . $instr . ')');
            // now for each one we check to see if it applies
            foreach ($peIDs as $peID) {
                $r = $db->GetRow('SELECT count(gID) AS peGroups, (SELECT count(UserGroups.gID) FROM UserGroups WHERE uID = ? AND gID IN (SELECT gID FROM PermissionAccessEntityGroups WHERE peID = ?)) AS uGroups FROM PermissionAccessEntityGroups WHERE peID = ?', array(
                    $user->getUserID(), $peID, $peID,));
                if ($r['peGroups'] == $r['uGroups'] && $r['peGroups'] > 1) {
                    $entity = PermissionAccessEntity::getByID($peID);
                    if (is_object($entity)) {
                        $entities[] = $entity;
                    }
                }
            }
        }

        return $entities;
    }

    public static function getOrCreate($groups)
    {
        $db    = Loader::db();
        $petID = $db->GetOne('SELECT petID FROM PermissionAccessEntityTypes WHERE petHandle = \'group_combination\'');
        $q     = 'SELECT pae.peID FROM PermissionAccessEntities pae ';
        $i     = 1;
        foreach ($groups as $g) {
            $q .= 'left join PermissionAccessEntityGroups paeg' . $i . ' on pae.peID = paeg' . $i . '.peID ';
            $i++;
        }
        $q .= 'where petID = ? ';
        $i = 1;
        foreach ($groups as $g) {
            $q .= 'and paeg' . $i . '.gID = ' . $g->getGroupID() . ' ';
            $i++;
        }
        $peID = $db->GetOne($q, array($petID));
        if (!$peID) {
            $db->Execute('INSERT INTO PermissionAccessEntities (petID) VALUES (?)', array($petID));
            Config::save('ACCESS_ENTITY_UPDATED', time());
            $peID = $db->Insert_ID();
            foreach ($groups as $g) {
                $db->Execute('INSERT INTO PermissionAccessEntityGroups (peID, gID) VALUES (?, ?)', array($peID, $g->getGroupID()));
            }
        }

        return PermissionAccessEntity::getByID($peID);
    }

    public function getAccessEntityUsers(PermissionAccess $pa)
    {
        $gl = new UserList();
        foreach ($this->groups as $g) {
            $gl->filterByGroupID($g->getGroupID());
        }

        return $gl->get();
    }

    public function load()
    {
        $db   = Loader::db();
        $gIDs = $db->GetCol('SELECT gID FROM PermissionAccessEntityGroups WHERE peID = ? ORDER BY gID ASC', array($this->peID));
        if ($gIDs && is_array($gIDs)) {
            for ($i = 0; $i < count($gIDs); $i++) {
                $g = Group::getByID($gIDs[$i]);
                if (is_object($g)) {
                    $this->groups[] = $g;
                    $this->label .= $g->getGroupDisplayName();
                    if ($i + 1 < count($gIDs)) {
                        $this->label .= t(' + ');
                    }
                }
            }
        }
    }
}
