<?php
defined('C5_EXECUTE') or die('Access Denied.');

/* @var $fileObj File */

$fullPath = $fileObj->getPath();
$relPath  = $fileObj->getRelativePath();
?>
<div class="usp-image full_bg" style="background-image: url('<?php echo $relPath; ?>');"></div>