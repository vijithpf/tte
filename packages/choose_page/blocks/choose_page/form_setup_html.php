<?php
defined('C5_EXECUTE') or die("Access Denied.");
/** @var FormPageSelectorHelper $pageSelectorHelper */
$pageSelectorHelper = Loader::helper('form/page_selector');

echo $pageSelectorHelper->selectPage('chosenPageID', $chosenPageID);
