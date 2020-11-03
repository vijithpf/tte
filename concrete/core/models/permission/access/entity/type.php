<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_PermissionAccessEntityType extends Object
{

    public function getAccessEntityTypeID()
    {
        return $this->petID;
    }

    public function getAccessEntityTypeHandle()
    {
        return $this->petHandle;
    }

    public function getAccessEntityTypeName()
    {
        return $this->petName;
    }

    public function getAccessEntityTypeClass()
    {
        $class = Loader::helper('text')->camelcase($this->petHandle) . 'PermissionAccessEntity';

        return $class;
    }

    /** Returns the display name for this access entity type (localized and escaped accordingly to $format)
     * @param string $format = 'html'
     *                       Escape the result in html format (if $format is 'html').
     *                       If $format is 'text' or any other value, the display name won't be escaped.
     *
     * @return string
     */
    public function getAccessEntityTypeDisplayName($format = 'html')
    {
        $value = tc('PermissionAccessEntityTypeName', $this->getAccessEntityTypeName());
        switch ($format) {
            case 'html':
                return h($value);
            case 'text':
            default:
                return $value;
        }
    }

    public static function getByID($petID)
    {
        $db  = Loader::db();
        $row = $db->GetRow('SELECT petID, pkgID, petHandle, petName FROM PermissionAccessEntityTypes WHERE petID = ?', array($petID));
        if ($row['petHandle']) {
            $wt = new PermissionAccessEntityType();
            $wt->setPropertiesFromArray($row);

            return $wt;
        }
    }

    public function __call($method, $args)
    {
        $obj = $this->getAccessEntityTypeClass();
        $o   = new $obj();

        return call_user_func_array(array($obj, $method), $args);
    }

    public function getAccessEntityTypeToolsURL($task = false)
    {
        if (!$task) {
            $task = 'process';
        }
        $uh    = Loader::helper('concrete/urls');
        $url   = $uh->getToolsURL('permissions/access/entity/types/' . $this->petHandle, $this->getPackageHandle());
        $token = Loader::helper('validation/token')->getParameter($task);
        $url .= '?' . $token . '&task=' . $task;

        return $url;
    }

    public static function getList($category = false)
    {
        $db   = Loader::db();
        $list = array();
        if ($category instanceof PermissionKeyCategory) {
            $r = $db->Execute('SELECT pet.petID FROM PermissionAccessEntityTypes pet INNER JOIN PermissionAccessEntityTypeCategories petc ON pet.petID = petc.petID WHERE petc.pkCategoryID = ? ORDER BY pet.petID ASC', array($category->getPermissionKeyCategoryID()));
        } else {
            $r = $db->Execute('SELECT petID FROM PermissionAccessEntityTypes ORDER BY petID ASC');
        }

        while ($row = $r->FetchRow()) {
            $list[] = PermissionAccessEntityType::getByID($row['petID']);
        }

        $r->Close();

        return $list;
    }

    public function getPackageID()
    {
        return $this->pkgID;
    }

    public function getPackageHandle()
    {
        return PackageList::getHandle($this->pkgID);
    }

    public static function exportList($xml)
    {
        $ptypes = PermissionAccessEntityType::getList();
        $db     = Loader::db();
        $axml   = $xml->addChild('permissionaccessentitytypes');
        foreach ($ptypes as $pt) {
            $ptype = $axml->addChild('permissionaccessentitytype');
            $ptype->addAttribute('handle', $pt->getAccessEntityTypeHandle());
            $ptype->addAttribute('name', $pt->getAccessEntityTypeName());
            $ptype->addAttribute('package', $pt->getPackageHandle());
            $categories = $db->GetCol('SELECT pkCategoryHandle FROM PermissionKeyCategories INNER JOIN PermissionAccessEntityTypeCategories WHERE PermissionKeyCategories.pkCategoryID = PermissionAccessEntityTypeCategories.pkCategoryID AND PermissionAccessEntityTypeCategories.petID = ?', array($pt->getAccessEntityTypeID()));
            if (count($categories) > 0) {
                $cat = $ptype->addChild('categories');
                foreach ($categories as $catHandle) {
                    $cat->addChild('category')->addAttribute('handle', $catHandle);
                }
            }
        }
    }

    public function delete()
    {
        $db = Loader::db();
        $db->Execute('DELETE FROM PermissionAccessEntityTypes WHERE petID = ?', array($this->petID));
    }

    public static function getListByPackage($pkg)
    {
        $db   = Loader::db();
        $list = array();
        $r    = $db->Execute('SELECT petID FROM PermissionAccessEntityTypes WHERE pkgID = ? ORDER BY petID ASC', array($pkg->getPackageID()));
        while ($row = $r->FetchRow()) {
            $list[] = PermissionAccessEntityType::getByID($row['petID']);
        }
        $r->Close();

        return $list;
    }

    public static function getByHandle($petHandle)
    {
        $db    = Loader::db();
        $petID = $db->GetOne('SELECT petID FROM PermissionAccessEntityTypes WHERE petHandle = ?', array($petHandle));
        if ($petID > 0) {
            return self::getByID($petID);
        }
    }

    public static function add($petHandle, $petName, $pkg = false)
    {
        $pkgID = 0;
        if (is_object($pkg)) {
            $pkgID = $pkg->getPackageID();
        }
        $db = Loader::db();
        $db->Execute('INSERT INTO PermissionAccessEntityTypes (petHandle, petName, pkgID) VALUES (?, ?, ?)', array($petHandle, $petName, $pkgID));
        $id  = $db->Insert_ID();
        $est = PermissionAccessEntityType::getByID($id);

        return $est;
    }
}
