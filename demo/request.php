<?php

require_once 'helpers.php';
require_once('../WebToPay.php');

$base_url = get_address() . dirname($_SERVER['SCRIPT_NAME']);
$response_url = $base_url . '/response.php';

$_SESSION['posted'] = $_POST;

try {
    $request = WebToPay::buildRequest(array_merge(array(
            'accepturl'     => $response_url.'?answer=accept',
            'cancelurl'     => $response_url.'?answer=cancel',
            'callbackurl'   => $response_url.'?answer=callback',
            'test'          => 1,
        ), $_POST));
}
catch (WebToPayException $e) {
    $_SESSION['error'] = $e->getMessage();
    redirect_to($base_url);
}

save_request_data(array(
        'request'           => $request,
        'sign_password'     => $_POST['sign_password'],
    ));

$form = array();
$form['title'] = 'Ruquest form';
$form['action'] = WebToPay::PAY_URL;
$form['data'] = $request;

echo template('base.html', array(
        'content' => template('form-preview.html', $form)
    ));

