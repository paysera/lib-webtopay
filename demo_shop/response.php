<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../WebToPay.php';

$get = removeQuotes($_GET);
$answer = isset($get['answer']) ? $get['answer'] : 'cancel';

if ('callback' == $answer) {
    try {
        $response = WebToPay::getPrefixed($get, WebToPay::PREFIX);
        $orderId = isset($response['orderid']) ? $response['orderid'] : null;

        $data = load_data();
        if ($orderId == '' || !isset($data['orders'][$orderId])) {
            throw new Exception('Order with this ID not found');
        }
        $order = $data['orders'][$orderId];

        if ($order['status'] === 'done') {
            echo 'OK';
        } else {
            $response = WebToPay::checkResponse($get, array(
                'projectid'     => $config['projectid'],
                'sign_password' => $config['sign_password'],
            ));
            if (
                $response['test'] != $config['test']
                || $response['amount'] != $order['item']['price']
                || $response['currency'] != $order['item']['currency']
                || $response['status'] != 1
            ) {
                throw new Exception('Some values are not as expected');
            }

            $order['status'] = 'done';

            $data['orders'][$orderId] = $order;
            save_data($data);

            echo 'OK';
        }
    } catch (Exception $e) {
        echo 'FAIL ' . $e->getMessage();
    }
} else if ('accept' == $answer) {
    echo template('Thank you for buying<br /><a href="' . get_address('orders.php') . '">Orders</a>');
} else {
    echo template('<div class="error">Payment rejected.</div>');
}

