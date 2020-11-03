<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @category Concrete
 *
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

/**
 * PageStatistics functions as a name space containing functions that return page-level statistics.
 *
 * @author Andrew Embler <andrew@concrete5.org>
 *
 * @category Concrete
 *
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Model_PageStatistics
{

    /**
     * Gets total page views across the entire site.
     *
     * @param date $date
     *
     * @return int
     */
    public static function getTotalPageViews($date = null)
    {
        $db = Loader::db();
        if ($date != null) {
            return $db->GetOne('SELECT count(pstID) FROM PageStatistics WHERE date = ?', array($date));
        } else {
            return $db->GetOne('SELECT count(pstID) FROM PageStatistics');
        }
    }

    /**
     * Gets total page views for everyone but the passed user object.
     *
     * @param User $u
     * @param date $date
     *
     * @return int
     */
    public static function getTotalPageViewsForOthers($u, $date = null)
    {
        $db = Loader::db();
        if ($date != null) {
            $v = array($u->getUserID(), $date);

            return $db->GetOne('SELECT count(pstID) FROM PageStatistics WHERE uID <> ? AND date = ?', $v);
        } else {
            $v = array($u->getUserID());

            return $db->GetOne('SELECT count(pstID) FROM PageStatistics WHERE uID <> ?', $v);
        }
    }

    /**
     * Gets the total number of versions across all pages. Used in the dashboard.
     *
     * @todo It might be nice if this were a little more generalized
     *
     * @return int
     */
    public static function getTotalPageVersions()
    {
        $db = Loader::db();

        return $db->GetOne('SELECT count(cvID) FROM CollectionVersions');
    }

    /**
     * Returns the datetime of the last edit to the site. Used in the dashboard.
     *
     * @return datetime
     */
    public static function getSiteLastEdit($type = 'system')
    {
        $db            = Loader::db();
        $cDateModified = $db->GetOne('SELECT max(Collections.cDateModified) FROM Collections');
        if (ENABLE_USER_TIMEZONES && $type == 'user') {
            $dh = Loader::helper('date');

            return $dh->getLocalDateTime($cDateModified);
        } else {
            return $cDateModified;
        }
    }

    /**
     * Gets the total number of pages currently in edit mode.
     *
     * @return int
     */
    public static function getTotalPagesCheckedOut()
    {
        $db = Loader::db();

        return $db->GetOne('SELECT count(cID) FROM Pages WHERE cIsCheckedOut = 1');
    }


    /**
     * For a particular page ID, grabs all the pages of this page, and increments the cTotalChildren number for them.
     */
    public static function incrementParents($cID)
    {
        $db        = Loader::db();
        $cParentID = $db->GetOne('SELECT cParentID FROM Pages WHERE cID = ?', array($cID));

        $q = 'UPDATE Pages SET cChildren = cChildren+1 WHERE cID = ?';

        $cpc = Page::getByID($cParentID);
        $cpc->refreshCache();

        $r = $db->query($q, array($cParentID));
    }

    /**
     * For a particular page ID, grabs all the pages of this page, and decrements the cTotalChildren number for them.
     */
    public static function decrementParents($cID)
    {
        $db        = Loader::db();
        $cParentID = $db->GetOne('SELECT cParentID FROM Pages WHERE cID = ?', array($cID));
        $cChildren = $db->GetOne('SELECT cChildren FROM Pages WHERE cID = ?', array($cParentID));
        $cChildren--;
        if ($cChildren < 0) {
            $cChildren = 0;
        }

        $q = 'UPDATE Pages SET cChildren = ? WHERE cID = ?';

        $cpc = Page::getByID($cParentID);
        $cpc->refreshCache();

        $r = $db->query($q, array($cChildren, $cParentID));
    }

    /**
     * Returns the total number of pages created for a given date.
     */
    public static function getTotalPagesCreated($date)
    {
        $db  = Loader::db();
        $num = $db->GetOne('SELECT count(Pages.cID) FROM Pages INNER JOIN Collections ON Pages.cID = Collections.cID WHERE cDateAdded >= ? AND cDateAdded <= ? AND cIsSystemPage = 0 AND cIsTemplate = 0', array($date . ' 00:00:00', $date . ' 23:59:59'));

        return $num;
    }
}
