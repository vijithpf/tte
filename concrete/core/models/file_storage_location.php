<?php

defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_FileStorageLocation extends Object
{

    const ALTERNATE_ID = 1;

    public function add($name, $directory, $forceID = false)
    {
        $db = Loader::db();
        if ($forceID) {
            $v = array($name, $directory, $forceID);
            $db->Execute('INSERT INTO FileStorageLocations (fslName, fslDirectory, fslID) VALUES (?, ?, ?)', $v);
            $fsl = FileStorageLocation::getByID($forceID);
        } else {
            $v = array($name, $directory);
            $db->Execute('INSERT INTO FileStorageLocations (fslName, fslDirectory, fslID) VALUES (?, ?)', $v);

            $id  = $db->Insert_ID();
            $fsl = FileStorageLocation::getByID($id);
        }

        return $fsl;
    }

    public function delete()
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM FileStorageLocations WHERE fslID = ?', array($this->fslID));
        $db->Execute('UPDATE Files SET fslID = 0 WHERE fslID = ?', array($this->fslID));
    }

    public function update($name, $directory)
    {
        $db = Loader::db();
        $db->Execute('UPDATE FileStorageLocations SET fslName = ?, fslDirectory = ? WHERE fslID = ?', array($name, $directory, $this->fslID));
    }

    public function getByID($id)
    {
        $db = Loader::db();
        $r  = $db->GetRow('SELECT * FROM FileStorageLocations WHERE fslID = ?', array($id));
        if (is_array($r) && $r['fslID'] == $id) {
            $obj = new FileStorageLocation();
            $obj->setPropertiesFromArray($r);

            return $obj;
        }
    }

    public function getID()
    {
        return $this->fslID;
    }

    public function getName()
    {
        return $this->fslName;
    }

    public function getDirectory()
    {
        return $this->fslDirectory;
    }
}
