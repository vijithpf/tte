<?php
/* @var BlockView $this */
/* @var string $bID */
/* @var int $chosenPageID */
/* @var FormHelper $form */
/* @var Page $page */
defined('C5_EXECUTE') or die("Access Denied.");
/** @var TextHelper $th */
$th = Loader::helper('text');

$chosenPage = Page::getByID($chosenPageID);
if (is_null($chosenPage->getCollectionID())) {
    return;
}
?>

<p><?php echo $th->entities($chosenPage->getCollectionName()); ?></p>
