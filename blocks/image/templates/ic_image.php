<?php
defined('C5_EXECUTE') or die('Access Denied.');

/* @var $fileObj File */

$fullPath = $fileObj->getPath();
$relPath  = $fileObj->getRelativePath();
?>
<div class="block_image full_bg mainImage" style="background-image: url('<?php echo $relPath; ?>')"></div>
