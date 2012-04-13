<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../src/includes.php';

$get = removeQuotes($_GET);
$answer = isset($get['answer']) ? $get['answer'] : 'cancel';

if ('callback' == $answer) {
    try {
        $response = WebToPay::validateAndParseData($get, $config['projectid'], $config['sign_password']);
        $orderId = isset($response['orderid']) ? $response['orderid'] : null;

        $data = load_data();
        if ($orderId == '' || !isset($data['orders'][$orderId])) {
            throw new Exception('Order with this ID not found');
        }
        $order = $data['orders'][$orderId];

        if ($response['status'] == 1) {
            if ($order['status'] === 'done') {
                echo 'OK';
            } else {
                if (
                    $response['test'] != $config['test']
                    || $response['amount'] != $order['item']['price']
                    || $response['currency'] != $order['item']['currency']
                    || $response['status'] != 1
                ) {
                    throw new Exception('Some values are not as expected');
                }

                $order['response'] = $response;
                $order['status'] = 'done';

                $data['orders'][$orderId] = $order;
                save_data($data);

                echo 'OK';
            }
        } elseif ($response['status'] == 3) {
            $data['orders'][$orderId]['additionalResponse'] = $response;
            save_data($data);
        }
    } catch (Exception $e) {
        echo 'FAIL ' . $e->getMessage();
    }
} else if ('accept' == $answer) {
    try {
        $response = WebToPay::validateAndParseData($get, $config['projectid'], $config['sign_password']);
        if ($response['status'] == 1 || $response['status'] == 2) {
            // You can start providing services when you get confirmation with accept url
            // Be sure to check if this order is not yet confirmed - user can refresh page anytime
            // status 2 means that payment has been got but it's not yet confirmed
            // @todo: get order by $response['orderid'], validate test (!), amount and currency
            echo 'Your payment has been got successfuly, it will be confirmed shortly<br />';
        }
    } catch (Exception $e) {
        echo 'Your payment is not yet confirmed, system error<br />';
    }
    echo template('Thank you for buying<br /><a href="' . get_address('orders.php') . '">Orders</a>');
} else {
    echo template('<div class="error">Payment rejected.</div>');
}

