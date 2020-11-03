<?php   
defined('C5_EXECUTE') or die("Access Denied.");
ini_set('memory_limit', -1);
set_time_limit(0);

$cnt = Loader::controller('/dashboard/formidable/results');	
$resultsList = $cnt->getRequestedSearchResults();
$resultsList->setItemsPerPage(0);
$results = $resultsList->getPage();

$f = $cnt->get('f');

Loader::model('formidable/result', 'formidable');
$rs = new FormidableResultsSearchAvailableColumnSet(true);

$date = date('Ymd');

header("Content-Type: application/vnd.ms-excel");
header("Cache-control: private");
header("Pragma: public");
header("Content-Disposition: inline; filename=formidable_report_{$f->title}_{$date}.xls"); 
header("Content-Title: Formidable Report - {$f->title} - Run on {$date}");

if (count($results) > 0) {

	echo '<table>';
	echo '<tr>';
	foreach($rs->getColumns() as $col) { 
		echo '<td>'.$col->getColumnName().'</td>';
	}
	echo '</tr>';
				
	foreach($results as $result) 
	{ 	
		echo '<tr>';
		foreach($rs->getColumns() as $col) {
			if (!is_array($col->getColumnCallback()))
				echo '<td>'.$result[$col->getColumnKey()].'</td>'; 	
			else
				echo '<td>'.$col->getColumnValue($result[$col->getColumnKey()]).'</td>'; 
		}
		echo '</tr>'; 
	}
	echo '</table>';
}

exit;