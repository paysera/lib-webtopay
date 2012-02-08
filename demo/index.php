<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../WebToPay.php';

$base_url = get_address() . dirname($_SERVER['SCRIPT_NAME']);
$response_url = $base_url . '/response.php';

$form = array();
$form['title'] = 'Fill test order form';
$form['action'] = dirname($_SERVER['SCRIPT_NAME']).'/request.php';
$data = array(
    'orderid'       => 1,
    'amount'        => '10000', // 100.00 LTL
    'currency'      => 'LTL',
    'paytext'       => 'Test payment',
    'country'       => 'LT',
    'lang'          => 'LIT',
    'accepturl'     => $response_url.'?answer=accept',
    'cancelurl'     => $response_url.'?answer=cancel',
    'callbackurl'   => $response_url.'?answer=callback',
    'test'          => $config['test'], // turn off test in production
);

foreach (WebToPay::getRequestSpec() as $item) {
    list($key, , , $user_set) = $item;
    if ($user_set && !isset($config[$key])) {
        if (isset($data[$key])) {
            $form['data'][$key] = $data[$key];
        }
        else {
            $form['data'][$key] = '';
        }
    }
}

if (isset($_SESSION['posted'])) {
    foreach (array_keys($form['data']) as $key) {
        if (isset($_SESSION['posted'][$key])) {
            $form['data'][$key] = $_SESSION['posted'][$key];
        }
    }
}

if (isset($_SESSION['error'])) {
    $form['error'] = $_SESSION['error'];
    unset($_SESSION['error']);
}

echo template('base.html', array(
    'content' => template('form.html', $form)
), false);

