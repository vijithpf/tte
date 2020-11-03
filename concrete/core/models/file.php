<?php

class Concrete5_Model_File extends Object
{

    const CREATE_NEW_VERSION_THRESHOLD = 300; // in seconds (5 minutes)
    const F_ERROR_INVALID_FILE         = 1;
    const F_ERROR_FILE_NOT_FOUND       = 2;

    /**
     * returns a file object for the given file ID.
     *
     * @param int $fID
     *
     * @return File
     */
    public static function getByID($fID)
    {
        $db  = Loader::db();
        $f   = new File();
        $row = $db->GetRow('SELECT Files.*, FileVersions.fvID
		FROM Files LEFT JOIN FileVersions ON Files.fID = FileVersions.fID AND FileVersions.fvIsApproved = 1
		WHERE Files.fID = ?', array($fID));
        if (!is_null($fID) && $row['fID'] == $fID) {
            $f->setPropertiesFromArray($row);
        } else {
            $f->error = File::F_ERROR_INVALID_FILE;
        }

        return $f;
    }

    /**
     * For all methods that file does not implement, we pass through to the currently active file version object.
     */
    public function __call($nm, $a)
    {
        $fv = $this->getApprovedVersion();

        return call_user_func_array(array($fv, $nm), $a);
    }

    public function getPermissionObjectIdentifier()
    {
        return $this->getFileID();
    }

    public function getPath()
    {
        $fv = $this->getVersion();

        return $fv->getPath();
    }

    public function getPassword()
    {
        return $this->fPassword;
    }

    public function getStorageLocationID()
    {
        return $this->fslID;
    }

    public function refreshCache()
    {
        // NOT NECESSARY
    }

    public function reindex()
    {
        Loader::model('attribute/categories/file');
        $attribs = FileAttributeKey::getAttributes($this->getFileID(), $this->getFileVersionID(), 'getSearchIndexValue');
        $db      = Loader::db();

        $db->Execute('DELETE FROM FileSearchIndexAttributes WHERE fID = ?', array($this->getFileID()));
        $searchableAttributes = array('fID' => $this->getFileID());
        $rs                   = $db->Execute('SELECT * FROM FileSearchIndexAttributes WHERE fID = -1');
        AttributeKey::reindex('FileSearchIndexAttributes', $searchableAttributes, $attribs, $rs);
    }

    public static function getRelativePathFromID($fID)
    {
        $path = CacheLocal::getEntry('file_relative_path', $fID);
        if ($path != false) {
            return $path;
        }

        $f    = File::getByID($fID);
        $path = $f->getRelativePath();

        CacheLocal::set('file_relative_path', $fID, $path);

        return $path;
    }


    public function setStorageLocation($item)
    {
        if ($item == 0) {
            // set to default
            $itemID = 0;
            $path   = DIR_FILES_UPLOADED;
        } else {
            $itemID = $item->getID();
            $path   = $item->getDirectory();
        }

        if ($itemID != $this->getStorageLocationID()) {
            // retrieve all versions of a file and move its stuff
            $list = $this->getVersionList();
            $fh   = Loader::helper('concrete/file');
            foreach ($list as $fv) {
                $newPath  = $fh->mapSystemPath($fv->getPrefix(), $fv->getFileName(), true, $path);
                $currPath = $fv->getPath();
                rename($currPath, $newPath);
            }
            $db = Loader::db();
            $db->Execute('UPDATE Files SET fslID = ? WHERE fID = ?', array($itemID, $this->fID));
        }
    }

    public function setPassword($pw)
    {
        Events::fire('on_file_set_password', $this, $pw);
        $db = Loader::db();
        $db->Execute('UPDATE Files SET fPassword = ? WHERE fID = ?', array($pw, $this->getFileID()));
        $this->fPassword = $pw;
    }

    public function setOriginalPage($ocID)
    {
        if ($ocID < 1) {
            return false;
        }

        $db = Loader::db();
        $db->Execute('UPDATE Files SET ocID = ? WHERE fID = ?', array($ocID, $this->getFileID()));
    }

    public function getOriginalPageObject()
    {
        if ($this->ocID > 0) {
            $c = Page::getByID($this->ocID);
            if (is_object($c) && !$c->isError()) {
                return $c;
            }
        }
    }

    public function overrideFileSetPermissions()
    {
        return $this->fOverrideSetPermissions;
    }

    public function resetPermissions($fOverrideSetPermissions = 0)
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM FilePermissionAssignments WHERE fID = ?', array($this->fID));
        $db->Execute('UPDATE Files SET fOverrideSetPermissions = ? WHERE fID = ?', array($fOverrideSetPermissions, $this->fID));
        if ($fOverrideSetPermissions) {
            $permissions = PermissionKey::getList('file');
            foreach ($permissions as $pk) {
                $pk->setPermissionObject($this);
                $pk->copyFromFileSetToFile();
            }
        }
    }


