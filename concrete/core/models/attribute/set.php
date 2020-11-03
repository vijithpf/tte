<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_AttributeSet extends Object
{

    public static function getByID($asID)
    {
        $db  = Loader::db();
        $row = $db->GetRow('SELECT asID, asHandle, pkgID, asName, akCategoryID, asIsLocked  FROM AttributeSets WHERE asID = ?', array($asID));
        if (isset($row['asID'])) {
            if (function_exists('get_called_class')) {
                $class = get_called_class(); // using this for an add-on that requires 5.3
            } else {
                $class = 'AttributeSet';
            }
            $akc = new $class();
            $akc->setPropertiesFromArray($row);

            return $akc;
        }
    }

    public static function getByHandle($asHandle)
    {
        $db  = Loader::db();
        $row = $db->GetRow('SELECT asID, asHandle, pkgID, asName, akCategoryID, asIsLocked FROM AttributeSets WHERE asHandle = ?', array($asHandle));
        if (isset($row['asID'])) {
            if (function_exists('get_called_class')) {
                $class = get_called_class(); // using this for an add-on that requires 5.3
            } else {
                $class = 'AttributeSet';
            }
            $akc = new $class();
            $akc->setPropertiesFromArray($row);

            return $akc;
        }
    }

    public static function getListByPackage($pkg)
    {
        $db   = Loader::db();
        $list = array();
        $r    = $db->Execute('SELECT asID FROM AttributeSets WHERE pkgID = ? ORDER BY asID ASC', array($pkg->getPackageID()));
        while ($row = $r->FetchRow()) {
            $list[] = AttributeSet::getByID($row['asID']);
        }
        $r->Close();

        return $list;
    }

    public function getAttributeSetID()
    {
        return $this->asID;
    }

    public function getAttributeSetHandle()
    {
        return $this->asHandle;
    }

    public function getAttributeSetName()
    {
        return $this->asName;
    }

    public function getPackageID()
    {
        return $this->pkgID;
    }

    public function getPackageHandle()
    {
        return PackageList::getHandle($this->pkgID);
    }

    public function getAttributeSetKeyCategoryID()
    {
        return $this->akCategoryID;
    }

    public function isAttributeSetLocked()
    {
        return $this->asIsLocked;
    }

    /** Returns the display name for this attribute set (localized and escaped accordingly to $format)
     * @param string $format = 'html'
     *                       Escape the result in html format (if $format is 'html').
     *                       If $format is 'text' or any other value, the display name won't be escaped.
     *
     * @return string
     */
    public function getAttributeSetDisplayName($format = 'html')
    {
        $value = tc('AttributeSetName', $this->getAttributeSetName());
        switch ($format) {
            case 'html':
                return h($value);
            case 'text':
            default:
                return $value;
        }
    }

    public function updateAttributeSetName($asName)
    {
        $this->asName = $asName;
        $db           = Loader::db();
        $db->Execute('UPDATE AttributeSets SET asName = ? WHERE asID = ?', array($asName, $this->asID));
    }

    public function updateAttributeSetHandle($asHandle)
    {
        $this->asHandle = $asHandle;
        $db             = Loader::db();
        $db->Execute('UPDATE AttributeSets SET asHandle = ? WHERE asID = ?', array($asHandle, $this->asID));
    }

    public function addKey($ak)
    {
        $db = Loader::db();
        $no = $db->GetOne('SELECT count(akID) FROM AttributeSetKeys WHERE akID = ? AND asID = ?', array($ak->getAttributeKeyID(), $this->getAttributeSetID()));
        if ($no < 1) {
            $do = $db->GetOne('SELECT max(displayOrder) FROM AttributeSetKeys WHERE asID = ?', $this->getAttributeSetID());
            $do++;
            $db->Execute('INSERT INTO AttributeSetKeys (asID, akID, displayOrder) VALUES (?, ?, ?)', array($this->getAttributeSetID(), $ak->getAttributeKeyID(), $do));
        }
    }

    public function clearAttributeKeys()
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM AttributeSetKeys WHERE asID = ?', array($this->asID));
    }

    public function export($axml)
    {
        $category = AttributeKeyCategory::getByID($this->getAttributeSetKeyCategoryID())->getAttributeKeyCategoryHandle();
        $akey     = $axml->addChild('attributeset');
        $akey->addAttribute('handle', $this->getAttributeSetHandle());
        $akey->addAttribute('name', $this->getAttributeSetName());
        $akey->addAttribute('package', $this->getPackageHandle());
        $akey->addAttribute('locked', $this->isAttributeSetLocked());
        $akey->addAttribute('category', $category);
        $keys = $this->getAttributeKeys();
        foreach ($keys as $ak) {
            $ak->export($akey, false);
        }

        return $akey;
    }

    public static function exportList($xml)
    {
        $axml = $xml->addChild('attributesets');
        $db   = Loader::db();
        $r    = $db->Execute('SELECT asID FROM AttributeSets ORDER BY asID ASC');
        $list = array();
        while ($row = $r->FetchRow()) {
            $list[] = AttributeSet::getByID($row['asID']);
        }
        foreach ($list as $as) {
            $as->export($axml);
        }
    }

    public function getAttributeKeys()
    {
        $db   = Loader::db();
        $r    = $db->Execute('SELECT akID FROM AttributeSetKeys WHERE asID = ? ORDER BY displayOrder ASC', $this->getAttributeSetID());
        $keys = array();
        $cat  = AttributeKeyCategory::getByID($this->akCategoryID);
        while ($row = $r->FetchRow()) {
            $ak = $cat->getAttributeKeyByID($row['akID']);
            if (is_object($ak)) {
                $keys[] = $ak;
            }
        }

        return $keys;
    }

    public function contains($ak)
    {
        $db = Loader::db();
        $r  = $db->GetOne('SELECT count(akID) FROM AttributeSetKeys WHERE asID = ? AND akID = ?', array($this->getAttributeSetID(), $ak->getAttributeKeyID()));

        return $r > 0;
    }

    /**
     * Removes an attribute set and sets all keys within to have a set ID of 0.
     */
    public function delete()
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM AttributeSets WHERE asID = ?', array($this->getAttributeSetID()));
        $db->Execute('DELETE FROM AttributeSetKeys WHERE asID = ?', array($this->getAttributeSetID()));
    }

    public function deleteKey($ak)
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM AttributeSetKeys WHERE asID = ? AND akID = ?', array($this->getAttributeSetID(), $ak->getAttributeKeyID()));
        $this->rescanDisplayOrder();
    }

    protected function rescanDisplayOrder()
    {
        $db = Loader::db();
        $do = 1;
        $r  = $db->Execute('SELECT akID FROM AttributeSetKeys WHERE asID = ? ORDER BY displayOrder ASC', $this->getAttributeSetID());
        while ($row = $r->FetchRow()) {
            $db->Execute('UPDATE AttributeSetKeys SET displayOrder = ? WHERE akID = ? AND asID = ?', array($do, $row['akID'], $this->getAttributeSetID()));
            $do++;
        }
    }

    public function updateAttributesDisplayOrder($uats)
    {
        $db = Loader::db();
        for ($i = 0; $i < count($uats); $i++) {
            $v = array($this->getAttributeSetID(), $uats[$i]);
            $db->query("update AttributeSetKeys set displayOrder = {$i} where asID = ? and akID = ?", $v);
        }
    }
}
