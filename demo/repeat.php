<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../WebToPay.php';

$base_url = get_address() . dirname($_SERVER['SCRIPT_NAME']);
$response_url = $base_url . '/response.php';

$form = array();
$form['title'] = 'Fill test repeat order form';
$form['action'] = dirname($_SERVER['SCRIPT_NAME']).'/request.php?repeat=1';
$data = array(
    'orderid' => 1,
);

foreach (WebToPay::getRepeatRequestSpec() as $item) {
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

