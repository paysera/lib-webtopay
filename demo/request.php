<?php

require_once 'helpers.php';
require_once('../WebToPay.php');

$base_url = get_address() . dirname($_SERVER['SCRIPT_NAME']);
$response_url = $base_url . '/response.php';

$post = array();
foreach ($_POST as $key => $val) {
    $val = trim($val);
    if (!empty($val)) {
        $post[$key] = $val;
    }
}

$_SESSION['posted'] = $post;

try {
    $request = WebToPay::buildRequest($post);
}
catch (WebToPayException $e) {
    $_SESSION['error'] = $e->getMessage();
    redirect_to($base_url);
}

save_request_data(array(
        'request'           => $request,
        'sign_password'     => $post['sign_password'],
    ));

$form = array();
$form['title'] = 'Ruquest form';
$form['action'] = WebToPay::PAY_URL;
$form['data'] = $request;

echo template('base.html', array(
        'content' => template('form-preview.html', $form)
    ));

