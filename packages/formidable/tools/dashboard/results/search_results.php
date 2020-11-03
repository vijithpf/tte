<?php   
defined('C5_EXECUTE') or die("Access Denied.");

$cnt = Loader::controller('/dashboard/formidable/results');	
$resultsList = $cnt->getRequestedSearchResults();
$f = $cnt->get('f');
$columns = $cnt->get('columns');
$results = $resultsList->getPage();
$pagination = $resultsList->getPagination();

Loader::packageElement('dashboard/results/search_results', 'formidable', array('f' => $f, 'columns' => $columns, 'results' => $results, 'resultsList' => $resultsList, 'pagination' => $pagination));
