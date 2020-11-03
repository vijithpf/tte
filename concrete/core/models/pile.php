<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Essentially a user's scrapbook, a pile is an object used for clumping bits of content together around a user account.
 * Piles currently only contain blocks but they could also contain collections. Any bit of content inside a user's pile
 * can be reordered, etc... although no public interface makes use of much of this functionality.
 */
class Concrete5_Model_Pile extends Object
{

    public $pID, $uID, $isDefault, $name, $state, $timestamp;

    public function getUserID()
    {
        return $this->uID;
    }

    public function getPileID()
    {
        return $this->pID;
    }

    public function isDefault()
    {
        return $this->isDefault;
    }

    public function getPileName()
    {
        return $this->name;
    }

    public function getPileState()
    {
        return $this->state;
    }

    public function get($pID)
    {
        $db  = Loader::db();
        $v   = array($pID);
        $q   = 'SELECT pID, uID, isDefault, name, state FROM Piles WHERE pID = ?';
        $r   = $db->query($q, $v);
        $row = $r->fetchRow();

        $p = new Pile();
        if (is_array($row)) {
            foreach ($row as $k => $v) {
                $p->{$k} = $v;
            }
        }

        return $p;
    }

    public function create($name)
    {
        $db = Loader::db();
        $u  = new User();
        $v  = array($u->getUserID(), 0, $name, 'READY');
        $q  = 'INSERT INTO Piles (uID, isDefault, name, state) VALUES (?, ?, ?, ?)';
        $r  = $db->query($q, $v);
        if ($r) {
            $pID = $db->Insert_ID();

            return Pile::get($pID);
        }
    }

    public function getOrCreate($name)
    {
        $db  = Loader::db();
        $u   = new User();
        $v   = array($name, $u->getUserID());
        $q   = 'SELECT pID FROM Piles WHERE name = ? AND uID = ?';
        $pID = $db->getOne($q, $v);

        if ($pID > 0) {
            return Pile::get($pID);
        }

        $v = array($u->getUserID(), 0, $name, 'READY');
        $q = 'INSERT INTO Piles (uID, isDefault, name, state) VALUES (?, ?, ?, ?)';
        $r = $db->query($q, $v);
        if ($r) {
            $pID = $db->Insert_ID();

            return Pile::get($pID);
        }
    }

    public function createDefaultPile()
    {
        $db = Loader::db();
        // for the sake of data integrity, we're going to ensure that a general pile does not exist
        $u = new User();
        if ($u->isRegistered()) {
            $v = array($u->getUserID(), 1);
            $q = 'SELECT pID FROM Piles WHERE uID = ? AND isDefault = ?';
        }
        $pID = $db->getOne($q, $v);
        if ($pID > 0) {
            $p = new Pile($pID);

            return $p;
        } else {
            // create a new one
            $v = array($u->getUserID(), 1, null, 'READY');
            $q = 'INSERT INTO Piles (uID, isDefault, name, state) VALUES (?, ?, ?, ?)';
            $r = $db->query($q, $v);
            if ($r) {
                $pID = $db->Insert_ID();

                return Pile::get($pID);
            }
        }
    }

    public function inPile($obj)
    {
        $db    = Loader::db();
        $v     = array();
        $class = strtoupper(get_class($obj));
        switch ($class) {
            case 'COLLECTION':
                $v = array('COLLECTION', $obj->getCollectionID());
                break;
            case 'BLOCK':
                $v = array('BLOCK', $obj->getBlockID());
                break;
        }
        $v[]  = $this->getPileID();
        $q    = 'SELECT pcID FROM PileContents WHERE itemType = ? AND itemID = ? AND pID = ?';
        $pcID = $db->getOne($q, $v);

        return ($pcID > 0);
    }

    public function getDefault()
    {
        $db = Loader::db();
        // checks to see if we're registered, or if we're a visitor. Either way, we get a pile entry
        $u = new User();
        if ($u->isRegistered()) {
            $v = array($u->getUserID(), 1);
            $q = 'SELECT pID FROM Piles WHERE uID = ? AND isDefault = ?';
        }
        $pID = $db->getOne($q, $v);
        if ($pID > 0) {
            $p = Pile::get($pID);

            return $p;
        } else {
            // create a new one
            $p = Pile::createDefaultPile();

            return $p;
        }
    }

    public function getMyPiles()
    {
        $db = Loader::db();

        $u = new User();
        if ($u->isRegistered()) {
            $v = array($u->getUserID());
            $q = 'SELECT pID FROM Piles WHERE uID = ? ORDER BY name ASC';
        }

        $piles = array();
        $r     = $db->query($q, $v);
        if ($r) {
            while ($row = $r->fetchRow()) {
                $piles[] = Pile::get($row['pID']);
            }
        }

        return $piles;
    }

