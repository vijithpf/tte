<?php     defined('C5_EXECUTE') or die("Access Denied.");
	
$date = Loader::helper('date');
	
$c = Page::getCurrentPage();

if($c instanceof Page) {
	$cID = $c->getCollectionID();
}

$files = $controller->getFileSet();
?>	


<?php   if ($displaySetTitle && $filesetname = $controller->getFileSetName()) { ?>
<h3><?php   echo $filesetname; ?></h3>
<?php   } ?>
	
<?php   if (!empty($files)) { ?>	
<ul  class="fileset-list">

	
	<?php  	
	foreach($files as $f) {
		$fp = new Permissions($f);
		
		if ($fp->canViewFile()) {  
			$fv = $f->getApprovedVersion();
			
			// although the 'title' for a file is used for display,
			// the filename is retreived here so we can always get a file extension
			$filename = $fv->getFileName();
			$ext =  pathinfo($filename, PATHINFO_EXTENSION);
			$url = View::url('/download_file', $f->getFileID(),$cID);
	 	
			// if you wish to directly link to the file, bypassing permissons, logging, etc,
			// use instead of the above line:  $url = $fv->getURL();
			
			// if we are overriding the filename (e.g. showing only 1 file)
			if ($titleOverride) {
				$title = $titleOverride;
			} else {
			
				// get the title of the file. This default to the filename on uploading, but can be changed 
				// through the file manager.
				
				$title = $f->getTitle();
				
				// want to always use the filename and not the title?  uncomment line below
				// $title = $filename;
				
				// removes or puts in brackets the file extension
				if ($extension == 'hide') {
					
					if(strlen($title) - strlen($ext) == strrpos($title,$ext)) {
						$title = pathinfo($title, PATHINFO_FILENAME);
					}
					
				} elseif ($extension == 'brackets') {
					
					if(strlen($title) - strlen($ext) == strrpos($title,$ext)) {
						$title = pathinfo($title, PATHINFO_FILENAME);
					}
					
					if ($ext) {
						$title .= ' (' . $ext . ')';
					}
				}
				
				if ($replaceUnderscores) {
					 $title = str_replace('_', ' ', $title);
				}  
				
				if ($uppercaseFirst) {
					$title = ucfirst(strtolower($title));
				}
				
				if ($displaySize) {
			 		$title .= ' - ' . $fv->getSize();
				}
				
				if ($displayDateAdded) {
					// DATE_APP_GENERIC_MDY is simply a built in constant for a date format string
					$title .= ' - ' . $date->getLocalDateTime($fv->getDateAdded(), DATE_APP_GENERIC_MDY);
			 	}
			 
			 	// if you want to add more information about a file (e.g. description, download count)
			 	// look up in the API the functions for a File object and FileVersion object ($f and $fv in above code)
			}
				
			?>
		 
			
			<li><a href="<?php   echo $url; ?>">
			<?php   echo $title; ?>
			</a></li>
			
	<?php   } ?>

<?php   }	?>

</ul>
<?php   }	?>

<?php   if (empty($files) && $noFilesMessage) { ?>
<p><?php   echo $noFilesMessage; ?></p>
<?php   } ?>
