<?php
defined('C5_EXECUTE') or die('Access Denied.');
$rssUrl = $showRss ? $controller->getRssUrl($b) : '';
$th = Loader::helper('text');
$dh = Loader::helper('date');
$i = 1;
$ih = Loader::helper('image');
//$ih = Loader::helper('image'); //<--uncomment this line if displaying image attributes (see below)
//Note that $nh (navigation helper) is already loaded for us by the controller (for legacy reasons)
?>

  <ul>

		<?php foreach ($pages as $page):
        $activeClass = '';
        if ($page->getCollectionID() == Page::getCurrentPage()->getCollectionID()) { $activeClass = 'active'; } //skip current page

        // Prepare data for each page being listed...
				$title = $th->entities($page->getCollectionName());
        $url = $nh->getLinkToCollection($page);
        $target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
        $target = empty($target) ? '_self' : $target;
        $description = $page->getCollectionDescription();
        $description = $controller->truncateSummaries ? $th->wordSafeShortText($description, $controller->truncateChars) : $description;
        $description = $th->entities($description);
        $date = $dh->date('d F', strtotime($page->getCollectionDatePublic()));
        $year = $dh->date('Y', strtotime($page->getCollectionDatePublic()));
    	?>

      <li class="<?php echo $activeClass; ?>"><a href="<?php echo $url; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a></li>


    <?php endforeach; ?>

  </ul><!-- /ul-->