    public function isMyPile()
    {
        $u = new User();

        if ($u->isRegistered()) {
            return $this->getUserID() == $u->getUserID();
        }
    }

    public function delete()
    {
        $db = Loader::db();
        $v  = array($this->pID);
        $q  = 'DELETE FROM Piles WHERE pID = ?';
        $db->query($q, $v);
        $q2 = 'DELETE FROM PileContents WHERE pID = ?';
        $db->query($q, $v);
    }

    public function getPileLength()
    {
        $db = Loader::db();
        $q  = 'SELECT count(pcID) FROM PileContents WHERE pID = ?';
        $v  = array($this->pID);
        $r  = $db->getOne($q, $v);
        if ($r > 0) {
            return $r;
        } else {
            return 0;
        }
    }

    public function getPileContentObjects($display = 'display_order')
    {
        $pc = array();
        $db = Loader::db();
        switch ($display) {
            case 'display_order_date':
                $order = 'displayOrder asc, timestamp desc';
                break;
            case 'date_desc':
                $order = 'timestamp desc';
                break;
            default:
                $order = 'displayOrder asc';
                break;
        }

        $v = array($this->pID);
        $q = "select pcID from PileContents where pID = ? order by {$order}";
        $r = $db->query($q, $v);
        while ($row = $r->fetchRow()) {
            $pc[] = PileContent::get($row['pcID']);
        }

        return $pc;
    }

    public function add(&$obj, $quantity = 1)
    {
        $db           = Loader::db();
        $existingPCID = $this->getPileContentID($obj);
        $v1           = array($this->pID);
        $q1           = 'SELECT max(displayOrder) AS displayOrder FROM PileContents WHERE pID = ?';
        $currentDO    = $db->getOne($q1, $v1);
        $displayOrder = $currentDO + 1;
        if (!$existingPCID) {
            switch (strtolower(get_class($obj))) {
                case 'page':
                    $v = array($this->pID, $obj->getCollectionID(), 'COLLECTION', $quantity, $displayOrder);
                    break;
                case 'block':
                    $v = array($this->pID, $obj->getBlockID(), 'BLOCK', $quantity, $displayOrder);
                    break;
                case 'pilecontent':
                    $v = array($this->pID, $obj->getItemID(), $obj->getItemType(), $obj->getQuantity(), $displayOrder);
                    break;
            }
            $q = 'INSERT INTO PileContents (pID, itemID, itemType, quantity, displayOrder) VALUES (?, ?, ?, ?, ?)';
            $r = $db->query($q, $v);
            if ($r) {
                $pcID = $db->Insert_ID();

                return $pcID;
            }
        } else {
            return $existingPCID;
        }
    }

    public function remove(&$obj, $quantity = 1)
    {
        $db = Loader::db();
        switch (strtolower(get_class($obj))) {
            case 'page':
                $v = array($this->pID, $obj->getCollectionID(), 'COLLECTION');
                break;
            case 'block':
                $v = array($this->pID, $obj->getBlockID(), 'BLOCK');
                break;
            case 'pilecontent':
                $v = array($this->pID, $obj->getItemID(), $obj->getItemType());
                break;
        }

        $q          = 'SELECT quantity FROM PileContents WHERE pID = ? AND itemID = ? AND itemType = ?';
        $exQuantity = $db->getOne($q, $v);
        if ($exQuantity > $quantity) {
            $db->query("update PileContent set quantity = quantity - {$quantity} where pID = ? and itemID = ? and itemType = ?", $v);
        } else {
            $db->query('DELETE FROM PileContents WHERE pID = ? AND itemID = ? AND itemType = ?', $v);
        }
    }

    public function getPileContentID(&$obj)
    {
        $db = Loader::db();
        switch (strtolower(get_class($obj))) {
            case 'page':
                $v    = array($this->pID, $obj->getCollectionID(), 'COLLECTION');
                $q    = 'SELECT pcID FROM PileContents WHERE pID = ? AND itemID = ? AND itemType = ?';
                $pcID = $db->getOne($q, $v);
                if ($pcID > 0) {
                    return $pcID;
                }
                break;
        }
    }

    public function rescanDisplayOrder()
    {
        $db                  = Loader::db();
        $v                   = array($this->pID);
        $q                   = 'SELECT pcID FROM PileContents WHERE pID = ? ORDER BY displayOrder ASC';
        $r                   = $db->query($q, $v);
        $currentDisplayOrder = 0;
        while ($row = $r->fetchRow()) {
            $v1 = array($currentDisplayOrder, $row['pcID']);
            $q1 = 'UPDATE PileContents SET displayOrder = ? WHERE pcID = ?';
            $db->query($q1, $v1);
            $currentDisplayOrder++;
        }
    }
}
