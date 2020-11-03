<?php
defined('C5_EXECUTE') or die('Access Denied.');

class Concrete5_Model_GroupSetList extends DatabaseItemList
{

    public function __construct()
    {
        $this->setQuery('SELECT gsID FROM GroupSets');
        $this->sortBy('gsName', 'asc');
    }

    public function get()
    {
        $r         = parent::get(0, 0);
        $groupsets = array();
        foreach ($r as $row) {
            $groupsets[] = GroupSet::getByID($row['gsID']);
        }

        return $groupsets;
    }
}
