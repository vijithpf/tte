<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_BasicWorkflowPermissionAssignment extends PermissionAssignment
{

    public function getPermissionAccessObject()
    {
        $db = Loader::db();
        $r  = $db->GetOne('SELECT paID FROM BasicWorkflowPermissionAssignments WHERE wfID = ? AND pkID = ?', array(
            $this->getPermissionObject()->getWorkflowID(), $this->pk->getPermissionKeyID(),
        ));

        return PermissionAccess::getByID($r, $this->pk);
    }

    public function clearPermissionAssignment()
    {
        $db = Loader::db();
        $db->Execute('UPDATE BasicWorkflowPermissionAssignments SET paID = 0 WHERE pkID = ? AND wfID = ?', array($this->pk->getPermissionKeyID(), $this->getPermissionObject()->getWorkflowID()));
    }

    public function assignPermissionAccess(PermissionAccess $pa)
    {
        $db = Loader::db();
        $db->Replace('BasicWorkflowPermissionAssignments', array('wfID' => $this->getPermissionObject()->getWorkflowID(), 'paID' => $pa->getPermissionAccessID(), 'pkID' => $this->pk->getPermissionKeyID()), array('wfID', 'pkID'), true);
        $pa->markAsInUse();
    }

    public function getPermissionKeyToolsURL($task = false)
    {
        return parent::getPermissionKeyToolsURL($task) . '&wfID=' . $this->getPermissionObject()->getWorkflowID();
    }
}
