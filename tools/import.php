<?php
exit();
// TODO Delete this file on production
defined('C5_EXECUTE') or die("Access Denied.");

Loader::model('file');

$filePath = BASE_URL . DIR_REL . '/tools/products1.csv';


$file = fopen($filePath, "r");
while (!feof($file)) {

    $row = 1;

    while (($product = (fgetcsv($file, 1000, ','))) !== false) {

        if ($row == 1) {
            $row++;
            continue;
        }

        $filters         = [];
        $pageDescription = null;

        $parentID  = (int) trim($product[0]);
        $pageTitle = trim($product[1]);
        $filter    = trim($product[2]);
        if ($filter) {
            $filters = explode(',', $filter);
        }
        $brand               = trim($product[3]);
        $imageTitle          = trim($product[4]);
        $pageDescriptionTemp = trim($product[5]);

        $newLines = substr_count($pageDescriptionTemp, "\n") + 1;

        if ($newLines <= 1) {
            $pageDescription = $pageDescriptionTemp;
        } else {
            $finalList = '<ul>';
            $lists     = explode("\n", $pageDescriptionTemp);

            foreach ($lists as $list) {
                $finalList .= '<li>' . $list . '</li>';
            }

            $finalList .= '</ul>';
        }


        $parent = Page::getByID($parentID);
        $ct     = CollectionType::getByHandle('product_detail');
        $data   = array(
            'cName'        => $pageTitle,
            'cDescription' => $pageDescription,
        );
        $page   = $parent->add($ct, $data);

        $page->setAttribute('brands', $brand);
        $page->setAttribute('product_description', $finalList);
        $page->setAttribute('product_filters', $filters);
        $page->setAttribute('thumbnail_image', File::getByTitle($imageTitle));
        $page->reindex();

        echo $pageTitle . ' ' . $finalList . '<br>';
    }

}