    public function getUserID()
    {
        return $this->uID;
    }

    public function setUserID($uID)
    {
        $this->uID = $uID;
        $db        = Loader::db();
        $db->Execute('UPDATE Files SET uID = ? WHERE fID = ?', array($uID, $this->fID));
    }

    public function getFileSets()
    {
        $db       = Loader::db();
        $fsIDs    = $db->Execute('SELECT fsID FROM FileSetFiles WHERE fID = ?', array($this->getFileID()));
        $filesets = array();
        while ($row = $fsIDs->FetchRow()) {
            $filesets[] = FileSet::getByID($row['fsID']);
        }

        return $filesets;
    }

    public function isStarred($u = false)
    {
        if (!$u) {
            $u = new User();
        }
        $db = Loader::db();
        Loader::model('file_set');
        $r = $db->GetOne('SELECT fsfID FROM FileSetFiles fsf INNER JOIN FileSets fs ON fs.fsID = fsf.fsID WHERE fsf.fID = ? AND fs.uID = ? AND fs.fsType = ?',
                         array($this->getFileID(), $u->getUserID(), FileSet::TYPE_STARRED));

        return $r > 0;
    }

    public function getDateAdded()
    {
        return $this->fDateAdded;
    }

    /**
     * Returns a file version object that is to be written to. Computes whether we can use the current most recent version, OR a new one should be created.
     */
    public function getVersionToModify($forceCreateNew = false)
    {
        $u         = new User();
        $createNew = false;

        $fv  = $this->getRecentVersion();
        $fav = $this->getApprovedVersion();

        // first test. Does the user ID of the most recent version match ours? If not, then we create new
        if ($u->getUserID() != $fv->getAuthorUserID()) {
            $createNew = true;
        }

        // second test. If the date the version was added is older than File::CREATE_NEW_VERSION_THRESHOLD, we create new
        $unixTime = strtotime($fv->getDateAdded());
        $diff     = time() - $unixTime;
        if ($diff > File::CREATE_NEW_VERSION_THRESHOLD) {
            $createNew = true;
        }

        if ($forceCreateNew) {
            $createNew = true;
        }

        if ($createNew) {
            $fv2 = $fv->duplicate();

            // Are the recent and active versions the same? If so, we approve this new version we just made
            if ($fv->getFileVersionID() == $fav->getFileVersionID()) {
                $fv2->approve();
            }

            return $fv2;
        } else {
            return $fv;
        }
    }

    public function getFileID()
    {
        return $this->fID;
    }

    public function duplicate()
    {
        $dh   = Loader::helper('date');
        $db   = Loader::db();
        $date = $dh->getSystemDateTime();

        $far = new ADODB_Active_Record('Files');
        $far->Load('fID=?', array($this->fID));

        $far2             = clone $far;
        $far2->fID        = null;
        $far2->fDateAdded = $date;
        $far2->Insert();
        $fIDNew = $db->Insert_ID();

        $fvIDs = $db->GetCol('SELECT fvID FROM FileVersions WHERE fID = ?', $this->fID);
        foreach ($fvIDs as $fvID) {
            $farv = new ADODB_Active_Record('FileVersions');
            $farv->Load('fID=? and fvID = ?', array($this->fID, $fvID));

            $farv2                     = clone $farv;
            $farv2->fID                = $fIDNew;
            $farv2->fvActivateDatetime = $date;
            $farv2->fvDateAdded        = $date;
            $farv2->Insert();
        }

        $r = $db->Execute('SELECT fvID, akID, avID FROM FileAttributeValues WHERE fID = ?', array($this->getFileID()));
        while ($row = $r->fetchRow()) {
            $db->Execute('INSERT INTO FileAttributeValues (fID, fvID, akID, avID) VALUES (?, ?, ?, ?)', array(
                $fIDNew,
                $row['fvID'],
                $row['akID'],
                $row['avID'],
            ));
        }

        $v = array($this->fID);
        $q = 'SELECT fID, paID, pkID FROM FilePermissionAssignments WHERE fID = ?';
        $r = $db->query($q, $v);
        while ($row = $r->fetchRow()) {
            $v = array($fIDNew, $row['paID'], $row['pkID']);
            $q = 'INSERT INTO FilePermissionAssignments (fID, paID, pkID) VALUES (?, ?, ?)';
            $db->query($q, $v);
        }

        // return the new file object
        $nf = File::getByID($fIDNew);
        Events::fire('on_file_duplicate', $this, $nf);

        return $nf;
    }

