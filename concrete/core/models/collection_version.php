<?php
defined('C5_EXECUTE') or die('Access Denied.');
/**
 * Contains the collection version object.
 *
 * @author Andrew Embler <andrew@concrete5.org>
 *
 * @category Concrete
 *
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

/**
 * An object that maps to versions of collections. Each page in concrete is a _collection_ of blocks, each of which has different versions (for version control.).
 *
 * @author Andrew Embler <andrew@concrete5.org>
 *
 * @category Concrete
 *
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Model_CollectionVersion extends Object
{

    public    $cvIsApproved;
    public    $cID;
    protected $attributes   = array();
    public    $layoutStyles = array();

    public function getPermissionObjectIdentifier()
    {
        return $this->getCollectionID() . ':' . $this->getVersionID();
    }

    public function refreshCache()
    {
        CacheLocal::delete('page', $this->getCollectionID() . ':' . $this->getVersionID());
        CacheLocal::delete('page', $this->getCollectionID() . ':' . 'RECENT');
        CacheLocal::delete('page', $this->getCollectionID() . ':' . 'ACTIVE');
        Events::fire('on_page_version_refresh_cache', $this);
    }

    public function get(&$c, $cvID)
    {
        $db = Loader::db();

        if (($c instanceof Page) && $c->getCollectionPointerID()) {
            $v = array($c->getCollectionPointerID());
        } else {
            $v = array($c->getCollectionID());
        }


        $q = 'SELECT cvID, cvIsApproved, cvIsNew, cvHandle, cvName, cvDescription, cvDateCreated, cvDatePublic, cvAuthorUID, cvApproverUID, cvComments, ptID, CollectionVersions.ctID, ctHandle, ctName FROM CollectionVersions LEFT JOIN PageTypes ON CollectionVersions.ctID = PageTypes.ctID WHERE cID = ?';
        if ($cvID == 'ACTIVE') {
            $q .= ' and cvIsApproved = 1';
        } elseif ($cvID == 'RECENT') {
            $q .= ' order by cvID desc';
        } else {
            $v[] = $cvID;
            $q .= ' and cvID = ?';
        }

        $row = $db->GetRow($q, $v);
        $cv  = new CollectionVersion();

        if (is_array($row) && $row['cvID']) {
            $cv->setPropertiesFromArray($row);
        }

        // load the attributes for a particular version object
        $cv->cID = $c->getCollectionID();

        return $cv;
    }

    public function getAttribute($ak, $c, $displayMode = false)
    {
        if (is_object($ak)) {
            $akHandle = $ak->getAttributeKeyHandle();
        } else {
            $akHandle = $ak;
            $ak       = null;
        }
        $akHash = $akHandle . ':' . $displayMode;

        if (!isset($this->attributes[$akHash])) {
            $this->attributes[$akHash] = false;
            if (!$ak) {
                $ak = CollectionAttributeKey::getByHandle($akHandle);
            }
            if (is_object($ak)) {
                $av = $c->getAttributeValueObject($ak);
                if (is_object($av)) {
                    $this->attributes[$akHash] = $av->getValue($displayMode);
                }
            }
        }

        return $this->attributes[$akHash];
    }

    public function isApproved()
    {
        return $this->cvIsApproved;
    }

    public function isMostRecent()
    {
        if (!isset($this->isMostRecent)) {
            $cID                = $this->cID;
            $db                 = Loader::db();
            $q                  = "select cvID from CollectionVersions where cID = '{$cID}' order by cvID desc";
            $cvID               = $db->getOne($q);
            $this->isMostRecent = ($cvID == $this->cvID);
        }

        return $this->isMostRecent;
    }

    public function isNew()
    {
        return $this->cvIsNew;
    }

    public function getVersionID()
    {
        return $this->cvID;
    }

    public function getCollectionID()
    {
        return $this->cID;
    }

    public function getVersionName()
    {
        return $this->cvName;
    }

    public function getVersionComments()
    {
        return $this->cvComments;
    }

    public function getVersionAuthorUserID()
    {
        return $this->cvAuthorUID;
    }

    public function getVersionApproverUserID()
    {
        return $this->cvApproverUID;
    }

    public function getVersionAuthorUserName()
    {
        if ($this->cvAuthorUID > 0) {
            $db = Loader::db();

            return $db->GetOne('SELECT uName FROM Users WHERE uID = ?', array($this->cvAuthorUID));
        }
    }

    public function getVersionApproverUserName()
    {
        if ($this->cvApproverUID > 0) {
            $db = Loader::db();

            return $db->GetOne('SELECT uName FROM Users WHERE uID = ?', array($this->cvApproverUID));
        }
    }

    public function getCustomAreaStyles()
    {
        if (!isset($this->customAreaStyles)) {
            $db                     = Loader::db();
            $r                      = $db->GetAll('SELECT csrID, arHandle FROM CollectionVersionAreaStyles WHERE cID = ? AND cvID = ?', array($this->getCollectionID(), $this->cvID));
            $this->customAreaStyles = array();
            foreach ($r as $styles) {
                $this->customAreaStyles[$styles['arHandle']] = $styles['csrID'];
            }
        }

        return $this->customAreaStyles;
    }

    /**
     * Gets the date the collection version was created
     * if user is specified, returns in the current user's timezone.
     *
     * @param string $type (system || user)
     *
     * @return string date formated like: 2009-01-01 00:00:00
     */
    public function getVersionDateCreated($type = 'system')
    {
        if (ENABLE_USER_TIMEZONES && $type == 'user') {
            $dh = Loader::helper('date');

            return $dh->getLocalDateTime($this->cvDateCreated);
        } else {
            return $this->cvDateCreated;
        }
    }

    public function canWrite()
    {
        return $this->cvCanWrite;
    }

    public function setComment($comment)
    {
        $thisCVID = $this->getVersionID();
        $comment  = ($comment != null) ? $comment : "Version {$thisCVID}";
        $v        = array($comment, $thisCVID, $this->cID);
        $db       = Loader::db();
        $q        = 'UPDATE CollectionVersions SET cvComments = ? WHERE cvID = ? AND cID = ?';
        $r        = $db->query($q, $v);

        $this->versionComments = $comment;
    }

    public function createNew($versionComments)
    {
        $db         = Loader::db();
        $highestVID = $db->GetOne('SELECT max(cvID) FROM CollectionVersions WHERE cID = ?', array($this->cID));
        $newVID     = $highestVID + 1;
        $c          = Page::getByID($this->cID, $this->cvID);

        $u               = new User();
        $versionComments = (!$versionComments) ? t('New Version %s', $newVID) : $versionComments;
        $cvIsNew         = 1;
        if ($c->getCollectionTypeHandle() == STACKS_PAGE_TYPE) {
            $cvIsNew = 0;
        }
        $dh = Loader::helper('date');
        $v  = array($this->cID, $newVID, $c->getCollectionName(), $c->getCollectionHandle(), $c->getCollectionDescription(), $c->getCollectionDatePublic(), $dh->getSystemDateTime(), $versionComments, $u->getUserID(), $cvIsNew, $this->ptID, $this->ctID);
        $q  = 'INSERT INTO CollectionVersions (cID, cvID, cvName, cvHandle, cvDescription, cvDatePublic, cvDateCreated, cvComments, cvAuthorUID, cvIsNew, ptID, ctID)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $q2 = 'SELECT akID, avID FROM CollectionAttributeValues WHERE cID = ? AND cvID = ?';
        $v2 = array($c->getCollectionID(), $this->getVersionID());
        $r2 = $db->query($q2, $v2);
        while ($row2 = $r2->fetchRow()) {
            $v3           = array(intval($c->getCollectionID()), $newVID, $row2['akID'], $row2['avID']);
            $recordExists = intval($db->getOne('SELECT count(*) FROM CollectionAttributeValues WHERE cID=? AND cvID=? AND akID=? AND avID=?', $v3)) ? 1 : 0;
            if (!$recordExists) {
                $db->query('INSERT INTO CollectionAttributeValues (cID, cvID, akID, avID) VALUES (?, ?, ?, ?)', $v3);
            }
        }

        $r   = $db->prepare($q);
        $res = $db->execute($r, $v);

        $nv = CollectionVersion::get($c, $newVID);
        Events::fire('on_page_version_add', $c, $nv);
        $nv->refreshCache();

        // now we return it
        return $nv;
    }


    public function approve($doReindexImmediately = true)
    {
        $db   = Loader::db();
        $u    = new User();
        $uID  = $u->getUserID();
        $cvID = $this->cvID;
        $cID  = $this->cID;
        $c    = Page::getByID($cID, $this->cvID);

        $ov = Page::getByID($cID, 'ACTIVE');

        $oldHandle = $ov->getCollectionHandle();
        $newHandle = $this->cvHandle;

        // update a collection updated record
        $dh = Loader::helper('date');
        $db->query('UPDATE Collections SET cDateModified = ? WHERE cID = ?', array($dh->getLocalDateTime(), $cID));

        // first we remove approval for the other version of this collection
        $v = array($cID);
        $q = 'UPDATE CollectionVersions SET cvIsApproved = 0 WHERE cID = ?';
        $r = $db->query($q, $v);
        $ov->refreshCache();

        // now we approve our version
        $v2 = array($uID, $cID, $cvID);
        $q2 = 'UPDATE CollectionVersions SET cvIsNew = 0, cvIsApproved = 1, cvApproverUID = ? WHERE cID = ? AND cvID = ?';
        $r  = $db->query($q2, $v2);

        // next, we rescan our collection paths for the particular collection, but only if this isn't a generated collection
        // I don't know why but this just isn't reliable. It might be a race condition with the cached page objects?
        /*
        if ((($oldHandle != $newHandle) || $oldHandle == '') && (!$c->isGeneratedCollection())) {
        */

        $c->rescanCollectionPath();

        //}

        // check for related version edits. This only gets applied when we edit global areas.
        $r = $db->Execute('SELECT cRelationID, cvRelationID FROM CollectionVersionRelatedEdits WHERE cID = ? AND cvID = ?', array($cID, $cvID));
        while ($row = $r->FetchRow()) {
            $cn  = Page::getByID($row['cRelationID'], $row['cvRelationID']);
            $cnp = new Permissions($cn);
            if ($cnp->canApprovePageVersions()) {
                $v = $cn->getVersionObject();
                $v->approve();
                $db->Execute('DELETE FROM CollectionVersionRelatedEdits WHERE cID = ? AND cvID = ? AND cRelationID = ? AND cvRelationID = ?', array($cID, $cvID, $row['cRelationID'], $row['cvRelationID']));
            }
        }

        if ($c->getCollectionInheritance() == 'TEMPLATE') {
            // we make sure to update the cInheritPermissionsFromCID value
            $ct      = CollectionType::getByID($c->getCollectionTypeID());
            $masterC = $ct->getMasterTemplate();
            $db->Execute('UPDATE Pages SET cInheritPermissionsFromCID = ? WHERE cID = ?', array($masterC->getCollectionID(), $c->getCollectioniD()));
        }

        Events::fire('on_page_version_approve', $c);
        $c->reindex(false, $doReindexImmediately);
        $this->refreshCache();
    }

    public function discard()
    {
        // discard's my most recent edit that is pending
        $u = new User();
        if ($this->isNew()) {
            $this->delete();
        }
        $this->refreshCache();
    }

    public function canDiscard()
    {
        $db    = Loader::db();
        $total = $db->GetOne('SELECT count(cvID) FROM CollectionVersions WHERE cID = ?', array($this->cID));

        return $this->isNew() && $total > 1;
    }

    public function removeNewStatus()
    {
        $db = Loader::db();
        $db->query('UPDATE CollectionVersions SET cvIsNew = 0 WHERE cID = ? AND cvID = ?', array($this->cID, $this->cvID));
        $this->refreshCache();
    }

    public function deny()
    {
        $db   = Loader::db();
        $cvID = $this->cvID;
        $cID  = $this->cID;

        // first we update a collection updated record
        $dh = Loader::helper('date');
        $db->query('UPDATE Collections SET cDateModified = ? WHERE cID = ?', array($dh->getLocalDateTime(), $cID));

        // first we remove approval for all versions of this collection
        $v = array($cID);
        $q = 'UPDATE CollectionVersions SET cvIsApproved = 0 WHERE cID = ?';
        $r = $db->query($q, $v);

        // now we deny our version
        $v2 = array($cID, $cvID);
        $q2 = 'UPDATE CollectionVersions SET cvIsApproved = 0, cvApproverUID = 0 WHERE cID = ? AND cvID = ?';
        $r2 = $db->query($q2, $v2);
        $this->refreshCache();
    }

    public function delete()
    {
        $db = Loader::db();

        $cvID = $this->cvID;
        $c    = Page::getByID($this->cID, $cvID);
        $cID  = $c->getCollectionID();

        $q = 'SELECT bID, arHandle FROM CollectionVersionBlocks WHERE cID = ? AND cvID = ?';
        $r = $db->query($q, array($cID, $cvID));
        if ($r) {
            while ($row = $r->fetchRow()) {
                if ($row['bID']) {
                    $b = Block::getByID($row['bID'], $c, $row['arHandle']);
                    if (is_object($b)) {
                        $b->deleteBlock();
                    }
                }
                unset($b);
            }
        }

        $r = $db->Execute('SELECT avID, akID FROM CollectionAttributeValues WHERE cID = ? AND cvID = ?', array($cID, $cvID));
        Loader::model('attribute/categories/collection');
        while ($row = $r->FetchRow()) {
            $cak = CollectionAttributeKey::getByID($row['akID']);
            $cav = $c->getAttributeValueObject($cak);
            if (is_object($cav)) {
                $cav->delete();
            }
        }

        $db->Execute('DELETE FROM CollectionVersionBlockStyles WHERE cID = ? AND cvID = ?', array($cID, $cvID));
        $db->Execute('DELETE FROM CollectionVersionRelatedEdits WHERE cID = ? AND cvID = ?', array($cID, $cvID));
        $db->Execute('DELETE FROM CollectionVersionAreaStyles WHERE cID = ? AND cvID = ?', array($cID, $cvID));
        $db->Execute('DELETE FROM CollectionVersionAreaLayouts WHERE cID = ? AND cvID = ?', array($cID, $cvID));

        $q = "delete from CollectionVersions where cID = '{$cID}' and cvID='{$cvID}'";
        $r = $db->query($q);
        $this->refreshCache();
    }
}

/**
 * An object that holds a list of versions for a particular collection.
 *
 * @author Andrew Embler <andrew@concrete5.org>
 *
 * @category Concrete
 *
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class VersionList extends Object
{

    public $vArray = array();

    public function VersionList(&$c, $limit = -1, $page = false)
    {
        $db = Loader::db();

        $cID         = $c->getCollectionID();
        $this->total = $db->GetOne('SELECT count(cvID) FROM CollectionVersions WHERE cID = ?', $cID);
        $q           = "select cvID from CollectionVersions where cID = '$cID' order by cvID desc ";
        if ($page > 1) {
            $pl = ($page - 1) * $limit;
        }
        if ($page > 1) {
            $q .= 'limit ' . $pl . ',' . $limit;
        } elseif ($limit > -1) {
            $q .= 'limit ' . $limit;
        }
        $r = $db->query($q);

        if ($r) {
            while ($row = $r->fetchRow()) {
                $this->vArray[] = CollectionVersion::get($c, $row['cvID'], true);
            }
            $r->free();
        }

        return $this;
    }

    public function getVersionListArray()
    {
        return $this->vArray;
    }

    public function getVersionListCount()
    {
        return $this->total;
    }
}
