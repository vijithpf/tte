<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_UserPermissionAccessEntity extends PermissionAccessEntity
{

    protected $user;

    public function getUserObject()
    {
        return $this->user;
    }

    public function getAccessEntityUsers(PermissionAccess $pa)
    {
        return array($this->getUserObject());
    }

    public function getAccessEntityTypeLinkHTML()
    {
        $html = '<a href="' . REL_DIR_FILES_TOOLS_REQUIRED . '/users/search_dialog?mode=choose_multiple" dialog-modal="false" dialog-width="90%" dialog-title="' . t('Add User') . '" class="dialog-launch" dialog-height="70%"">' . tc('PermissionAccessEntityTypeName', 'User') . '</a>';

        return $html;
    }

    public static function getAccessEntitiesForUser($user)
    {
        $entities = array();
        $db       = Loader::db();
        if ($user->isRegistered()) {
            // we find the peID for the current user, if one exists. This means that the user has special permissions set just for them.
            $peID = $db->GetOne('SELECT peID FROM PermissionAccessEntityUsers WHERE uID = ?', array($user->getUserID()));
            if ($peID > 0) {
                $entity = PermissionAccessEntity::getByID($peID);
                if (is_object($entity)) {
                    $entities[] = $entity;
                }
            }
        }

        return $entities;
    }

    public static function getOrCreate(UserInfo $ui)
    {
        $db    = Loader::db();
        $petID = $db->GetOne('SELECT petID FROM PermissionAccessEntityTypes WHERE petHandle = \'user\'');
        $peID  = $db->GetOne('SELECT pae.peID FROM PermissionAccessEntities pae INNER JOIN PermissionAccessEntityUsers paeg ON pae.peID = paeg.peID WHERE petID = ? AND paeg.uID = ?',
                             array($petID, $ui->getUserID()));
        if (!$peID) {
            $db->Execute('INSERT INTO PermissionAccessEntities (petID) VALUES(?)', array($petID));
            $peID = $db->Insert_ID();
            Config::save('ACCESS_ENTITY_UPDATED', time());
            $db->Execute('INSERT INTO PermissionAccessEntityUsers (peID, uID) VALUES (?, ?)', array($peID, $ui->getUserID()));
        }

        return PermissionAccessEntity::getByID($peID);
    }

    public function load()
    {
        $db  = Loader::db();
        $uID = $db->GetOne('SELECT uID FROM PermissionAccessEntityUsers WHERE peID = ?', array($this->peID));
        if ($uID) {
            $ui = UserInfo::getByID($uID);
            if (is_object($ui)) {
                $this->user  = $ui;
                $this->label = $ui->getUserName();
            } else {
                $this->label = t('(Deleted User)');
            }
        }
    }
}
