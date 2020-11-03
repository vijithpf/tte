<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_FilePermissionAssignment extends PermissionAssignment
{

    protected $permissionObjectToCheck;

    protected $inheritedPermissions = array(
        'view_file'                 => 'view_file_set_file',
        'view_file_in_file_manager' => 'search_file_set',
        'edit_file_properties'      => 'edit_file_set_file_properties',
        'edit_file_contents'        => 'edit_file_set_file_contents',
        'copy_file'                 => 'copy_file_set_files',
        'edit_file_permissions'     => 'edit_file_set_permissions',
        'delete_file'               => 'delete_file_set_files',
    );


    public function getPermissionAccessObject()
    {
        $db = Loader::db();
        if ($this->permissionObjectToCheck instanceof File) {
            $r = $db->GetCol('SELECT paID FROM FilePermissionAssignments WHERE fID = ? AND pkID = ?', array(
                $this->permissionObject->getFileID(), $this->pk->getPermissionKeyID(),
            ));
        } elseif (is_array($this->permissionObjectToCheck)) { // sets
            $sets = array();
            foreach ($this->permissionObjectToCheck as $fs) {
                $sets[] = $fs->getFileSetID();
            }
            $inheritedPKID = $db->GetOne('SELECT pkID FROM PermissionKeys WHERE pkHandle = ?', array($this->inheritedPermissions[$this->pk->getPermissionKeyHandle()]));
            $r             = $db->GetCol('SELECT DISTINCT paID FROM FileSetPermissionAssignments WHERE fsID IN (' . implode(',', $sets) . ') AND pkID = ? ' . $filterString, array(
                $inheritedPKID,
            ));
        } elseif ($this->permissionObjectToCheck instanceof FileSet && isset($this->inheritedPermissions[$this->pk->getPermissionKeyHandle()])) {
            $inheritedPKID = $db->GetOne('SELECT pkID FROM PermissionKeys WHERE pkHandle = ?', array($this->inheritedPermissions[$this->pk->getPermissionKeyHandle()]));
            $r             = $db->GetCol('SELECT DISTINCT paID FROM FileSetPermissionAssignments WHERE fsID = ? AND pkID = ?', array(
                $this->permissionObjectToCheck->getFileSetID(), $inheritedPKID,
            ));
        } else {
            return false;
        }

        if (count($r) == 1) {
            $permID = $r[0];
        }
        if (count($r) > 1) {
            $permID = $r;
        }

        if (is_array($permID)) {
            foreach ($permID as $paID) {
                $pa = PermissionAccess::getByID($paID, $this->pk);
                if (is_object($pa)) {
                    $perms[] = $pa;
                }
            }

            return PermissionAccess::createByMerge($perms);
        } else {
            return PermissionAccess::getByID($permID, $this->pk);
        }
    }

    public function setPermissionObject(File $f)
    {
        $this->permissionObject = $f;

        if ($f->overrideFileSetPermissions()) {
            $this->permissionObjectToCheck = $f;
        } else {
            $sets     = $f->getFileSets();
            $permsets = array();
            foreach ($sets as $fs) {
                if ($fs->overrideGlobalPermissions()) {
                    $permsets[] = $fs;
                }
            }
            if (count($permsets) > 0) {
                $this->permissionObjectToCheck = $permsets;
            } else {
                $fs                            = FileSet::getGlobal();
                $this->permissionObjectToCheck = $fs;
            }
        }
    }

    public function clearPermissionAssignment()
    {
        $db = Loader::db();
        $db->Execute('UPDATE FilePermissionAssignments SET paID = 0 WHERE pkID = ? AND fID = ?', array($this->pk->getPermissionKeyID(), $this->permissionObject->getFileID()));
    }

    public function assignPermissionAccess(PermissionAccess $pa)
    {
        $db = Loader::db();
        $db->Replace('FilePermissionAssignments', array('fID' => $this->getPermissionObject()->getFileID(), 'paID' => $pa->getPermissionAccessID(), 'pkID' => $this->pk->getPermissionKeyID()), array('fID', 'pkID'), true);
        $pa->markAsInUse();
    }


    public function getPermissionKeyToolsURL($task = false)
    {
        return parent::getPermissionKeyToolsURL($task) . '&fID=' . $this->getPermissionObject()->getFileID();
    }
}
