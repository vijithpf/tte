<?php
defined('C5_EXECUTE') or die('Access Denied.');

/**
 * An object that represents an option in a survey.
 *
 * @author Ryan Tyler <ryan@concrete5.org>
 * @author Tony Trupp <tony@concrete5.org>
 * @copyright  Copyright (c) 2003-2012 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Controller_Block_SurveyOption
{

    public $optionID, $optionName, $displayOrder;

    public function getOptionID()
    {
        return $this->optionID;
    }

    public function getOptionName()
    {
        return $this->optionName;
    }

    public function getOptionDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function getResults()
    {
        $db     = Loader::db();
        $v      = array($this->optionID, intval($this->cID));
        $q      = 'SELECT count(resultID) FROM btSurveyResults WHERE optionID = ? AND cID=?';
        $result = $db->getOne($q, $v);
        if ($result > 0) {
            return $result;
        } else {
            return 0;
        }
    }
}
