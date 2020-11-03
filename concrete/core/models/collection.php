<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Contains the collection object.
 *
 * @author Andrew Embler <andrew@concrete5.org>
 *
 * @category Concrete
 *
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

/**
 * A generic object that holds blocks and maps them to areas.
 *
 * @author Andrew Embler <andrew@concrete5.org>
 *
 * @category Concrete
 *
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Model_Collection extends Object
{

    public    $cID;
    protected $attributes = array();

    /* version specific stuff */

    public function loadVersionObject($cvID = 'ACTIVE')
    {
        $this->vObj = CollectionVersion::get($this, $cvID);
    }

    public function getVersionToModify()
    {
        // first, we check to see if the version we're modifying has the same
        // author uID associated with it as we currently have, and if it's inactive
        // If that's the case, then we just return the current collection + version object.

        $u    = new User();
        $vObj = $this->getVersionObject();
        if ($this->isMasterCollection() || ($vObj->isNew())) {
            return $this;
        } else {
            // otherwise, we have to clone this version of the collection entirely,
            // and return that collection.

            $nc = $this->cloneVersion($versionComments);

            return $nc;
        }
    }

    public function getNextVersionComments()
    {
        $c    = Page::getByID($this->getCollectionID(), 'ACTIVE');
        $cvID = $c->getVersionID();

        return t('Version %d', $cvID + 1);
    }


    public function cloneVersion($versionComments)
    {
        // first, we run the version object's createNew() command, which returns a new
        // version object, which we can combine with our collection object, so we'll have
        // our original collection object ($this), and a new collection object, consisting
        // of our collection + the new version
        $vObj     = $this->getVersionObject();
        $nvObj    = $vObj->createNew($versionComments);
        $nc       = Page::getByID($this->getCollectionID());
        $nc->vObj = $nvObj;
        // now that we have the original version object and the cloned version object,
        // we're going to select all the blocks that exist for this page, and we're going
        // to copy them to the next version
        // unless btIncludeAll is set -- as that gets included no matter what

        $db   = Loader::db();
        $cID  = $this->getCollectionID();
        $cvID = $vObj->getVersionID();
        $q    = "select bID, arHandle from CollectionVersionBlocks where cID = '$cID' and cvID = '$cvID' and cbIncludeAll=0 order by cbDisplayOrder asc";
        $r    = $db->query($q);
        if ($r) {
            while ($row = $r->fetchRow()) {
                // now we loop through these, create block objects for all of them, and
                // duplicate them to our collection object (which is actually the same collection,
                // but different version)
                $b = Block::getByID($row['bID'], $this, $row['arHandle']);
                if (is_object($b)) {
                    $b->alias($nc);
                }
            }
        }

        // duplicate any area styles
        $q = "select csrID, arHandle from CollectionVersionAreaStyles where cID = '$cID' and cvID = '$cvID'";
        $r = $db->query($q);
        while ($row = $r->FetchRow()) {
            $db->Execute('INSERT INTO CollectionVersionAreaStyles (cID, cvID, arHandle, csrID) VALUES (?, ?, ?, ?)', array(
                $this->getCollectionID(),
                $nvObj->getVersionID(),
                $row['arHandle'],
                $row['csrID'],
            ));
        }

        // duplicate any area layout joins
        $q = "select * from CollectionVersionAreaLayouts where cID = '$cID' and cvID = '$cvID'";
        $r = $db->query($q);
        while ($row = $r->FetchRow()) {
            $db->Execute('INSERT INTO CollectionVersionAreaLayouts (cID, cvID, arHandle, layoutID, areaNameNumber, position) VALUES (?, ?, ?, ?, ?, ?)', array(
                $this->getCollectionID(),
                $nvObj->getVersionID(),
                $row['arHandle'],
                $row['layoutID'],
                $row['areaNameNumber'],
                $row['position'],
            ));
        }

        // now that we've duplicated all the blocks for the collection, we return the new
        // collection

        return $nc;
    }

    /* attribute stuff */

    /**
     * Returns the value of the attribute with the handle $ak
     * of the current object.
     *
     * $displayMode makes it possible to get the correct output
     * value. When you need the raw attribute value or object, use
     * this:
     * <code>
     * $c = Page::getCurrentPage();
     * $attributeValue = $c->getAttribute('attribute_handle');
     * </code>
     *
     * But if you need the formatted output supported by some
     * attribute, use this:
     * <code>
     * $c = Page::getCurrentPage();
     * $attributeValue = $c->getAttribute('attribute_handle', 'display');
     * </code>
     *
     * An attribute type like "date" will then return the date in
     * the correct format just like other attributes will show
     * you a nicely formatted output and not just a simple value
     * or object.
     *
     *
     * @param string|object $akHandle
     * @param bool $displayMode
     *
     * @return type
     */
    public function getAttribute($akHandle, $displayMode = false)
    {
        if (is_object($this->vObj)) {
            return $this->vObj->getAttribute($akHandle, $this, $displayMode);
        }
    }

    public function getCollectionAttributeValue($ak)
    {
        if (is_object($this->vObj)) {
            return $this->vObj->getAttribute($ak, $this);
        }
    }

    // remove the collection attributes for this version of a page
    public function clearCollectionAttributes($retainAKIDs = array())
    {
        $db = Loader::db();
        if (count($retainAKIDs) > 0) {
            $cleanAKIDs = array();
            foreach ($retainAKIDs as $akID) {
                $cleanAKIDs[] = intval($akID);
            }
            $akIDStr = implode(',', $cleanAKIDs);
            $v2      = array($this->getCollectionID(), $this->getVersionID());
            $db->query("delete from CollectionAttributeValues where cID = ? and cvID = ? and akID not in ({$akIDStr})", $v2);
        } else {
            $v2 = array($this->getCollectionID(), $this->getVersionID());
            $db->query('DELETE FROM CollectionAttributeValues WHERE cID = ? AND cvID = ?', $v2);
        }
        $this->reindex();
    }

    public static function reindexPendingPages()
    {
        $num = 0;
        $db  = Loader::db();
        $r   = $db->Execute('SELECT cID FROM PageSearchIndex WHERE cRequiresReindex = 1');
        while ($row = $r->FetchRow()) {
            $pc = Page::getByID($row['cID']);
            $pc->reindex($this, true);
            $num++;
        }
        Config::save('DO_PAGE_REINDEX_CHECK', false);

        return $num;
    }

    public function hasLayouts()
    {
        $cHasLayouts = CacheLocal::getEntry('page_layouts', $this->getCollectionID() . ':' . $this->vObj->getVersionID());
        if ($cHasLayouts === -1) {
            return false;
        } elseif ($cHasLayouts) {
            return $cHasLayouts;
        }
        $db          = Loader::db();
        $cHasLayouts = $db->GetOne('SELECT count(cvalID) FROM CollectionVersionAreaLayouts WHERE cID = ? AND cvID = ?', array($this->cID, $this->vObj->getVersionID()));
        if (!$cHasLayouts) {
            CacheLocal::set('page_layouts', $this->getCollectionID() . ':' . $this->vObj->getVersionID(), -1);
        } else {
            CacheLocal::set('page_layouts', $this->getCollectionID() . ':' . $this->vObj->getVersionID(), 1);
        }

        return $cHasLayouts;
    }

    public function reindex($index = false, $actuallyDoReindex = true)
    {
        if ($this->isAlias()) {
            return false;
        }
        if ($actuallyDoReindex || ENABLE_PROGRESSIVE_PAGE_REINDEX == false) {
            $db = Loader::db();

            Loader::model('attribute/categories/collection');
            $attribs = CollectionAttributeKey::getAttributes($this->getCollectionID(), $this->getVersionID(), 'getSearchIndexValue');

            $db->Execute('DELETE FROM CollectionSearchIndexAttributes WHERE cID = ?', array($this->getCollectionID()));
            $searchableAttributes = array('cID' => $this->getCollectionID());
            $rs                   = $db->Execute('SELECT * FROM CollectionSearchIndexAttributes WHERE cID = -1');
            AttributeKey::reindex('CollectionSearchIndexAttributes', $searchableAttributes, $attribs, $rs);

            if ($index == false) {
                Loader::library('database_indexed_search');
                $index = new IndexedSearch();
            }

            $index->reindexPage($this);
            $db->Replace('PageSearchIndex', array('cID' => $this->getCollectionID(), 'cRequiresReindex' => 0), array('cID'), false);

            $cache = PageCache::getLibrary();
            $cache->purge($this);
        } else {
            $db = Loader::db();
            Config::save('DO_PAGE_REINDEX_CHECK', true);
            $db->Replace('PageSearchIndex', array('cID' => $this->getCollectionID(), 'cRequiresReindex' => 1), array('cID'), false);
        }
    }

    public function getAttributeValueObject($ak, $createIfNotFound = false)
    {
        $db = Loader::db();
        $av = false;
        if (is_string($ak)) {
            $ak = CollectionAttributeKey::getByHandle($ak);
        }
        $v    = array($this->getCollectionID(), $this->getVersionID(), $ak->getAttributeKeyID());
        $avID = $db->GetOne('SELECT avID FROM CollectionAttributeValues WHERE cID = ? AND cvID = ? AND akID = ?', $v);
        if ($avID > 0) {
            $av = CollectionAttributeValue::getByID($avID);
            if (is_object($av)) {
                $av->setCollection($this);
                $av->setAttributeKey($ak);
            }
        }

        if ($createIfNotFound) {
            $cnt = 0;

            // Is this avID in use ?
            if (is_object($av)) {
                $cnt = $db->GetOne('SELECT count(avID) FROM CollectionAttributeValues WHERE avID = ?', $av->getAttributeValueID());
            }

            if ((!is_object($av)) || ($cnt > 1)) {
                $newAV = $ak->addAttributeValue();
                $av    = CollectionAttributeValue::getByID($newAV->getAttributeValueID());
                $av->setCollection($this);
            }
        }

        return $av;
    }

    public function setAttribute($ak, $value)
    {
        Loader::model('attribute/categories/collection');
        if (!is_object($ak)) {
            $ak = CollectionAttributeKey::getByHandle($ak);
        }
        $ak->setAttribute($this, $value);
        unset($ak);
        $this->reindex();
    }

    public function clearAttribute($ak)
    {
        $db  = Loader::db();
        $cav = $this->getAttributeValueObject($ak);
        if (is_object($cav)) {
            $cav->delete();
        }
        $this->reindex();
    }

    // get's an array of collection attribute objects that are attached to this collection. Does not get values
    public function getSetCollectionAttributes()
    {
        $db      = Loader::db();
        $akIDs   = $db->GetCol('SELECT akID FROM CollectionAttributeValues WHERE cID = ? AND cvID = ?', array($this->getCollectionID(), $this->getVersionID()));
        $attribs = array();
        foreach ($akIDs as $akID) {
            $attribs[] = CollectionAttributeKey::getByID($akID);
        }

        return $attribs;
    }

    public function addAttribute($ak, $value)
    {
        $this->setAttribute($ak, $value);
    }

    /* area stuff */

    public function getArea($arHandle)
    {
        return Area::get($this, $arHandle);
    }

    /* aliased content */

    public function hasAliasedContent()
    {
        $db = Loader::db();
        // aliased content is content on the particular page that is being
        // used elsewhere - but the content on the PAGE is the original version
        $v        = array($this->cID);
        $q        = 'SELECT bID FROM CollectionVersionBlocks WHERE cID = ? AND isOriginal = 1';
        $r        = $db->query($q, $v);
        $bIDArray = array();
        if ($r) {
            while ($row = $r->fetchRow()) {
                $bIDArray[] = $row['bID'];
            }
            if (count($bIDArray) > 0) {
                $bIDList    = implode(',', $bIDArray);
                $v2         = array($bIDList, $this->cID);
                $q2         = 'SELECT cID FROM CollectionVersionBlocks WHERE bID IN (?) AND cID <> ? LIMIT 1';
                $aliasedCID = $db->getOne($q2, $v2);
                if ($aliasedCID > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /* basic CRUD */

    public function getCollectionID()
    {
        return $this->cID;
    }

    public function getCollectionDateLastModified($mask = null, $type = 'system')
    {
        $dh = Loader::helper('date');
        if (ENABLE_USER_TIMEZONES && $type == 'user') {
            $cDateModified = $dh->getLocalDateTime($this->cDateModified);
        } else {
            $cDateModified = $this->cDateModified;
        }
        if ($mask == null) {
            return $cDateModified;
        } else {
            return $dh->date($mask, strtotime($cDateModified));
        }
    }

    public function getVersionObject()
    {
        return $this->vObj;
    }

    public function getCollectionHandle()
    {
        return $this->cHandle;
    }

    public function getCollectionDateAdded($mask = null, $type = 'system')
    {
        $dh = Loader::helper('date');
        if (ENABLE_USER_TIMEZONES && $type == 'user') {
            $cDateAdded = $dh->getLocalDateTime($this->cDateAdded);
        } else {
            $cDateAdded = $this->cDateAdded;
        }
        if ($mask == null) {
            return $cDateAdded;
        } else {
            return $dh->date($mask, strtotime($cDateAdded));
        }
    }

    public function getVersionID()
    {
        // shortcut
        return $this->vObj->cvID;
    }

    public function __destruct()
    {
        unset($this->vObj);
    }

    public function getCollectionAreaDisplayOrder($arHandle, $ignoreVersions = false)
    {
        // this function queries CollectionBlocks to grab the highest displayOrder value, then increments it, and returns
        // this is used to add new blocks to existing Pages/areas

        $db   = Loader::db();
        $cID  = $this->cID;
        $cvID = $this->vObj->cvID;
        if ($ignoreVersions) {
            $q = 'SELECT max(cbDisplayOrder) AS cbdis FROM CollectionVersionBlocks WHERE cID = ? AND arHandle = ?';
            $v = array($cID, $arHandle);
        } else {
            $q = 'SELECT max(cbDisplayOrder) AS cbdis FROM CollectionVersionBlocks WHERE cID = ? AND cvID = ? AND arHandle = ?';
            $v = array($cID, $cvID, $arHandle);
        }
        $r = $db->query($q, $v);
        if ($r) {
            if ($r->numRows() > 0) {
                // then we know we got a value; we increment it and return
                $res          = $r->fetchRow();
                $displayOrder = $res['cbdis'];
                if (is_null($displayOrder)) {
                    return 0;
                }
                $displayOrder++;

                return $displayOrder;
            } else {
                // we didn't get anything, so we return a zero
                return 0;
            }
        }
    }

    /**
     * Retrieves all custom style rules that should be inserted into the header on a page, whether they are defined in areas
     * or blocks.
     */
    public function outputCustomStyleHeaderItems($return = false)
    {
        $db   = Loader::db();
        $csrs = array();
        $txt  = Loader::helper('text');
        CacheLocal::set('csrCheck', $this->getCollectionID() . ':' . $this->getVersionID(), true);

        $r1 = $db->GetAll('SELECT bID, arHandle, csrID FROM CollectionVersionBlockStyles WHERE cID = ? AND cvID = ? AND csrID > 0', array($this->getCollectionID(), $this->getVersionID()));
        $r2 = $db->GetAll('SELECT arHandle, csrID FROM CollectionVersionAreaStyles WHERE cID = ? AND cvID = ? AND csrID > 0', array($this->getCollectionID(), $this->getVersionID()));
        foreach ($r1 as $r) {
            $csrID    = $r['csrID'];
            $arHandle = $txt->filterNonAlphaNum($r['arHandle']);
            $bID      = $r['bID'];
            $obj      = CustomStyleRule::getByID($csrID);
            if (is_object($obj)) {
                $obj->setCustomStyleNameSpace('blockStyle' . $bID . $arHandle);
                $csrs[] = $obj;
                CacheLocal::set('csrObject', $this->getCollectionID() . ':' . $this->getVersionID() . ':' . $r['arHandle'] . ':' . $r['bID'], $obj);
            }
        }

        foreach ($r2 as $r) {
            $csrID    = $r['csrID'];
            $arHandle = $txt->filterNonAlphaNum($r['arHandle']);
            $obj      = CustomStyleRule::getByID($csrID);
            if (is_object($obj)) {
                $obj->setCustomStyleNameSpace('areaStyle' . $arHandle);
                $csrs[] = $obj;
                CacheLocal::set('csrObject', $this->getCollectionID() . ':' . $this->getVersionID() . ':' . $r['arHandle'], $obj);
            }
        }

        // grab all the header block style rules for items in global areas on this page
        $rs = $db->GetCol('SELECT arHandle FROM Areas WHERE arIsGlobal = 1 AND cID = ?', array($this->getCollectionID()));
        if (count($rs) > 0) {
            $pcp = new Permissions($this);
            foreach ($rs as $garHandle) {
                if ($pcp->canViewPageVersions()) {
                    $s = Stack::getByName($garHandle, 'RECENT');
                } else {
                    $s = Stack::getByName($garHandle, 'ACTIVE');
                }
                if (is_object($s)) {
                    CacheLocal::set('csrCheck', $s->getCollectionID() . ':' . $s->getVersionID(), true);
                    $rs1 = $db->GetAll('SELECT bID, csrID, arHandle FROM CollectionVersionBlockStyles WHERE cID = ? AND cvID = ? AND csrID > 0', array($s->getCollectionID(), $s->getVersionID()));
                    foreach ($rs1 as $r) {
                        $csrID    = $r['csrID'];
                        $arHandle = $txt->filterNonAlphaNum($r['arHandle']);
                        $bID      = $r['bID'];
                        $obj      = CustomStyleRule::getByID($csrID);
                        if (is_object($obj)) {
                            $obj->setCustomStyleNameSpace('blockStyle' . $bID . $arHandle);
                            $csrs[] = $obj;
                            CacheLocal::set('csrObject', $s->getCollectionID() . ':' . $s->getVersionID() . ':' . $r['arHandle'] . ':' . $r['bID'], $obj);
                        }
                    }
                }
            }
        }
        //get the header style rules
        $styleHeader = '';
        foreach ($csrs as $st) {
            if ($st->getCustomStyleRuleCSSID(true)) {
                $styleHeader .= '#' . $st->getCustomStyleRuleCSSID(1) . ' {' . $st->getCustomStyleRuleText() . "} \r\n";
            }
        }

        $r3 = $db->GetAll('SELECT l.layoutID, l.spacing, arHandle, areaNameNumber FROM CollectionVersionAreaLayouts cval LEFT JOIN Layouts AS l ON  cval.layoutID=l.layoutID WHERE cval.cID = ? AND cval.cvID = ?', array($this->getCollectionID(), $this->getVersionID()));
        foreach ($r3 as $data) {
            if (!intval($data['spacing'])) {
                continue;
            }
            $layoutIDVal      = strtolower('ccm-layout-' . TextHelper::camelcase($data['arHandle']) . '-' . $data['layoutID'] . '-' . $data['areaNameNumber']);
            $layoutStyleRules = '#' . $layoutIDVal . ' .ccm-layout-col-spacing { margin:0px ' . ceil(floatval($data['spacing']) / 2) . 'px }';
            $styleHeader .= $layoutStyleRules . " \r\n";
        }

        if (strlen(trim($styleHeader))) {
            if ($return == true) {
                return $styleHeader;
            } else {
                $v = View::getInstance();
                $v->addHeaderItem("<style type=\"text/css\"> \r\n" . $styleHeader . '</style>', 'VIEW');
            }
        }
    }

    public function getAreaCustomStyleRule($area)
    {
        $db = Loader::db();

        $areac = $area->getAreaCollectionObject();
        if ($areac instanceof Stack) {
            // this fixes the problem of users applying design to the main area on the page, and then that trickling into any
            // stacks that have been added to other areas of the page.
            return false;
        }

        $styles = $this->vObj->getCustomAreaStyles();
        $csrID  = $styles[$area->getAreaHandle()];

        if ($csrID > 0) {
            $txt = Loader::helper('text');
            Loader::model('custom_style');
            $arHandle = $txt->filterNonAlphaNum($area->getAreaHandle());
            $csr      = CustomStyleRule::getByID($csrID);
            if (is_object($csr)) {
                $csr->setCustomStyleNameSpace('areaStyle' . $arHandle);

                return $csr;
            }
        }
    }

    public function resetAreaCustomStyle($area)
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM CollectionVersionAreaStyles WHERE cID = ? AND cvID = ? AND arHandle = ?', array(
            $this->getCollectionID(),
            $this->getVersionID(),
            $area->getAreaHandle(),
        ));
    }

    public function setAreaCustomStyle($area, $csr)
    {
        $db = Loader::db();
        $db->Replace('CollectionVersionAreaStyles',
                     array('cID' => $this->getCollectionID(), 'cvID' => $this->getVersionID(), 'arHandle' => $area->getAreaHandle(), 'csrID' => $csr->getCustomStyleRuleID()),
                     array('cID', 'cvID', 'arHandle'), true
        );
    }


    public function addAreaLayout($area, $layout, $addToPosition = 'bottom')
    {
        $db = Loader::db();

        //get max layout name number, for fixed autonaming of layouts 
        $vals       = array(intval($this->cID), $this->getVersionID(), $area->getAreaHandle());
        $sql        = 'SELECT MAX(areaNameNumber) FROM CollectionVersionAreaLayouts WHERE cID=? AND cvID=? AND arHandle=?';
        $nextNumber = intval($db->getOne($sql, $vals)) + 1;

        if ($addToPosition == 'top') {
            $position = -1;
        } else {

            //does the main area already have blocks in it? 
            //$areaBlocks = $area->getAreaBlocksArray($this); 
            $areaBlocks = $this->getBlocks($area->getAreaHandle());

            //then copy those blocks from that area into a newly created 1x1 layout, so it can be above out new layout 
            if (count($areaBlocks)) {

                //creat new 1x1 layout to hold existing parent area blocks
                //Loader::model('layout'); 
                $placeHolderLayout = new Layout(array('rows' => 1, 'columns' => 1));
                $placeHolderLayout->save($this);
                $vals = array($this->getCollectionID(), $this->getVersionID(), $area->getAreaHandle(), $placeHolderLayout->getLayoutID(), $nextNumber, 10000);
                $sql  = 'INSERT INTO CollectionVersionAreaLayouts ( cID, cvID, arHandle, layoutID, areaNameNumber, position ) VALUES (?, ?, ?, ?, ?, ?)';
                $db->query($sql, $vals);

                //add parent area blocks to this new layout
                $placeHolderLayout->setAreaObj($area);
                $placeHolderLayout->setAreaNameNumber($nextNumber);
                $placeHolderLayoutAreaHandle = $placeHolderLayout->getCellAreaHandle(1);
                $v                           = array($placeHolderLayoutAreaHandle, $this->getCollectionID(), $this->getVersionID(), $area->getAreaHandle());
                $db->Execute('UPDATE CollectionVersionBlocks SET arHandle=? WHERE cID=? AND cvID=? AND arHandle=?', $v);
                $db->Execute('UPDATE CollectionVersionBlockStyles SET arHandle=? WHERE cID=? AND cvID=? AND arHandle=?', $v);
                $nextNumber++;
            }

            $position = 10001;
        }


        $vals = array($this->getCollectionID(), $this->getVersionID(), $area->getAreaHandle(), $layout->getLayoutID(), $nextNumber, $position);
        $sql  = 'INSERT INTO CollectionVersionAreaLayouts ( cID, cvID, arHandle, layoutID, areaNameNumber, position ) VALUES (?, ?, ?, ?, ?, ?)';
        $db->query($sql, $vals);

        $layout->setAreaNameNumber($nextNumber);
    }

    public function relateVersionEdits($oc)
    {
        $db = Loader::db();
        $v  = array(
            $this->getCollectionID(),
            $this->getVersionID(),
            $oc->getCollectionID(),
            $oc->getVersionID(),
        );
        $r  = $db->GetOne('SELECT count(*) FROM CollectionVersionRelatedEdits WHERE cID = ? AND cvID = ? AND cRelationID = ? AND cvRelationID = ?', $v);
        if ($r > 0) {
            return false;
        } else {
            $db->Execute('INSERT INTO CollectionVersionRelatedEdits (cID, cvID, cRelationID, cvRelationID) VALUES (?, ?, ?, ?)', $v);
        }
    }

    public function updateAreaLayoutId($cvalID = 0, $newLayoutId = 0)
    {
        $db = Loader::db();
        //$vals = array( $newLayoutId, $oldLayoutId, $this->getCollectionID(), $this->getVersionID(), $area->getAreaHandle() );
        //$sql = 'UPDATE CollectionVersionAreaLayouts SET layoutID=? WHERE layoutID=? AND cID=? AND  cvID=? AND arHandle=?'; 
        $vals = array($newLayoutId, $cvalID);
        $sql  = 'UPDATE CollectionVersionAreaLayouts SET layoutID=? WHERE cvalID=?';
        $db->query($sql, $vals);
    }


    public function deleteAreaLayout($area, $layout, $deleteBlocks = 0)
    {
        $db   = Loader::db();
        $vals = array($this->getCollectionID(), $this->getVersionID(), $area->getAreaHandle(), $layout->getLayoutID());
        $db->Execute('DELETE FROM CollectionVersionAreaLayouts WHERE cID = ? AND cvID = ? AND arHandle = ? AND layoutID = ? LIMIT 1', $vals);

        //also delete this layouts blocks
        $layout->setAreaObj($area);
        //we'll try to grab more areas than necessary, just incase the layout size had been reduced at some point. 
        $maxCell = $layout->getMaxCellNumber() + 20;
        for ($i = 1; $i <= $maxCell; $i++) {
            if ($deleteBlocks) {
                $layout->deleteCellsBlocks($this, $i);
            } else {
                $layout->moveCellsBlocksToParent($this, $i);
            }
        }

        Layout::cleanupOrphans();
    }

    public function getCollectionTypeID()
    {
        return false;
    }


    public function rescanDisplayOrder($areaName)
    {
        // this collection function fixes the display order properties for all the blocks within the collection/area. We select all the items
        // order by display order, and fix the sequence

        $db   = Loader::db();
        $cID  = $this->cID;
        $cvID = $this->vObj->cvID;
        $q    = "select bID from CollectionVersionBlocks where cID = '$cID' and cvID = '{$cvID}' and arHandle='$arHandle' order by cbDisplayOrder asc";
        $r    = $db->query($q);

        if ($r) {
            $displayOrder = 0;
            while ($row = $r->fetchRow()) {
                $q  = "update CollectionVersionBlocks set cbDisplayOrder = '$displayOrder' where cID = '$cID' and cvID = '{$cvID}' and arHandle = '$arHandle' and bID = '{$row['bID']}'";
                $r2 = $db->query($q);
                $displayOrder++;
            }
            $r->free();
        }
    }


    /* new cleaned up API below */

    /**
     * @param int $cID
     * @param mixed $version 'RECENT'|'ACTIVE'|version id
     *
     * @return Collection
     */
    public static function getByID($cID, $version = 'RECENT')
    {
        $db  = Loader::db();
        $q   = 'SELECT Collections.cDateAdded, Collections.cDateModified, Collections.cID FROM Collections WHERE cID = ?';
        $row = $db->getRow($q, array($cID));

        $c = new Collection();
        $c->setPropertiesFromArray($row);

        if ($version != false) {
            // we don't do this on the front page
            $c->loadVersionObject($version);
        }

        return $c;
    }

    /* This function is slightly misnamed: it should be getOrCreateByHandle($handle) but I wanted to keep it brief 
     * @param string $handle
     * @return Collection
     */
    public static function getByHandle($handle)
    {
        $db = Loader::db();

        // first we ensure that this does NOT appear in the Pages table. This is not a page. It is more basic than that 

        $r = $db->query('SELECT Collections.cID, Pages.cID AS pcID FROM Collections LEFT JOIN Pages ON Collections.cID = Pages.cID WHERE Collections.cHandle = ?', array($handle));
        if ($r->numRows() == 0) {

            // there is nothing in the collections table for this page, so we create and grab

            $data['handle'] = $handle;
            $cObj           = Collection::add($data);
        } else {
            $row = $r->fetchRow();
            if ($row['cID'] > 0 && $row['pcID'] == null) {

                // there is a collection, but it is not a page. so we grab it
                $cObj = Collection::getByID($row['cID']);
            }
        }

        if (isset($cObj)) {
            return $cObj;
        }
    }

    public function refreshCache()
    {
        CacheLocal::flush();
    }

    public function getGlobalBlocks()
    {
        $db     = Loader::db();
        $v      = array(Stack::ST_TYPE_GLOBAL_AREA);
        $rs     = $db->GetCol('SELECT stName FROM Stacks WHERE Stacks.stType = ?', $v);
        $blocks = array();
        if (count($rs) > 0) {
            $pcp = new Permissions($this);
            foreach ($rs as $garHandle) {
                if ($pcp->canViewPageVersions()) {
                    $s = Stack::getByName($garHandle, 'RECENT');
                } else {
                    $s = Stack::getByName($garHandle, 'ACTIVE');
                }
                if (is_object($s)) {
                    $blocksTmp = $s->getBlocks(STACKS_AREA_NAME);
                    $blocks    = array_merge($blocks, $blocksTmp);
                }
            }
        }

        return $blocks;
    }

    /**
     * List the block IDs in a collection or area within a collection.
     *
     * @param string $arHandle . If specified, returns just the blocks in an area
     *
     * @return array
     */
    public function getBlockIDs($arHandle = false)
    {
        $blockIDs = CacheLocal::getEntry('collection_block_ids', $this->getCollectionID() . ':' . $this->getVersionID());
        $blocks   = array();

        if (!is_array($blockIDs)) {
            $v        = array($this->getCollectionID(), $this->getVersionID());
            $db       = Loader::db();
            $q        = 'SELECT Blocks.bID, CollectionVersionBlocks.arHandle FROM CollectionVersionBlocks INNER JOIN Blocks ON (CollectionVersionBlocks.bID = Blocks.bID) INNER JOIN BlockTypes ON (Blocks.btID = BlockTypes.btID) WHERE CollectionVersionBlocks.cID = ? AND (CollectionVersionBlocks.cvID = ? OR CollectionVersionBlocks.cbIncludeAll=1) ORDER BY CollectionVersionBlocks.cbDisplayOrder ASC';
            $r        = $db->GetAll($q, $v);
            $blockIDs = array();
            if (is_array($r)) {
                foreach ($r as $bl) {
                    $blockIDs[strtolower($bl['arHandle'])][] = $bl;
                }
            }
            CacheLocal::set('collection_block_ids', $this->getCollectionID() . ':' . $this->getVersionID(), $blockIDs);
        }


        if ($arHandle != false) {
            $blockIDsTmp = $blockIDs[strtolower($arHandle)];
            $blockIDs    = $blockIDsTmp;
        } else {
            $blockIDsTmp = $blockIDs;
            $blockIDs    = array();
            foreach ($blockIDsTmp as $arHandle => $row) {
                foreach ($row as $brow) {
                    if (!in_array($brow, $blockIDs)) {
                        $blockIDs[] = $brow;
                    }
                }
            }
        }

        return $blockIDs;
    }

    /**
     * List the blocks in a collection or area within a collection.
     *
     * @param string $arHandle . If specified, returns just the blocks in an area
     *
     * @return array
     */
    public function getBlocks($arHandle = false)
    {
        $blockIDs = $this->getBlockIDs($arHandle);

        $blocks = array();
        if (is_array($blockIDs)) {
            foreach ($blockIDs as $row) {
                $ab = Block::getByID($row['bID'], $this, $row['arHandle']);
                if (is_object($ab)) {
                    $blocks[] = $ab;
                }
            }
        }

        return $blocks;
    }

    /**
     * Adds a new block the collection. Specify the block type with
     * $bt by either the BlockType object or a string with the handle
     * of the type.
     *
     * Specify the area where the block should be added with $a and
     * pass all block specific data to the block controller with the
     * array $data.
     *
     * @param BlockType /string $bt
     * @param string $a
     * @param array $data
     *
     * @return Block
     */
    public function addBlock($bt, $a, $data)
    {
        $db = Loader::db();

        if (!is_object($bt)) {
            $bt = BlockType::getByHandle($bt);
        }

        // first we add the block to the system
        $nb = $bt->add($data, $this, $a);

        // now that we have a block, we add it to the collectionversions table

        $arHandle = (is_object($a)) ? $a->getAreaHandle() : $a;
        $cID      = $this->getCollectionID();
        $vObj     = $this->getVersionObject();

        if ($bt->includeAll()) {
            // normally, display order is dependant on a per area, per version basis. However, since this block
            // is not aliased across versions, then we want to get display order simply based on area, NOT based 
            // on area + version
            $newBlockDisplayOrder = $this->getCollectionAreaDisplayOrder($arHandle, true); // second argument is "ignoreVersions"
        } else {
            $newBlockDisplayOrder = $this->getCollectionAreaDisplayOrder($arHandle);
        }

        $v = array($cID, $vObj->getVersionID(), $nb->getBlockID(), $arHandle, $newBlockDisplayOrder, 1, $bt->includeAll());
        $q = 'INSERT INTO CollectionVersionBlocks (cID, cvID, bID, arHandle, cbDisplayOrder, isOriginal, cbIncludeAll) VALUES (?, ?, ?, ?, ?, ?, ?)';

        $res = $db->Execute($q, $v);


        return Block::getByID($nb->getBlockID(), $this, $a);
    }

    public function add($data)
    {
        $db          = Loader::db();
        $dh          = Loader::helper('date');
        $cDate       = $dh->getSystemDateTime();
        $cDatePublic = ($data['cDatePublic']) ? $data['cDatePublic'] : $cDate;

        if (isset($data['cID'])) {
            $res    = $db->query('INSERT INTO Collections (cID, cHandle, cDateAdded, cDateModified) VALUES (?, ?, ?, ?)', array($data['cID'], $data['handle'], $cDate, $cDate));
            $newCID = $data['cID'];
        } else {
            $res    = $db->query('INSERT INTO Collections (cHandle, cDateAdded, cDateModified) VALUES (?, ?, ?)', array($data['handle'], $cDate, $cDate));
            $newCID = $db->Insert_ID();
        }

        $cvIsApproved = (isset($data['cvIsApproved']) && $data['cvIsApproved'] == 0) ? 0 : 1;
        $cvIsNew      = 1;
        if ($cvIsApproved) {
            $cvIsNew = 0;
        }
        if (isset($data['cvIsNew'])) {
            $cvIsNew = $data['cvIsNew'];
        }
        $data['name'] = Loader::helper('text')->sanitize($data['name']);
        if (is_object($this)) {
            $ptID = $this->getCollectionThemeID();
        } else {
            $ptID = 0;
        }
        $ctID = $data['ctID'];
        if (!$ctID) {
            $ctID = 0;
        }

        if ($res) {
            // now we add a pending version to the collectionversions table
            $v2   = array($newCID, 1, $ctID, $data['name'], $data['handle'], $data['cDescription'], $cDatePublic, $cDate, VERSION_INITIAL_COMMENT, $data['uID'], $cvIsApproved, $cvIsNew, $ptID);
            $q2   = 'INSERT INTO CollectionVersions (cID, cvID, ctID, cvName, cvHandle, cvDescription, cvDatePublic, cvDateCreated, cvComments, cvAuthorUID, cvIsApproved, cvIsNew, ptID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $r2   = $db->prepare($q2);
            $res2 = $db->execute($r2, $v2);
        }

        $nc = Collection::getByID($newCID);

        return $nc;
    }

    public function markModified()
    {
        // marks this collection as newly modified
        $db            = Loader::db();
        $dh            = Loader::helper('date');
        $cDateModified = $dh->getSystemDateTime();

        $v   = array($cDateModified, $this->cID);
        $q   = 'UPDATE Collections SET cDateModified = ? WHERE cID = ?';
        $r   = $db->prepare($q);
        $res = $db->execute($r, $v);
    }

    public function delete()
    {
        if ($this->cID > 0) {
            $db = Loader::db();

            // First we delete all versions
            $vl      = new VersionList($this);
            $vlArray = $vl->getVersionListArray();

            foreach ($vlArray as $v) {
                $v->delete();
            }

            $cID = $this->getCollectionID();

            $q = "delete from CollectionAttributeValues where cID = {$cID}";
            $db->query($q);

            $q = "delete from Collections where cID = '{$cID}'";
            $r = $db->query($q);

            $q = "delete from CollectionSearchIndexAttributes where cID = {$cID}";
            $db->query($q);
        }
    }

    public function duplicate()
    {
        $db    = Loader::db();
        $dh    = Loader::helper('date');
        $cDate = $dh->getSystemDateTime();

        $v      = array($cDate, $cDate, $this->cHandle);
        $r      = $db->query('INSERT INTO Collections (cDateAdded, cDateModified, cHandle) VALUES (?, ?, ?)', $v);
        $newCID = $db->Insert_ID();

        if ($r) {

            // first, we get the creation date of the active version in this collection
            //$q = "select cvDateCreated from CollectionVersions where cvIsApproved = 1 and cID = {$this->cID}";
            //$dcOriginal = $db->getOne($q);
            // now we create the query that will grab the versions we're going to copy

            $qv = "select * from CollectionVersions where cID = '{$this->cID}' order by cvDateCreated asc";

            // now we grab all of the current versions
            $rv     = $db->query($qv);
            $cvList = array();
            while ($row = $rv->fetchRow()) {
                // insert
                $cvList[] = $row['cvID'];
                $cDate    = date('Y-m-d H:i:s', strtotime($cDate) + 1);
                $vv       = array($newCID, $row['cvID'], $row['ctID'], $row['cvName'], $row['cvHandle'], $row['cvDescription'], $row['cvDatePublic'], $cDate, $row['cvComments'], $row['cvAuthorUID'], $row['cvIsApproved'], $row['ptID']);
                $qv       = 'INSERT INTO CollectionVersions (cID, cvID, ctID, cvName, cvHandle, cvDescription, cvDatePublic, cvDateCreated, cvComments, cvAuthorUID, cvIsApproved, ptID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                $db->query($qv, $vv);
            }

            // duplicate layout records 
            $ql = "select * from CollectionVersionAreaLayouts where cID = '{$this->cID}' order by cvalID asc";
            $rl = $db->query($ql);
            while ($row = $rl->fetchRow()) {
                $vl = array($newCID, $row['cvID'], $row['arHandle'], $row['layoutID'], $row['position'], $row['areaNameNumber']);
                $ql = 'INSERT INTO CollectionVersionAreaLayouts (cID, cvID, arHandle, layoutID, position, areaNameNumber) VALUES ( ?, ?, ?, ?, ?, ?)';
                $db->query($ql, $vl);
            }

            $ql = "select * from CollectionVersionBlockStyles where cID = '{$this->cID}'";
            $rl = $db->query($ql);
            while ($row = $rl->fetchRow()) {
                $vl = array($newCID, $row['cvID'], $row['bID'], $row['arHandle'], $row['csrID']);
                $ql = 'INSERT INTO CollectionVersionBlockStyles (cID, cvID, bID, arHandle, csrID) VALUES (?, ?, ?, ?, ?)';
                $db->query($ql, $vl);
            }
            $ql = "select * from CollectionVersionAreaStyles where cID = '{$this->cID}'";
            $rl = $db->query($ql);
            while ($row = $rl->fetchRow()) {
                $vl = array($newCID, $row['cvID'], $row['arHandle'], $row['csrID']);
                $ql = 'INSERT INTO CollectionVersionAreaStyles (cID, cvID, arHandle, csrID) VALUES (?, ?, ?, ?)';
                $db->query($ql, $vl);
            }

            // now we grab all the blocks we're going to need
            $cvList = implode(',', $cvList);
            $q      = "select bID, cvID, arHandle, cbDisplayOrder, cbOverrideAreaPermissions, cbIncludeAll from CollectionVersionBlocks where cID = '{$this->cID}' and cvID in ({$cvList})";
            $r      = $db->query($q);
            while ($row = $r->fetchRow()) {
                $v = array($newCID, $row['cvID'], $row['bID'], $row['arHandle'], $row['cbDisplayOrder'], 0, $row['cbOverrideAreaPermissions'], $row['cbIncludeAll']);
                $q = 'INSERT INTO CollectionVersionBlocks (cID, cvID, bID, arHandle, cbDisplayOrder, isOriginal, cbOverrideAreaPermissions, cbIncludeAll) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
                $db->query($q, $v);
                if ($row['cbOverrideAreaPermissions'] != 0) {
                    $q2 = "select paID, pkID from BlockPermissionAssignments where cID = '{$this->cID}' and bID = '{$row['bID']}' and cvID = '{$row['cvID']}'";
                    $r2 = $db->query($q2);
                    while ($row2 = $r2->fetchRow()) {
                        $db->Replace('BlockPermissionAssignments',
                                     array('cID' => $newCID, 'cvID' => $row['cvID'], 'bID' => $row['bID'], 'paID' => $row2['paID'], 'pkID' => $row2['pkID']),
                                     array('cID', 'cvID', 'bID', 'paID', 'pkID'), true);
                    }
                }
            }

            // duplicate any attributes belonging to the collection

            $v = array($this->getCollectionID());
            $q = 'SELECT akID, cvID, avID FROM CollectionAttributeValues WHERE cID = ?';
            $r = $db->query($q, $v);
            while ($row = $r->fetchRow()) {
                $v2 = array($row['akID'], $row['cvID'], $row['avID'], $newCID);
                $db->query('INSERT INTO CollectionAttributeValues (akID, cvID, avID, cID) VALUES (?, ?, ?, ?)', $v2);
            }

            return Collection::getByID($newCID);
        }
    }
}
