<?php   

defined('C5_EXECUTE') or die("Access Denied.");

//date_default_timezone_set('Europe/Amsterdam');

class RemoveTempSubmissions extends Job {

	public function getJobName() {
		return t('Formidable cleanup');
	}
	
	public function getJobDescription() {
		return t("Removes temporary content and files.");
	}
	
	function run() {
	
		$found = $removed = 0;
		
		Loader::model('formidable/result', 'formidable');
		
		$db = Loader::db();
		$tmp_results = $db->getAll("SELECT answerSetID 
									FROM FormidableAnswerSets
									WHERE temp = 1
									AND submitted < DATE_SUB(NOW(), INTERVAL 1 DAY)");
		if (sizeof($tmp_results)){
			$found = sizeof($tmp_results);
			foreach ($tmp_results as $result) {
				$fr = new FormidableResult($result['answerSetID']);
				$fr->delete();
				$removed++;
			}	
		}
		
		
		$db = Loader::db();
		// Clean up answers which aren't properly deleted...
		$records = $db->getOne("SELECT COUNT(answerSetID)
							    FROM FormidableAnswers 
							    WHERE answerSetID NOT IN ( SELECT answerSetID 
														   FROM FormidableAnswerSets 
														   WHERE answerSetID IS NOT NULL )
								OR formID NOT IN ( SELECT formID 
												   FROM FormidableForms 
												   WHERE formID IS NOT NULL )");
			
		$delete = $db->execute("DELETE FROM FormidableAnswers 
								WHERE answerSetID NOT IN ( SELECT answerSetID 
														   FROM FormidableAnswerSets 
														   WHERE answerSetID IS NOT NULL )
								OR formID NOT IN ( SELECT formID 
												   FROM FormidableForms 
												   WHERE formID IS NOT NULL )");	
		
		// Clean up answerssets which aren't properly deleted...
		$records_as = $db->getOne("SELECT COUNT(answerSetID)
							       FROM FormidableAnswerSets 
							  	   WHERE formID NOT IN ( SELECT formID 
												         FROM FormidableForms 
												         WHERE formID IS NOT NULL )");
				
		$delete_as = $db->execute("DELETE FROM FormidableAnswerSets 
							  	  WHERE formID NOT IN ( SELECT formID 
												        FROM FormidableForms 
												        WHERE formID IS NOT NULL )");		
		// Clean up files!
		$fe = 0;
		$file = Loader::helper('file');		
		$files = $file->getDirectoryContents($file->getTemporaryDirectory().'/formidable', array(), true);
		if (sizeof($files) > 0) {
			foreach($files as $file) {
				if (is_dir($file)) {
					if (@rmdir($file))
						$fe++;
				} else {
					if (@unlink($file))
						$fe++;
				}
			}
		}
		
		return t('%s temp submissions deleted, %s temp answers deleted, %s temp files deleted', $removed, $records+$records_as, $fe);
		
	}

}

?>