<?php
defined('C5_EXECUTE') or die('Access Denied.');

/**
 * An object for individual Guestbook responses.
 *
 * @author Ryan Tyler <ryan@concrete5.org>
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2012 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Controller_Block_GuestbookEntry
{
    /**
     * blocks bID.
     *
     * @var int
     */
    public $bID;

    /**
     * page collectionID.
     *
     * @var int
     */
    public $cID;

    /**
     * blocks uID user id.
     *
     * @var int
     */
    public $uID;

    /**
     * the entry id.
     *
     * @var int
     */
    public $entryID;

    /**
     * the user's name.
     *
     * @var string
     */
    public $user_name;

    /**
     * the user's email address.
     *
     * @var string
     */
    public $user_email;

    /**
     * the text for the comment.
     *
     * @var string
     */
    public $commentText;

    public function __construct($bID, $cID = null)
    {
        $this->bID = $bID;
        $this->cID = $cID;
    }

    /**
     * Loads the object data from the db.
     *
     * @param int $entryID
     *
     * @return bool
     */
    public function loadData($entryID)
    {
        $db   = Loader::db();
        $data = $db->getRow('SELECT * FROM btGuestBookEntries WHERE entryID=? AND bID=?', array($entryID, $this->bID));

        $this->entryID     = $data['entryID'];
        $this->user_name   = $data['user_name'];
        $this->user_email  = $data['user_email'];
        $this->commentText = $data['commentText'];
        $this->uID         = $data['uID'];
    }

    /**
     * Adds an entry to the guestbook for the current block.
     *
     * @param string $comment
     * @param string $name
     * @param string $email
     */
    public function addEntry($comment, $name, $email, $approved, $cID, $uID = 0)
    {
        $txt = Loader::helper('text');

        $db    = Loader::db();
        $query = 'INSERT INTO btGuestBookEntries (bID, cID, uID, user_name, user_email, commentText, entryDate, approved) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)';
        $res   = $db->query($query, array($this->bID, $cID, intval($uID), $txt->sanitize($name), $txt->sanitize($email), $txt->sanitize($comment), $approved));

        $this->adjustCountCache(1);
    }

    /**
     * Adjusts cache of count bynumber specified,.
     *
     * Refreshes from db if cache is invalidated or
     * false is called in
     */
    private function adjustCountCache($number = false)
    {
        $ca    = new Cache();
        $db    = Loader::db();
        $count = $ca->get('GuestBookCount', $this->cID . '-' . $this->bID);
        if ($count && $number) {
            $count += $number;
        } else {
            $q     = 'SELECT count(bID) AS count
				FROM btGuestBookEntries
				WHERE bID = ?
				AND cID = ?
				AND approved=1';
            $v     = array($this->bID, $this->cID);
            $rs    = $db->query($q, $v);
            $row   = $rs->FetchRow();
            $count = $row['count'];
        }
        $ca->set('GuestBookCount', $this->cID . '-' . $this->bID, $count);
    }

    /**
     * Updates the given guestbook entry for the current block.
     *
     * @param int $entryID
     * @param string $comment
     * @param string $name
     * @param string $email
     * @param string $uID
     */
    public function updateEntry($entryID, $comment, $name, $email, $uID = 0)
    {
        $db    = Loader::db();
        $txt   = Loader::helper('text');
        $query = 'UPDATE btGuestBookEntries SET user_name=?, uID=?, user_email=?, commentText=? WHERE entryID=? AND bID=?';
        $res   = $db->query($query, array($txt->sanitize($name), intval($uID), $txt->sanitize($email), $txt->sanitize($comment), $entryID, $this->bID));
    }

    /**
     * Deletes the given guestbook entry for the current block.
     *
     * @param int $entryID
     */
    public function removeEntry($entryID)
    {
        $db    = Loader::db();
        $query = 'DELETE FROM btGuestBookEntries WHERE entryID=? AND bID=?';
        $res   = $db->query($query, array($entryID, $this->bID));
        $this->adjustCountCache(-1);
    }

    public function approveEntry($entryID)
    {
        $db    = Loader::db();
        $query = 'UPDATE btGuestBookEntries SET approved = 1 WHERE entryID=? AND bID=?';
        $res   = $db->query($query, array($entryID, $this->bID));
        $this->adjustCountCache(1);
    }

    public function unApproveEntry($entryID)
    {
        $db    = Loader::db();
        $query = 'UPDATE btGuestBookEntries SET approved = 0 WHERE entryID=? AND bID=?';
        $res   = $db->query($query, array($entryID, $this->bID));
        $this->adjustCountCache(-1);
    }

    /**
     * Deletes all the entries for the current block.
     */
    public function removeAllEntries($cID)
    {
        $db    = Loader::db();
        $query = 'DELETE FROM btGuestBookEntries WHERE bID=? AND cID = ?';
        $res   = $db->query($query, array($this->bID, $cID));
        $this->adjustCountCache(false);
    }

    /**
     * gets all entries for the current block.
     *
     * @param int $bID
     * @param string $order ASC|DESC
     *
     * @return array $rows
     */
    public static function getAll($bID, $cID, $order = 'ASC')
    {
        $db    = Loader::db();
        $query = "SELECT * FROM btGuestBookEntries WHERE bID = ? AND cID = ? ORDER BY entryDate {$order}";

        $rows = $db->getAll($query, array($bID, $cID));

        return $rows;
    }
} // end class def

