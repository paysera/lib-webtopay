<?php

require_once 'helpers.php';

$form = array();
$form['title'] = 'Fill test order form';
$form['action'] = dirname($_SERVER['SCRIPT_NAME']).'/request.php';
$form['data'] = array(
        'projectid'    => 1,
        'orderid'       => 1,
        'amount'        => '10000', // 100.00 LTL
        'currency'      => 'LTL',
        'paytext'       => 'Test payment',
        'country'       => 'lt',
        'sign_password' => 'secret',
        'account_password' => '',
    );

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

