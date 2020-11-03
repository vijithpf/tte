<?php  

defined('C5_EXECUTE') or die("Access Denied.");
//Loader::model('file');
//Loader::model('file_version');
//Loader::library('file/importer');

class FormidableFileImporterLibrary extends Concrete5_Library_FileImporter {
	
	protected function storeFile($prefix, $pointer, $filename, $fr = false) {
		$fi = Loader::helper('concrete/file');		
		$path = $fi->mapSystemPath(NULL, $filename, true, $fr);		
		if (!is_dir($fr)) { 
			@mkdir($fr, DIRECTORY_PERMISSIONS_MODE, TRUE); 
			@chmod($fr, DIRECTORY_PERMISSIONS_MODE); 
			@touch($fr . '/index.html');
		} 
		$r = @copy($pointer, $path);
		@chmod($path, FILE_PERMISSIONS_MODE);
		return $r;
	}

	
	public function import($pointer, $filename = false, $fr = false) {
		
		if ($filename == false) 
			$filename = basename($pointer);
		
		$fh = Loader::helper('validation/file');
		$fi = Loader::helper('file');
		$filename = $fi->sanitize($filename);
		
		if (!$fh->file($pointer)) 
			return FileImporter::E_FILE_INVALID;
		
		if (!$fh->extension($filename)) 
			return FileImporter::E_FILE_INVALID_EXTENSION;

		$response = $this->storeFile($prefix, $pointer, $filename, $fr);
		if (!$response) 
			return FileImporter::E_FILE_UNABLE_TO_STORE;
		
		return array('error' => false,
					 'file' => $filename,
					 'dir' => $fr);
	}
	
	public function move_file($old_path, $new_file, $new_dir) {
		
		if (!is_dir($new_dir)) { 
			@mkdir($new_dir, DIRECTORY_PERMISSIONS_MODE, TRUE); 
			@chmod($new_dir, DIRECTORY_PERMISSIONS_MODE); 
			@touch($new_dir . '/index.html');
		} 

		$r = @rename($old_path, $new_dir.'/'.$new_file);
		@chmod($new_dir.'/'.$new_file, FILE_PERMISSIONS_MODE);

		return $r;	
	}
}