    public static function add($filename, $prefix, $data = array())
    {
        $db   = Loader::db();
        $dh   = Loader::helper('date');
        $date = $dh->getSystemDateTime();

        $uID = 0;
        $u   = new User();
        if (isset($data['uID'])) {
            $uID = $data['uID'];
        } elseif ($u->isRegistered()) {
            $uID = $u->getUserID();
        }

        $db->Execute('INSERT INTO Files (fDateAdded, uID) VALUES (?, ?)', array($date, $uID));

        $fID = $db->Insert_ID();

        $f = File::getByID($fID);

        $fv = $f->addVersion($filename, $prefix, $data);
        Events::fire('on_file_add', $f, $fv);

        $entities    = $u->getUserAccessEntityObjects();
        $hasUploader = false;
        foreach ($entities as $obj) {
            if ($obj instanceof FileUploaderPermissionAccessEntity) {
                $hasUploader = true;
            }
        }
        if (!$hasUploader) {
            $u->refreshUserGroups();
        }

        return $fv;
    }

    public function addVersion($filename, $prefix, $data = array())
    {
        $u   = new User();
        $uID = (isset($data['uID']) && $data['uID'] > 0) ? $data['uID'] : $u->getUserID();

        if ($uID < 1) {
            $uID = 0;
        }

        $fvTitle       = (isset($data['fvTitle'])) ? $data['fvTitle'] : '';
        $fvDescription = (isset($data['fvDescription'])) ? $data['fvDescription'] : '';
        $fvTags        = (isset($data['fvTags'])) ? FileVersion::cleanTags($data['fvTags']) : '';
        $fvIsApproved  = (isset($data['fvIsApproved'])) ? $data['fvIsApproved'] : '1';

        $db   = Loader::db();
        $dh   = Loader::helper('date');
        $date = $dh->getSystemDateTime();

        $fvID = $db->GetOne('SELECT max(fvID) FROM FileVersions WHERE fID = ?', array($this->fID));
        if ($fvID > 0) {
            $fvID++;
        } else {
            $fvID = 1;
        }

        $db->Execute('INSERT INTO FileVersions (fID, fvID, fvFilename, fvPrefix, fvDateAdded, fvIsApproved, fvApproverUID, fvAuthorUID, fvActivateDateTime, fvTitle, fvDescription, fvTags, fvExtension) 
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array(
            $this->fID,
            $fvID,
            $filename,
            $prefix,
            $date,
            $fvIsApproved,
            $uID,
            $uID,
            $date,
            $fvTitle,
            $fvDescription,
            $fvTags,
            '',));

        $fv = $this->getVersion($fvID);
        Events::fire('on_file_version_add', $fv);

        return $fv;
    }

    public function getApprovedVersion()
    {
        return $this->getVersion();
    }

    public function inFileSet($fs)
    {
        $db = Loader::db();
        $r  = $db->GetOne('SELECT fsfID FROM FileSetFiles WHERE fID = ? AND fsID = ?', array($this->getFileID(), $fs->getFileSetID()));

        return $r > 0;
    }

    /**
     * Removes a file, including all of its versions.
     */
    public function delete()
    {
        // first, we remove all files from the drive
        $db       = Loader::db();
        $pathbase = false;
        $r        = $db->GetAll('SELECT fvFilename, fvPrefix FROM FileVersions WHERE fID = ?', array($this->fID));
        $h        = Loader::helper('concrete/file');
        Loader::model('file_storage_location');
        if ($this->getStorageLocationID() > 0) {
            $fsl      = FileStorageLocation::getByID($this->getStorageLocationID());
            $pathbase = $fsl->getDirectory();
        }
        foreach ($r as $val) {

            // Now, we make sure this file isn't referenced by something else. If it is we don't delete the file from the drive
            $cnt = $db->GetOne('SELECT count(*) AS total FROM FileVersions WHERE fID <> ? AND fvFilename = ? AND fvPrefix = ?', array(
                $this->fID,
                $val['fvFilename'],
                $val['fvPrefix'],
            ));
            if ($cnt == 0) {
                if ($pathbase != false) {
                    $path = $h->mapSystemPath($val['fvPrefix'], $val['fvFilename'], false, $pathbase);
                } else {
                    $path = $h->mapSystemPath($val['fvPrefix'], $val['fvFilename'], false);
                }
                $t1 = $h->getThumbnailSystemPath($val['fvPrefix'], $val['fvFilename'], 1);
                $t2 = $h->getThumbnailSystemPath($val['fvPrefix'], $val['fvFilename'], 2);
                $t3 = $h->getThumbnailSystemPath($val['fvPrefix'], $val['fvFilename'], 3);
                if (file_exists($path)) {
                    unlink($path);
                }
                if (file_exists($t1)) {
                    unlink($t1);
                }
                if (file_exists($t2)) {
                    unlink($t2);
                }
                if (file_exists($t3)) {
                    unlink($t3);
                }
            }
        }

        // now from the DB
        $db->Execute('DELETE FROM Files WHERE fID = ?', array($this->fID));
        $db->Execute('DELETE FROM FileVersions WHERE fID = ?', array($this->fID));
        $db->Execute('DELETE FROM FileAttributeValues WHERE fID = ?', array($this->fID));
        $db->Execute('DELETE FROM FileSetFiles WHERE fID = ?', array($this->fID));
        $db->Execute('DELETE FROM FileVersionLog WHERE fID = ?', array($this->fID));
        $db->Execute('DELETE FROM FileSearchIndexAttributes WHERE fID = ?', array($this->fID));
        $db->Execute('DELETE FROM DownloadStatistics WHERE fID = ?', array($this->fID));
        $db->Execute('DELETE FROM FilePermissionAssignments WHERE fID = ?', array($this->fID));
    }


