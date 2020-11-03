<?php
defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Returns all surveys in a site.
 *
 * @author Ryan Tyler <ryan@concrete5.org>
 * @author Andrew Embler <andrew@concrete5.org>
 * @author Tony Trupp <tony@concrete5.org>
 * @copyright  Copyright (c) 2003-2012 Concrete5. (http://www.concrete5.org)
 * @license    http://www.concrete5.org/license/     MIT License
 */
class Concrete5_Controller_Block_SurveyList extends DatabaseItemList
{
    protected $itemsPerPage    = 10;
    protected $autoSortColumns = array('cvName', 'question', 'numberOfResponses', 'lastResponse');

    public function __construct()
    {
        $this->setQuery(
            'SELECT DISTINCT btSurvey.bID, CollectionVersions.cID, btSurvey.question, CollectionVersions.cvName, (SELECT max(timestamp) FROM btSurveyResults WHERE btSurveyResults.bID = btSurvey.bID AND btSurveyResults.cID = CollectionVersions.cID) AS lastResponse, (SELECT count(timestamp) FROM btSurveyResults WHERE btSurveyResults.bID = btSurvey.bID AND btSurveyResults.cID = CollectionVersions.cID) AS numberOfResponses ' .
            'FROM btSurvey, CollectionVersions, CollectionVersionBlocks');
        $this->filter(false, 'btSurvey.bID = CollectionVersionBlocks.bID');
        $this->filter(false, 'CollectionVersions.cID = CollectionVersionBlocks.cID');
        $this->filter(false, 'CollectionVersionBlocks.cvID = CollectionVersionBlocks.cvID');
        $this->filter(false, 'CollectionVersions.cvIsApproved = 1');
        $this->userPostQuery .= 'group by btSurvey.bID, CollectionVersions.cID';
    }
}
