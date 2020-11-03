<?php

defined('C5_EXECUTE') or die('Access Denied.');
/**
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

/**
 * A namespace that holds functions for retrieving statistics about a user.
 *
 * @category Concrete
 *
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Model_UserStatistics extends Object
{

    protected $ui;

    public function __construct($ui)
    {
        $this->ui = $ui;
    }


    // The logic on this is a little weird. We're trying to show the number
    // of visits since last login. So we're taking your last login, and your
    // previous login (the one before that) and finding the amount of visits
    // between those values.

    public function getPreviousSessionPageViews()
    {
        $db  = Loader::db();
        $ui  = $this->ui;
        $v   = array($ui->getUserID(), $ui->getPreviousLogin(), $ui->getLastLogin());
        $num = $db->getOne('SELECT count(pstID) FROM PageStatistics WHERE uID <> ? AND PageStatistics.timestamp BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?)', $v);

        return $num;
    }

    public static function getTotalRegistrationsForDay($date)
    {
        $db  = Loader::db();
        $num = $db->GetOne('SELECT count(uID) FROM Users WHERE uDateAdded >= ? AND uDateAdded <= ?', array($date . ' 00:00:00', $date . ' 23:59:59'));

        return $num;
    }

    public static function getLastLoggedInUser()
    {
        $db  = Loader::db();
        $uID = $db->GetOne('SELECT uID FROM Users ORDER BY uLastLogin DESC');

        return UserInfo::getByID($uID);
    }
}
