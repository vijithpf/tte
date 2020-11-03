<?php defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @author     Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */

/**
 * The user object deals primarily with logging users in and session-related activities.
 *
 * @category   Concrete
 *
 * @copyright  Copyright (c) 2003-2008 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Model_User extends Object
{

    public    $uID              = '';
    public    $uName            = '';
    public    $uGroups          = array();
    public    $superUser        = false;
    public    $uTimezone        = null;
    protected $uDefaultLanguage = null;
    // an associative array of all access entity objects that are associated with this user.
    protected $accessEntities = array();
    protected $hasher;

    /** Return an User instance given its id (or null if it's not found)
     *
     * @param int  $uID               The id of the user
     * @param bool $login             = false Set to true to make the user the current one
     * @param bool $cacheItemsOnLogin = false Set to true to cache some items when $login is true
     *
     * @return User|null
     */
    public static function getByUserID($uID, $login = false, $cacheItemsOnLogin = true)
    {
        $db  = Loader::db();
        $v   = array($uID);
        $q   = 'SELECT uID, uName, uIsActive, uLastOnline, uTimezone, uDefaultLanguage FROM Users WHERE uID = ? LIMIT 1';
        $r   = $db->Query($q, $v);
        $row = $r ? $r->FetchRow() : null;
        $nu  = null;
        if ($row) {
            $nu                   = new User();
            $nu->uID              = $row['uID'];
            $nu->uName            = $row['uName'];
            $nu->uIsActive        = $row['uIsActive'];
            $nu->uDefaultLanguage = $row['uDefaultLanguage'];
            $nu->uLastLogin       = $row['uLastLogin'];
            $nu->uTimezone        = $row['uTimezone'];
            $nu->uGroups          = $nu->_getUserGroups(true);
            $nu->superUser        = ($nu->getUserID() == USER_SUPER_ID);
            if ($login) {
                User::regenerateSession();
                $_SESSION['uID']              = $row['uID'];
                $_SESSION['uName']            = $row['uName'];
                $_SESSION['uBlockTypesSet']   = false;
                $_SESSION['uGroups']          = $nu->uGroups;
                $_SESSION['uLastOnline']      = $row['uLastOnline'];
                $_SESSION['uTimezone']        = $row['uTimezone'];
                $_SESSION['uDefaultLanguage'] = $row['uDefaultLanguage'];
                if ($cacheItemsOnLogin) {
                    Loader::helper('concrete/interface')->cacheInterfaceItems();
                }
                $nu->recordLogin();
            }
        }

        return $nu;
    }

    protected static function regenerateSession()
    {
        unset($_SESSION['dashboardMenus']);
        unset($_SESSION['ccmQuickNavRecentPages']);
        unset($_SESSION['accessEntities']);

        $tmpSession = $_SESSION;
        session_write_close();
        @setcookie(session_name(), session_id(), time() - 100000);
        session_id(sha1(mt_rand()));
        @session_start();
        $_SESSION = $tmpSession;
    }

    /**
     * @param int $uID
     *
     * @return User
     */
    public function loginByUserID($uID)
    {
        return User::getByUserID($uID, true);
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['uID']) && $_SESSION['uID'] > 0 && isset($_SESSION['uName']) && $_SESSION['uName'] != '';
    }

    public function checkLogin()
    {
        $aeu = Config::get('ACCESS_ENTITY_UPDATED');
        if ($aeu && $aeu > $_SESSION['accessEntitiesUpdated']) {
            User::refreshUserGroups();
        }

        if ($_SESSION['uID'] > 0) {
            $db       = Loader::db();
            $row      = $db->GetRow('SELECT uID, uIsActive FROM Users WHERE uID = ? AND uName = ?', array($_SESSION['uID'], $_SESSION['uName']));
            $checkUID = $row['uID'];
            if ($checkUID == $_SESSION['uID']) {
                if (!$row['uIsActive']) {
                    return false;
                }
                $_SESSION['uOnlineCheck'] = time();
                if (($_SESSION['uOnlineCheck'] - $_SESSION['uLastOnline']) > (ONLINE_NOW_TIMEOUT / 2)) {
                    $db = Loader::db();
                    $db->query("update Users set uLastOnline = {$_SESSION['uOnlineCheck']} where uID = {$this->uID}");
                    $_SESSION['uLastOnline'] = $_SESSION['uOnlineCheck'];
                }

                return true;
            } else {
                return false;
            }
        }
    }

    public function __construct()
    {
        $args = func_get_args();

        if (isset($args[1])) {
            // first, we check to see if the username and password match the admin username and password
            // $username = uName normally, but if not it's email address

            $username = $args[0];
            $password = $args[1];
            if (!$args[2]) {
                $_SESSION['uGroups'] = false;
            }
            $v = array($username);
            if (defined('USER_REGISTRATION_WITH_EMAIL_ADDRESS') && USER_REGISTRATION_WITH_EMAIL_ADDRESS == true) {
                $q = 'SELECT uID, uName, uIsActive, uIsValidated, uTimezone, uDefaultLanguage, uPassword FROM Users WHERE uEmail = ?';
            } else {
                $q = 'SELECT uID, uName, uIsActive, uIsValidated, uTimezone, uDefaultLanguage, uPassword FROM Users WHERE uName = ?';
            }
            $db = Loader::db();
            $r  = $db->query($q, $v);
            if ($r) {
                $row                = $r->fetchRow();
                $pw_is_valid_legacy = (defined('PASSWORD_SALT') && User::legacyEncryptPassword($password) == $row['uPassword']);
                $pw_is_valid        = $pw_is_valid_legacy || $this->getUserPasswordHasher()->checkPassword($password, $row['uPassword']);
                if ($row['uID'] && $row['uIsValidated'] === '0' && defined('USER_VALIDATE_EMAIL_REQUIRED') && USER_VALIDATE_EMAIL_REQUIRED == true) {
                    $this->loadError(USER_NON_VALIDATED);
                } elseif ($row['uID'] && $row['uIsActive'] && $pw_is_valid) {
                    $this->uID              = $row['uID'];
                    $this->uName            = $row['uName'];
                    $this->uIsActive        = $row['uIsActive'];
                    $this->uTimezone        = $row['uTimezone'];
                    $this->uDefaultLanguage = $row['uDefaultLanguage'];
                    $this->uGroups          = $this->_getUserGroups($args[2]);
                    if ($row['uID'] == USER_SUPER_ID) {
                        $this->superUser = true;
                    } else {
                        $this->superUser = false;
                    }
                    $this->recordLogin();
                    if (!$args[2]) {
                        User::regenerateSession();
                        $_SESSION['uID']              = $row['uID'];
                        $_SESSION['uName']            = $row['uName'];
                        $_SESSION['superUser']        = $this->superUser;
                        $_SESSION['uBlockTypesSet']   = false;
                        $_SESSION['uGroups']          = $this->uGroups;
                        $_SESSION['uTimezone']        = $this->uTimezone;
                        $_SESSION['uDefaultLanguage'] = $this->uDefaultLanguage;
                        Loader::helper('concrete/interface')->cacheInterfaceItems();
                    }
                } elseif ($row['uID'] && !$row['uIsActive']) {
                    $this->loadError(USER_INACTIVE);
                } else {
                    $this->loadError(USER_INVALID);
                }
                $r->free();
                if ($pw_is_valid_legacy) {
                    // this password was generated on a previous version of Concrete5. 
                    // We re-hash it to make it more secure.
                    $v = array($this->getUserPasswordHasher()->HashPassword($password), $this->uID);
                    $db->execute($db->prepare('UPDATE Users SET uPassword = ? WHERE uID = ?'), $v);
                }
            } else {
                $this->getUserPasswordHasher()->hashpassword($password); // hashpassword and checkpassword are slow functions. 
                // We run one here just take time.
                // Without it an attacker would be able to tell that the 
                // username doesn't exist using a timing attack.
                $this->loadError(USER_INVALID);
            }
        } else {
            $req = Request::get();
            if ($req->hasCustomRequestUser()) {
                $this->uID              = null;
                $this->uName            = null;
                $this->superUser        = false;
                $this->uDefaultLanguage = null;
                $this->uTimezone        = null;
                $ux                     = $req->getCustomRequestUser();
                if ($ux) {
                    $this->uID       = $ux->getUserID();
                    $this->uName     = $ux->getUserName();
                    $this->superUser = $ux->getUserID() == USER_SUPER_ID;
                    if ($ux->getUserDefaultLanguage()) {
                        $this->uDefaultLanguage = $ux->getUserDefaultLanguage();
                    }
                    $this->uTimezone = $ux->getUserTimezone();
                }
            } elseif (isset($_SESSION['uID'])) {
                $this->uID       = $_SESSION['uID'];
                $this->uName     = $_SESSION['uName'];
                $this->uTimezone = $_SESSION['uTimezone'];
                if (isset($_SESSION['uDefaultLanguage'])) {
                    $this->uDefaultLanguage = $_SESSION['uDefaultLanguage'];
                }
                $this->superUser = ($_SESSION['uID'] == USER_SUPER_ID) ? true : false;
            } else {
                $this->uID              = null;
                $this->uName            = null;
                $this->superUser        = false;
                $this->uDefaultLanguage = null;
                $this->uTimezone        = null;
            }
            $this->uGroups = $this->_getUserGroups();
            if (!isset($args[2]) && !$req->hasCustomRequestUser()) {
                $_SESSION['uGroups'] = $this->uGroups;
            }
        }

        return $this;
    }

    public function recordLogin()
    {
        $db         = Loader::db();
        $uLastLogin = $db->getOne('SELECT uLastLogin FROM Users WHERE uID = ?', array($this->uID));

        $db->query('UPDATE Users SET uLastIP = ?, uLastLogin = ?, uPreviousLogin = ?, uNumLogins = uNumLogins + 1 WHERE uID = ?', array(ip2long(Loader::helper('validation/ip')->getRequestIP()), time(), $uLastLogin, $this->uID));
    }

    public function recordView($c)
    {
        $db  = Loader::db();
        $uID = ($this->uID > 0) ? $this->uID : 0;
        $cID = $c->getCollectionID();
        $v   = array($cID, $uID);
        $db->query('INSERT INTO PageStatistics (cID, uID, date) VALUES (?, ?, NOW())', $v);
    }

    // $salt is retained for compatibilty with older versions of concerete5, but not used.
    public function encryptPassword($uPassword, $salt = null)
    {
        return $this->getUserPasswordHasher()->HashPassword($uPassword);
    }

    // this is for compatibility with passwords generated in older versions of Concrete5. 
    // Use only for checking password hashes, not generating new ones to store.
    public function legacyEncryptPassword($uPassword)
    {
        return md5($uPassword . ':' . PASSWORD_SALT);
    }

    public function isActive()
    {
        return $this->uIsActive;
    }

    public function isSuperUser()
    {
        return $this->superUser;
    }

    public function getLastOnline()
    {
        return $this->uLastOnline;
    }

    public function getUserName()
    {
        return $this->uName;
    }

    public function isRegistered()
    {
        return $this->getUserID() > 0;
    }

    public function getUserID()
    {
        return $this->uID;
    }

    public function getUserTimezone()
    {
        return $this->uTimezone;
    }

    public function logout()
    {
        // First, we check to see if we have any collection in edit mode
        $this->unloadCollectionEdit();
        @session_unset();
        @session_destroy();
        Events::fire('on_user_logout');
        if (isset($_COOKIE['ccmUserHash']) && $_COOKIE['ccmUserHash']) {
            setcookie('ccmUserHash', '', 315532800, DIR_REL . '/',
                (defined('SESSION_COOKIE_PARAM_DOMAIN') ? SESSION_COOKIE_PARAM_DOMAIN : ''),
                (defined('SESSION_COOKIE_PARAM_SECURE') ? SESSION_COOKIE_PARAM_SECURE : false),
                (defined('SESSION_COOKIE_PARAM_HTTPONLY') ? SESSION_COOKIE_PARAM_HTTPONLY : false));
        }
    }

    public static function checkUserForeverCookie()
    {
        if (isset($_COOKIE['ccmUserHash']) && $_COOKIE['ccmUserHash']) {
            $hash = $_COOKIE['ccmUserHash'];
            $uID  = UserValidationHash::getUserID($hash, UVTYPE_LOGIN_FOREVER);
            if (is_numeric($uID) && $uID > 0) {
                User::loginByUserID($uID);
            }
        }
    }

    public function setUserForeverCookie()
    {
        $uHash = UserValidationHash::add($this->getUserID(), UVTYPE_LOGIN_FOREVER);
        setcookie('ccmUserHash',
                  $uHash,
                  time() + USER_FOREVER_COOKIE_LIFETIME,
                  DIR_REL . '/',
            (defined('SESSION_COOKIE_PARAM_DOMAIN') ? SESSION_COOKIE_PARAM_DOMAIN : ''),
            (defined('SESSION_COOKIE_PARAM_SECURE') ? SESSION_COOKIE_PARAM_SECURE : false),
            (defined('SESSION_COOKIE_PARAM_HTTPONLY') ? SESSION_COOKIE_PARAM_HTTPONLY : false)
        );
    }

    public function getUserGroups()
    {
        return $this->uGroups;
    }

    /**
     * Sets a default language for a user record.
     */
    public function setUserDefaultLanguage($lang)
    {
        $db                           = Loader::db();
        $this->uDefaultLanguage       = $lang;
        $_SESSION['uDefaultLanguage'] = $lang;
        $db->Execute('UPDATE Users SET uDefaultLanguage = ? WHERE uID = ?', array($lang, $this->getUserID()));
    }

    /**
     * Gets the default language for the logged-in user.
     */
    public function getUserDefaultLanguage()
    {
        return $this->uDefaultLanguage;
    }

    public function refreshUserGroups()
    {
        unset($_SESSION['uGroups']);
        unset($_SESSION['accessEntities']);
        $ug                  = $this->_getUserGroups();
        $_SESSION['uGroups'] = $ug;
        $this->uGroups       = $ug;
    }

    public function getUserAccessEntityObjects()
    {
        $req = Request::get();
        if ($req->hasCustomRequestUser()) {
            // we bypass session-saving performance
            // and we don't save them in session.
            return PermissionAccessEntity::getForUser($this);
        }

        if (isset($_SESSION['accessEntities'])) {
            $entities = $_SESSION['accessEntities'];
        } else {
            $entities                          = PermissionAccessEntity::getForUser($this);
            $_SESSION['accessEntities']        = $entities;
            $_SESSION['accessEntitiesUpdated'] = time();
        }

        return $entities;
    }

    public function _getUserGroups($disableLogin = false)
    {
        $req = Request::get();
        if ((!empty($_SESSION['uGroups'])) && (!$disableLogin) && (!$req->hasCustomRequestUser())) {
            $ug = $_SESSION['uGroups'];
        } else {
            $db = Loader::db();
            if ($this->uID) {
                $ug[REGISTERED_GROUP_ID] = REGISTERED_GROUP_ID;
                //$_SESSION['uGroups'][REGISTERED_GROUP_ID] = REGISTERED_GROUP_NAME;

                $uID = $this->uID;
                $q   = "select Groups.gID, Groups.gName, Groups.gUserExpirationIsEnabled, Groups.gUserExpirationSetDateTime, Groups.gUserExpirationInterval, Groups.gUserExpirationAction, Groups.gUserExpirationMethod, UserGroups.ugEntered from UserGroups inner join Groups on (UserGroups.gID = Groups.gID) where UserGroups.uID = '$uID'";
                $r   = $db->query($q);
                if ($r) {
                    while ($row = $r->fetchRow()) {
                        $expire = false;
                        if ($row['gUserExpirationIsEnabled']) {
                            switch ($row['gUserExpirationMethod']) {
                                case 'SET_TIME':
                                    if (time() > strtotime($row['gUserExpirationSetDateTime'])) {
                                        $expire = true;
                                    }
                                    break;
                                case 'INTERVAL':
                                    if (time() > strtotime($row['ugEntered']) + ($row['gUserExpirationInterval'] * 60)) {
                                        $expire = true;
                                    }
                                    break;
                            }
                        }

                        if ($expire) {
                            if ($row['gUserExpirationAction'] == 'REMOVE' || $row['gUserExpirationAction'] == 'REMOVE_DEACTIVATE') {
                                $db->Execute('DELETE FROM UserGroups WHERE uID = ? AND gID = ?', array($uID, $row['gID']));
                            }
                            if ($row['gUserExpirationAction'] == 'DEACTIVATE' || $row['gUserExpirationAction'] == 'REMOVE_DEACTIVATE') {
                                $db->Execute('UPDATE Users SET uIsActive = 0 WHERE uID = ?', array($uID));
                            }
                        } else {
                            $ug[$row['gID']] = $row['gName'];
                        }
                    }
                    $r->free();
                }
            }

            // now we populate also with guest information, since presumably logged-in users 
            // see the same stuff as guest
            $ug[GUEST_GROUP_ID] = GUEST_GROUP_ID;
        }

        return $ug;
    }

    public function enterGroup($g, $joinType = '')
    {
        // takes a group object, and, if the user is not already in the group, it puts them into it
        $dt = Loader::helper('date');

        if (is_object($g)) {
            $gID = $g->getGroupID();
            $db  = Loader::db();
            $db->Replace('UserGroups', array(
                'uID'       => $this->getUserID(),
                'gID'       => $g->getGroupID(),
                'type'      => $joinType,
                'ugEntered' => $dt->getSystemDateTime(),
            ),
                         array('uID', 'gID'), true);
            Events::fire('on_user_enter_group', $this, $g);
        }
    }

    public function updateGroupMemberType($g, $joinType)
    {
        if ($g instanceof Group) {
            $db = Loader::db();
            $dt = Loader::helper('date');
            $db->Execute('UPDATE UserGroups SET type = ?, ugEntered = ? WHERE uID = ? AND gID = ?', array($joinType, $dt->getSystemDateTime(), $this->uID, $g->getGroupID()));
        }
    }

    public function exitGroup($g)
    {
        // takes a group object, and, if the user is in the group, they exit the group
        if (is_object($g)) {
            $gID = $g->getGroupID();
            $db  = Loader::db();

            $ret = Events::fire('on_user_exit_group', $this, $g);
            $q   = "delete from UserGroups where uID = '{$this->uID}' and gID = '{$gID}'";
            $r   = $db->query($q);
        }
    }

    public function getGroupMemberType($g)
    {
        $db = Loader::db();
        $r  = $db->GetOne('SELECT type FROM UserGroups WHERE uID = ? AND gID = ?', array($this->getUserID(), $g->getGroupID()));

        return $r;
    }

    public function inGroup($g, $joinType = null)
    {
        $db = Loader::db();
        if (isset($joinType) && is_object($g)) {
            $v   = array($this->uID, $g->getGroupID(), $joinType);
            $cnt = $db->GetOne('SELECT gID FROM UserGroups WHERE uID = ? AND gID = ? AND type = ?', $v);
        } elseif (is_object($g)) {
            $v   = array($this->uID, $g->getGroupID());
            $cnt = $db->GetOne('SELECT gID FROM UserGroups WHERE uID = ? AND gID = ?', $v);
        }

        return $cnt > 0;
    }

    public function loadMasterCollectionEdit($mcID, $ocID)
    {
        // basically, this function loads the master collection ID you're working on into session
        // so you can work on it without the system failing because you're editing a template
        $_SESSION['mcEditID'] = $mcID;
        $_SESSION['ocID']     = $ocID;
    }

    public function loadCollectionEdit(&$c)
    {
        $c->refreshCache();

        // can only load one page into edit mode at a time.
        if ($c->isCheckedOut()) {
            return false;
        }

        $db  = Loader::db();
        $cID = $c->getCollectionID();
        // first, we check to see if we have a collection in edit mode. If we do, we relinquish it
        $this->unloadCollectionEdit(false);

        $q = "select cIsCheckedOut, cCheckedOutDatetime from Pages where cID = '{$cID}'";
        $r = $db->query($q);
        if ($r) {
            $row = $r->fetchRow();
            if (!$row['cIsCheckedOut']) {
                $_SESSION['editCID'] = $cID;
                $uID                 = $this->getUserID();
                $dh                  = Loader::helper('date');
                $datetime            = $dh->getSystemDateTime();
                $q2                  = "update Pages set cIsCheckedOut = 1, cCheckedOutUID = '{$uID}', cCheckedOutDatetime = '{$datetime}', cCheckedOutDatetimeLastEdit = '{$datetime}' where cID = '{$cID}'";
                $r2                  = $db->query($q2);

                $c->cIsCheckedOut               = 1;
                $c->cCheckedOutUID              = $uID;
                $c->cCheckedOutDatetime         = $datetime;
                $c->cCheckedOutDatetimeLastEdit = $datetime;
            }
        }
    }

    public function unloadCollectionEdit($removeCache = true)
    {
        // first we remove the cached versions of all of these pages
        $db = Loader::db();
        if ($this->getUserID() > 0) {
            $col = $db->GetCol('SELECT cID FROM Pages WHERE cCheckedOutUID = ' . $this->getUserID());
            foreach ($col as $cID) {
                $p = Page::getByID($cID);
                if ($removeCache) {
                    $p->refreshCache();
                }
            }

            $q = 'UPDATE Pages SET cIsCheckedOut = 0, cCheckedOutUID = NULL, cCheckedOutDatetime = NULL, cCheckedOutDatetimeLastEdit = NULL WHERE cCheckedOutUID = ?';
            $db->query($q, array($this->getUserID()));
        }
    }

    public function config($cfKey)
    {
        if ($this->isRegistered()) {
            $db  = Loader::db();
            $val = $db->GetOne('SELECT cfValue FROM Config WHERE uID = ? AND cfKey = ?', array($this->getUserID(), $cfKey));

            return $val;
        }
    }

    public function saveConfig($cfKey, $cfValue)
    {
        $db = Loader::db();
        $db->Replace('Config', array('cfKey' => $cfKey, 'cfValue' => $cfValue, 'uID' => $this->getUserID()), array('cfKey', 'uID'), true);
    }

    public function refreshCollectionEdit(&$c)
    {
        if ($this->isLoggedIn() && $c->getCollectionCheckedOutUserID() == $this->getUserID()) {
            $db  = Loader::db();
            $cID = $c->getCollectionID();

            $dh       = Loader::helper('date');
            $datetime = $dh->getSystemDateTime();

            $q = "update Pages set cCheckedOutDatetimeLastEdit = '{$datetime}' where cID = '{$cID}'";
            $r = $db->query($q);

            $c->cCheckedOutDatetimeLastEdit = $datetime;
        }
    }

    public function forceCollectionCheckInAll()
    {
        // This function forces checkin to take place
        $db = Loader::db();
        $q  = 'UPDATE Pages SET cIsCheckedOut = 0, cCheckedOutUID = NULL, cCheckedOutDatetime = NULL, cCheckedOutDatetimeLastEdit = NULL';
        $r  = $db->query($q);

        return $r;
    }

    /**
     * @see PasswordHash
     *
     * @return PasswordHash
     */
    public function getUserPasswordHasher()
    {
        if (isset($this->hasher)) {
            return $this->hasher;
        }
        Loader::library('3rdparty/phpass/PasswordHash');
        $this->hasher = new PasswordHash(PASSWORD_HASH_COST_LOG2, PASSWORD_HASH_PORTABLE);

        return $this->hasher;
    }

    public function isClientAdmin()
    {
        return array_search(CLIENT_ADMIN_GROUP_NAME, $this->getUserGroups(), true) !== false;
    }

    public function isAdmin()
    {
        return $this->isClientAdmin() || $this->isSuperUser();
    }
}
