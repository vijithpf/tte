<?php
/* @var View $this */
/* @var Page $c */
defined('C5_EXECUTE') or die(_('Access Denied.'));

$page = Page::getCurrentPage();
/** @var FormHelper $form */
$form = Loader::helper('form');

//Theses Are For Options
$pageList = new PageList();
$pageList->filterByCollectionTypeHandle('product_detail');
$pageList->filterByParentID($page->getCollectionID());
$products = $pageList->get();
$filters  = [];
$brands   = [];

$filters=[];
$brands=[];

/** @var Page $product */
foreach ($products as $product) {

    $brand      = (string) $product->getAttribute('brands');
    $filterTemp = explode(',', (string) $product->getAttribute('product_filters'));
    foreach ($filterTemp as $filter) {
        if ($filter) {
            $filters[$filter] = trim($filter);
        }
    }
    if($brand) {
        $brands[$brand] = $brand;
    }
}
asort($filters);
array_unshift($filters, "All Filters");

asort($brands);
array_unshift($brands, "All Brands");
?>
<main class="site-body">
    <div class="container-fluid">

        <?php $this->inc('includes/trading_banner.php'); ?>

        <?php $this->inc('includes/introduction.php'); ?>

        <div class="container text-center">

            <form method="get" id="filters_form">
                <div class="business-filter">
                    <div class="filter-item">
                        <span>Filter by</span>
                    </div>
                    <div class="filter-item all-brands-filter">
                        <span><?php echo $form->select('brand', $brands); ?></span>
                    </div>
                    <div class="filter-item all-brands-filter">
                        <span><?php echo $form->select('filter', $filters) ?></span>
                    </div>
                    <div class="item-search filter-search">
                        <?php echo $form->text('keywords', '', ['placeholder' => 'Search Products']); ?>
                        <input type="submit">
                    </div>
                </div>
            </form>
        </div>

        <?php
        $stack = Stack::getByName('Products Page List');
        $stack->display();
        ?>

    </div><!-- /.container-fluid -->
</main><!-- /.site-body -->

<div id="productModal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-body make-enquery-wrap">
            <h2>Product Enquiry</h2>
            <?php $stack = Stack::getByName('Product Enquiry Form');
            $stack->display(); ?>
        </div>
        <button class="fa fa-times close-modal close-enquiry-modal" data-dismiss="modal"></button>
    </div>
</div>
