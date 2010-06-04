<?php

require_once 'helpers.php';
require_once('../WebToPay.php');

$base_url = get_address() . dirname($_SERVER['SCRIPT_NAME']);
$response_url = $base_url . '/response.php';

$form = array();
$form['title'] = 'Fill test repeat order form';
$form['action'] = dirname($_SERVER['SCRIPT_NAME']).'/request.php?repeat=1';
$data = array(
        'projectid'     => 1,
        'orderid'       => 1,
        'sign_password' => 'secret',
    );

foreach (WebToPay::getRepeatRequestSpec() as $item) {
    list($key, , , $user_set) = $item;
    if ($user_set) {
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
    ));

