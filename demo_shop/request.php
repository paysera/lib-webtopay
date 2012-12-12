<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../src/includes.php';

$post = removeQuotes($_POST);
$id = $post['id'];
if (!isset($shopItems[$id])) {
    redirect_to(get_address());
}
$item = $shopItems[$id];

$data = load_data();
$orderid = isset($data['orderid']) ? $data['orderid'] + 1 : 1;

$order = array(
    'amount' => $item['price'],
    'currency' => $item['currency'],
    'orderid' => $orderid,
);

$data['orderid'] = $orderid;
$data['orders'][$orderid] = array('item' => $item, 'status' => 'new', 'additionalData' => $post);
save_data($data);

// this method builds request and sends Location header for redirecting to payment site
// as an alternative, you can use WebToPay::buildRequest and make auto-post form
WebToPay::redirectToPayment(array_merge(
    $post,
    $config,
    $order
));