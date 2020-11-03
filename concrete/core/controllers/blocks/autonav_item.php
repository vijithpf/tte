<?php
defined('C5_EXECUTE') or die('Access Denied.');

/**
 * An object used by the Autonav Block to display navigation items in a tree.
 *
 * @author Andrew Embler <andrew@concrete5.org>
 * @copyright  Copyright (c) 2003-2012 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Controller_Block_AutonavItem
{

    protected $level;
    protected $isActive    = false;
    protected $_c;
    public    $hasChildren = false;

    /**
     * Instantiates an Autonav Block Item.
     *
     * @param array $itemInfo
     * @param int $level
     */
    public function __construct($itemInfo, $level = 1)
    {
        $this->level = $level;
        if (is_array($itemInfo)) {
            // this is an array pulled from a separate SQL query
            foreach ($itemInfo as $key => $value) {
                $this->{$key} = $value;
            }
        }

        return $this;
    }

    /**
     * Returns the number of children below this current nav item.
     *
     * @return int
     */
    public function hasChildren()
    {
        return $this->hasChildren;
    }

    /**
     * Determines whether this nav item is the current page the user is on.
     *
     * @param Page $page The page object for the current page
     *
     * @return bool
     */
    public function isActive(&$c)
    {
        if ($c) {
            $cID = ($c->getCollectionPointerID() > 0) ? $c->getCollectionPointerOriginalID() : $c->getCollectionID();

            return ($cID == $this->cID);
        }
    }

    /**
     * Returns the description of the current navigation item (typically grabbed from the page's short description field).
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->cvDescription;
    }

    /**
     * Returns a target for the nav item.
     */
    public function getTarget()
    {
        if ($this->cPointerExternalLink != '') {
            if ($this->cPointerExternalLinkNewWindow) {
                return '_blank';
            }
        }

        $_c = $this->getCollectionObject();
        if (is_object($_c)) {
            return $_c->getAttribute('nav_target');
        }

        return '';
    }

    /**
     * Gets a URL that will take the user to this particular page. Checks against URL_REWRITING, the page's path, etc..
     *
     * @return string $url
     */
    public function getURL()
    {
        $dispatcher = '';
        if (!URL_REWRITING) {
            $dispatcher = '/' . DISPATCHER_FILENAME;
        }
        if ($this->cPointerExternalLink != '') {
            $link = $this->cPointerExternalLink;
        } elseif ($this->cPath) {
            $link = DIR_REL . $dispatcher . $this->cPath . '/';
        } elseif ($this->cID == HOME_CID) {
            $link = DIR_REL . '/';
        } else {
            $link = DIR_REL . '/' . DISPATCHER_FILENAME . '?cID=' . $this->cID;
        }

        return $link;
    }

    /**
     * Gets the name of the page or link.
     *
     * @return string
     */
    public function getName()
    {
        return $this->cvName;
    }

    /**
     * Gets the pageID for the navigation item.
     *
     * @return int
     */
    public function getCollectionID()
    {
        return $this->cID;
    }


    /**
     * Gets the current level at the nav tree that we're at.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Sets the collection Object of the navigation item to the passed object.
     *
     * @param Page $obj
     */
    public function setCollectionObject(&$obj)
    {
        $this->_c = $obj;
    }

    /**
     * Gets the collection Object of the navigation item.
     *
     * @return Page
     */
    public function getCollectionObject()
    {
        return $this->_c;
    }
}
