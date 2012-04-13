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

$amount = $item['price'];
$currency = $item['currency'];

                // get payment methods available for this project with min/max amounts in specified currency
$methods = WebToPay::getPaymentMethodList($config['projectid'], $currency)
    ->filterForAmount($amount, $currency)    // filter: leave only those, which are available for this sum
    ->setDefaultLanguage('en');              // set default language for titles (default: lt)


echo template('paymentMethod.html', array(
    'methods' => $methods,
    'post' => $post,
));
