<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../WebToPay.php';

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

$request = WebToPay::buildRequest(array_merge(
    $post,
    $config,
    $order
));

$data['orderid'] = $orderid;
$data['orders'][$orderid] = array('item' => $item, 'status' => 'new', 'additionalData' => $post);

save_data($data);

redirect_to(WebToPay::PAY_URL . '?' . http_build_query($request));