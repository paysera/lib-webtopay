<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';
require_once '../WebToPay.php';

$answer = isset($_GET['answer']) ? $_GET['answer'] : 'cancel';

if ('sms' == $answer) {
    try {
        $response = WebToPay::checkResponse($_GET, array(
            'sign_password' => $config['sign_password'],
            'log' => 'var/mokejimai.log',
        ));
        if ($response['test'] != $config['test']) {
            throw new Exception('Test value is not as expected');
        }

        $meta['status'] = 'OK';
        $meta['verified'] = WebToPay::$verified;

        echo 'OK SMS message text';
    }
    catch (Exception $e) {
        $meta['status'] = get_class($e).': '.$e->getMessage();
        if (WebToPay::$verified) {
            $meta['verified'] = WebToPay::$verified;
        }
        echo 'FAIL ' . $meta['status'];
    }
}
elseif ('callback' == $answer) {
    $meta = array(
        'time'      => date('Y-m-d H:i:s'),
        'verified'  => 'none',
    );

    try {
        $response = WebToPay::getPrefixed($_GET, WebToPay::PREFIX);
        $data = load_data($response);
        if (false === $data) {
            throw new Exception('Missing requested data.');
        }

        $request = $data['request'];

        $response = WebToPay::checkResponse($_GET, array(
            'projectid'     => $config['projectid'],
            'sign_password' => $config['sign_password'],
        ));
        if (
            $response['test'] != $config['test']
            || $response['amount'] != $request['amount']        // you should check if amount and currency matches
            || $response['currency'] != $request['currency']
            || $response['status'] != 1
        ) {
            throw new Exception('Some values are not as expected');
        }

        $meta['status'] = 'OK';
        $meta['verified'] = WebToPay::$verified;
        save_response_data($response, $meta);

        echo 'OK';
    }
    catch (Exception $e) {
        $meta['status'] = get_class($e).': '.$e->getMessage();
        if (WebToPay::$verified) {
            $meta['verified'] = WebToPay::$verified;
        }
        save_response_data($response, $meta);
        echo 'FAIL ' . $meta['status'];
    }
}
else if ('accept' == $answer) {
    try {
        if (!isset($_SESSION['posted'])) {
            throw new Exception('Session expired.');
        }

        $data = load_data(array_merge($_SESSION['posted'], $config));
        if (false === $data) {
            throw new Exception('Missing requested data.');
        }

        if (isset($data['response'])) {
            $respurl = array();
            foreach ($data['response'] as $key => $val) {
                $respurl[] = WebToPay::PREFIX. $key . '=' . urlencode($val);
            }
            $data['response_url'] = '?answer=callback&' . implode('&', $respurl);
        }
        else {
            $data['response_url'] = '';
        }

        echo template('base.html', array(
            'content' => template('response.html', $data)
        ), false);
    }
    catch (Exception $e) {
        echo template('base.html', array(
            'content' => '<div class="error">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>',
        ), false);
    }
}
else {
    echo template('base.html', array(
        'content' => '<div class="error">Payment rejected.</div>',
    ), false);
}