    /**
     * returns the most recent FileVersion object.
     *
     * @return FileVersion
     */
    public function getRecentVersion()
    {
        $db   = Loader::db();
        $fvID = $db->GetOne('SELECT fvID FROM FileVersions WHERE fID = ? ORDER BY fvID DESC', array($this->fID));

        return $this->getVersion($fvID);
    }

    /**
     * returns the FileVersion object for the provided fvID
     * if none provided returns the approved version.
     *
     * @param int $fvID
     *
     * @return FileVersion
     */
    public function getVersion($fvID = null)
    {
        if ($fvID == null) {
            $fvID = $this->fvID; // approved version
        }
        $fv = CacheLocal::getEntry('file', $this->getFileID() . ':' . $fvID);
        if ($fv === -1) {
            return false;
        }
        if ($fv) {
            return $fv;
        }

        $db                  = Loader::db();
        $row                 = $db->GetRow('SELECT * FROM FileVersions WHERE fvID = ? AND fID = ?', array($fvID, $this->fID));
        $row['fvAuthorName'] = $db->GetOne('SELECT uName FROM Users WHERE uID = ?', array($row['fvAuthorUID']));

        $fv           = new FileVersion();
        $row['fslID'] = $this->fslID;
        $fv->setPropertiesFromArray($row);

        CacheLocal::set('file', $this->getFileID() . ':' . $fvID, $fv);

        return $fv;
    }

    /**
     * Returns an array of all FileVersion objects owned by this file.
     */
    public function getVersionList()
    {
        $db    = Loader::db();
        $r     = $db->Execute('SELECT fvID FROM FileVersions WHERE fID = ? ORDER BY fvDateAdded DESC', array($this->getFileID()));
        $files = array();
        while ($row = $r->FetchRow()) {
            $files[] = $this->getVersion($row['fvID']);
        }

        return $files;
    }

    public function getTotalDownloads()
    {
        $db = Loader::db();

        return $db->GetOne('SELECT count(*) FROM DownloadStatistics WHERE fID = ?', array($this->getFileID()));
    }

    public function getDownloadStatistics($limit = 20)
    {
        $db          = Loader::db();
        $limitString = '';
        if ($limit != false) {
            $limitString = 'limit ' . intval($limit);
        }

        if (is_object($this) && $this instanceof File) {
            return $db->getAll("SELECT * FROM DownloadStatistics WHERE fID = ? ORDER BY timestamp desc {$limitString}", array($this->getFileID()));
        } else {
            return $db->getAll("SELECT * FROM DownloadStatistics ORDER BY timestamp desc {$limitString}");
        }
    }

    /**
     * Tracks File Download, takes the cID of the page that the file was downloaded from.
     *
     * @param int $rcID
     */
    public function trackDownload($rcID = null)
    {
        $u    = new User();
        $uID  = intval($u->getUserID());
        $fv   = $this->getVersion();
        $fvID = $fv->getFileVersionID();
        if (!isset($rcID) || !is_numeric($rcID)) {
            $rcID = 0;
        }
        Events::fire('on_file_download', $fv, $u);
        $db = Loader::db();
        $db->Execute('INSERT INTO DownloadStatistics (fID, fvID, uID, rcID) VALUES (?, ?, ?, ?)', array($this->fID, intval($fvID), $uID, $rcID));
    }
}
