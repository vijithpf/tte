<?php
defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Bundles groups together into different sets.
 *
 * @author Andrew Embler <andrew@concrete5.org>
 *
 * @category Concrete
 *
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Model_GroupSet extends Object
{

    public static function getList()
    {
        $db   = Loader::db();
        $r    = $db->Execute('SELECT gsID FROM GroupSets ORDER BY gsName ASC');
        $list = array();
        while ($row = $r->FetchRow()) {
            $list[] = GroupSet::getByID($row['gsID']);
        }

        return $list;
    }

    public static function getByID($gsID)
    {
        $db  = Loader::db();
        $row = $db->GetRow('SELECT gsID, pkgID, gsName FROM GroupSets WHERE gsID = ?', array($gsID));
        if (isset($row['gsID'])) {
            $gs = new GroupSet();
            $gs->setPropertiesFromArray($row);

            return $gs;
        }
    }

    public static function getByName($gsName)
    {
        $db  = Loader::db();
        $row = $db->GetRow('SELECT gsID, pkgID, gsName FROM GroupSets WHERE gsName = ?', array($gsName));
        if (isset($row['gsID'])) {
            $gs = new GroupSet();
            $gs->setPropertiesFromArray($row);

            return $gs;
        }
    }

    public static function getListByPackage($pkg)
    {
        $db   = Loader::db();
        $list = array();
        $r    = $db->Execute('SELECT gsID FROM GroupSets WHERE pkgID = ? ORDER BY gsID ASC', array($pkg->getPackageID()));
        while ($row = $r->FetchRow()) {
            $list[] = GroupSet::getByID($row['gsID']);
        }
        $r->Close();

        return $list;
    }

    public function getGroupSetID()
    {
        return $this->gsID;
    }

    public function getGroupSetName()
    {
        return $this->gsName;
    }

    public function getPackageID()
    {
        return $this->pkgID;
    }

    /** Returns the display name for this group set (localized and escaped accordingly to $format)
     * @param string $format = 'html'
     *                       Escape the result in html format (if $format is 'html').
     *                       If $format is 'text' or any other value, the display name won't be escaped.
     *
     * @return string
     */
    public function getGroupSetDisplayName($format = 'html')
    {
        $value = tc('GroupSetName', $this->getGroupSetName());
        switch ($format) {
            case 'html':
                return h($value);
            case 'text':
            default:
                return $value;
        }
    }

    public function updateGroupSetName($gsName)
    {
        $this->gsName = $gsName;
        $db           = Loader::db();
        $db->Execute('UPDATE GroupSets SET gsName = ? WHERE gsID = ?', array($gsName, $this->gsID));
    }

    public function addGroup(Group $g)
    {
        $db = Loader::db();
        $no = $db->GetOne('SELECT count(gID) FROM GroupSetGroups WHERE gID = ? AND gsID = ?', array($g->getGroupID(), $this->getGroupSetID()));
        if ($no < 1) {
            $db->Execute('INSERT INTO GroupSetGroups (gsID, gID) VALUES (?, ?)', array($this->getGroupSetID(), $g->getGroupID()));
        }
    }

    public static function add($gsName, $pkg = false)
    {
        $db    = Loader::db();
        $pkgID = 0;
        if (is_object($pkg)) {
            $pkgID = $pkg->getPackageID();
        }
        $db->Execute('INSERT INTO GroupSets (gsName, pkgID) VALUES (?,?)', array($gsName, $pkgID));
        $id = $db->Insert_ID();
        $gs = GroupSet::getByID($id);

        return $gs;
    }

    public function clearGroups()
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM GroupSetGroups WHERE gsID = ?', array($this->gsID));
    }

    public function getGroups()
    {
        $db     = Loader::db();
        $r      = $db->Execute('SELECT gID FROM GroupSetGroups WHERE gsID = ? ORDER BY gID ASC', $this->getGroupSetId());
        $groups = array();
        while ($row = $r->FetchRow()) {
            $g = Group::getByID($row['gID']);
            if (is_object($g)) {
                $groups[] = $g;
            }
        }

        return $groups;
    }

    public function contains(Group $g)
    {
        $db = Loader::db();
        $r  = $db->GetOne('SELECT count(gID) FROM GroupSetGroups WHERE gsID = ? AND gID = ?', array($this->getGroupSetID(), $g->getGroupID()));

        return $r > 0;
    }

    public function delete()
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM GroupSets WHERE gsID = ?', array($this->getGroupSetID()));
        $db->Execute('DELETE FROM GroupSetGroups WHERE gsID = ?', array($this->getGroupSetID()));
    }

    public function removeGroup(Group $g)
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM GroupSetGroups WHERE gsID = ? AND gID = ?', array($this->getGroupSetID(), $g->getGroupID()));
    }
}
