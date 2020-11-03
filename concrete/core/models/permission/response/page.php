<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_PagePermissionResponse extends PermissionResponse
{

    // legacy support
    public function canWrite()
    {
        return $this->validate('edit_page_contents');
    }

    public function canReadVersions()
    {
        return $this->validate('view_page_versions');
    }

    public function canRead()
    {
        return $this->validate('view_page');
    }

    public function canAddSubContent()
    {
        return $this->validate('add_subpage');
    }

    public function canAddSubpages()
    {
        return $this->validate('add_subpage');
    }

    public function canDeleteCollection()
    {
        return $this->canDeletePage();
    }

    public function canApproveCollection()
    {
        return $this->validate('approve_page_versions');
    }

    public function canAdminPage()
    {
        return $this->validate('edit_page_permissions');
    }

    public function canAdmin()
    {
        return $this->validate('edit_page_permissions');
    }

    public function canAddExternalLink()
    {
        $pk = $this->category->getPermissionKeyByHandle('add_subpage');
        $pk->setPermissionObject($this->object);

        return $pk->canAddExternalLink();
    }

    public function canAddSubCollection($ct)
    {
        $pk = $this->category->getPermissionKeyByHandle('add_subpage');
        $pk->setPermissionObject($this->object);

        return $pk->validate($ct);
    }

    public function canViewPageInSitemap()
    {
        if (PERMISSIONS_MODEL != 'simple') {
            $pk = $this->category->getPermissionKeyByHandle('view_page_in_sitemap');
            $pk->setPermissionObject($this->object);

            return $pk->validate();
        }

        return $this->canViewPage();
    }

    public function canEditPageProperties($obj = false)
    {
        if ($this->object->isExternalLink()) {
            return $this->canDeletePage();
        }

        $pk = $this->category->getPermissionKeyByHandle('edit_page_properties');
        $pk->setPermissionObject($this->object);

        return $pk->validate($obj);
    }

    public function canDeletePage()
    {
        if ($this->object->isExternalLink()) {
            // then whether the person can delete/write to this page ACTUALLY dependent on whether the PARENT collection
            // is writable
            $cParentCollection = Page::getByID($this->object->getCollectionParentID(), 'RECENT');
            $cp2               = new Permissions($cParentCollection);

            return $cp2->canAddExternalLink();
        }

        return $this->validate('delete_page');
    }

    // end legacy

    // convenience function
    public function canViewToolbar()
    {
        $u = new User();
        if (!$u->isRegistered()) {
            return false;
        }
        if ($u->isSuperUser()) {
            return true;
        }

        $dh = Loader::helper('concrete/dashboard');
        if ($dh->canRead()
            || $this->canViewPageVersions()
            || $this->canPreviewPageAsUser()
            || $this->canEditPageSpeedSettings()
            || $this->canEditPageProperties()
            || $this->canEditPageContents()
            || $this->canAddSubpage()
            || $this->canDeletePage()
            || $this->canApprovePageVersions()
            || $this->canEditPagePermissions()
            || $this->canMoveOrCopyPage()
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function testForErrors()
    {
        if ($this->object->isMasterCollection()) {
            $canEditMaster = TaskPermission::getByHandle('access_page_defaults')->can();
            if (!($canEditMaster && $_SESSION['mcEditID'] == $this->object->getCollectionID())) {
                return COLLECTION_FORBIDDEN;
            }
        } else {
            if ((!$this->canViewPage()) && (!$this->object->getCollectionPointerExternalLink() != '')) {
                return COLLECTION_FORBIDDEN;
            }
        }
    }

    public function getAllTimedAssignmentsForPage()
    {
        $db          = Loader::db();
        $assignments = array();
        $r           = $db->Execute('SELECT peID, pkID, pdID FROM PagePermissionAssignments ppa INNER JOIN PermissionAccessList pal ON ppa.paID = pal.paID WHERE pdID > 0 AND cID = ?', array($this->object->getCollectionID()));
        while ($row = $r->FetchRow()) {
            $pk  = PagePermissionKey::getByID($row['pkID']);
            $pae = PermissionAccessEntity::getByID($row['peID']);
            $pd  = PermissionDuration::getByID($row['pdID']);
            $ppc = new PageContentPermissionTimedAssignment();
            $ppc->setDurationObject($pd);
            $ppc->setAccessEntityObject($pae);
            $ppc->setPermissionKeyObject($pk);
            $assignments[] = $ppc;
        }
        $r = $db->Execute('SELECT arHandle FROM Areas WHERE cID = ? AND arOverrideCollectionPermissions = 1', array($this->object->getCollectionID()));
        while ($row = $r->FetchRow()) {
            $r2 = $db->Execute('SELECT peID, pdID, pkID FROM AreaPermissionAssignments apa INNER JOIN PermissionAccessList pal ON apa.paID = pal.paID WHERE pdID > 0 AND cID = ? AND arHandle = ?', array($this->object->getCollectionID(), $row['arHandle']));
            while ($row2 = $r2->FetchRow()) {
                $pk   = AreaPermissionKey::getByID($row2['pkID']);
                $pae  = PermissionAccessEntity::getByID($row2['peID']);
                $area = Area::get($this->getPermissionObject(), $row['arHandle']);
                $pk->setPermissionObject($area);
                $pd  = PermissionDuration::getByID($row2['pdID']);
                $ppc = new PageContentPermissionTimedAssignment();
                $ppc->setDurationObject($pd);
                $ppc->setAccessEntityObject($pae);
                $ppc->setPermissionKeyObject($pk);
                $assignments[] = $ppc;
            }
        }
        $r = $db->Execute('SELECT peID, cvb.cvID, cvb.bID, pdID, pkID FROM BlockPermissionAssignments bpa
		INNER JOIN PermissionAccessList pal ON bpa.paID = pal.paID INNER JOIN CollectionVersionBlocks cvb ON cvb.cID = bpa.cID AND cvb.cvID = bpa.cvID AND cvb.bID = bpa.bID
		WHERE pdID > 0 AND cvb.cID = ? AND cvb.cvID = ? AND cvb.cbOverrideAreaPermissions = 1', array($this->object->getCollectionID(), $this->object->getVersionID()));
        while ($row = $r->FetchRow()) {
            $pk       = BlockPermissionKey::getByID($row['pkID']);
            $pae      = PermissionAccessEntity::getByID($row['peID']);
            $arHandle = $db->GetOne('SELECT arHandle FROM CollectionVersionBlocks WHERE bID = ? AND cvID = ? AND cID = ?', array(
                $row['bID'], $row['cvID'], $this->object->getCollectionID(),
            ));
            $b        = Block::getByID($row['bID'], $this->object, $arHandle);
            $pk->setPermissionObject($b);
            $pd  = PermissionDuration::getByID($row['pdID']);
            $ppc = new PageContentPermissionTimedAssignment();
            $ppc->setDurationObject($pd);
            $ppc->setAccessEntityObject($pae);
            $ppc->setPermissionKeyObject($pk);
            $assignments[] = $ppc;
        }

        return $assignments;
    }
}
