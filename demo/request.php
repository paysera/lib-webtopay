<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../WebToPay.php';

$base_url = get_address() . dirname($_SERVER['SCRIPT_NAME']);
$response_url = $base_url . '/response.php';

$post = array();
foreach ($_POST as $key => $val) {
    $val = trim($val);
    if (!empty($val)) {
        if (get_magic_quotes_gpc()) {
            $post[$key] = stripslashes($val);
        }
        else {
            $post[$key] = $val;
        }
    }
}

$_SESSION['posted'] = $post;

try {
    if (!empty($_GET['repeat'])) {
        $base_url .= '/repeat.php';
        $request = WebToPay::buildRepeatRequest(array_merge($post, $config));
    }
    else {
        $request = WebToPay::buildRequest(array_merge($post, $config));
        save_request_data($request);
    }
}
catch (WebToPayException $e) {
    $_SESSION['error'] = $e->getMessage();
    redirect_to($base_url);
}

$form = array();
$form['title'] = 'Request form';
$form['action'] = WebToPay::PAY_URL;
$form['data'] = $request;

echo template('base.html', array(
    'content' => template('form-preview.html', $form)
), false);

