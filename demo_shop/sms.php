<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../src/includes.php';

$get = removeQuotes($_GET);

try {
    $parsedData = WebToPay::validateAndParseData($get, $config['projectid'], $config['sign_password']);
} catch (WebToPayException $e) {
    $parsedData = 'Error: ' . $e->getMessage();
}

$data = load_data();
$data['sms'][] = array(
    '_GET' => $get,
    'parsedData' => $parsedData,
);
save_data($data);