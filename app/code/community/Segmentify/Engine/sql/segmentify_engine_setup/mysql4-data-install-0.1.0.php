<?php
$campaigns = array(
    array(
        'campaign' => 'Home Page',
        'segmentify_id' => 'ext_home_rec',
        'selector' => 'seg-home-rec',
        'active' => true,
        'device'=>'PC',
        'test'=>false,
        'items'=>4,
        'updated_at'=>date('Y-m-d H:i:s')
    ),
    array(
        'campaign' => 'Product Page',
        'segmentify_id' => 'ext_product_rec',
        'selector' => 'seg-product-rec',
        'active' => true,
        'device'=>'PC',
        'test'=>false,
        'items'=>4,
        'updated_at'=>date('Y-m-d H:i:s')
    ),
    array(
        'campaign' => 'Category Page',
        'segmentify_id' => 'ext_category_rec',
        'selector' => 'seg-category-rec',
        'active' => true,
        'device'=>'PC',
        'test'=>false,
        'items'=>4,
        'updated_at'=>date('Y-m-d H:i:s')
    ),
    array(
        'campaign' => 'Basket Page',
        'segmentify_id' => 'ext_basket_rec',
        'selector' => 'seg-basket-rec',
        'active' => true,
        'device'=>'PC',
        'test'=>false,
        'items'=>4,
        'updated_at'=>date('Y-m-d H:i:s')
    ),
    array(
        'campaign' => 'Search Page',
        'segmentify_id' => 'ext_search_rec',
        'selector' => 'seg-search-rec',
        'active' => true,
        'device'=>'PC',
        'test'=>false,
        'items'=>4,
        'updated_at'=>date('Y-m-d H:i:s')
    ),
    array(
        'campaign' => 'Error/404 Page',
        'segmentify_id' => 'ext_404_rec',
        'selector' => 'seg-404-rec',
        'active' => true,
        'device'=>'PC',
        'test'=>false,
        'items'=>4,
        'updated_at'=>date('Y-m-d H:i:s')
    ),
);

foreach ($campaigns as $campaign) {
    Mage::getModel('segmentify_engine/campaign')->setData($campaign)->save();
}

$connect = array(
    'apikey' => '',
    'account_id' => '',
    'licence_ts' => '',
    'active' => false,
    'updated_at'=>date('Y-m-d H:i:s')
);

Mage::getModel('segmentify_engine/connect')->setData($connect)->save();





