<?php

require_once 'helpers.php';
require_once('../WebToPay.php');

$answer = isset($_GET['answer']) ? $_GET['answer'] : 'cancel';

if ('callback' == $answer) {
    $meta = array(
            'time'      => date('Y-m-d H:i:s'),
            'verified'  => 'none',
        );

    try {
        $data = load_request_data($_GET);
        if (false === $data) {
            throw new Exception('Missing requested data.');
        }

        $request = $data['request'];

        WebToPay::checkResponse($_GET, array(
                'projectid'     => $request['projectid'],
                'orderid'       => $request['orderid'],
                'amount'        => $request['amount'],
                'currency'      => $request['currency'],
                'account_password' => $request['account_password'],
            ));

        $meta['status'] = 'OK';
        $meta['verified'] = WebToPay::$verified;
        save_response_data($_GET, $meta);

        echo 'OK';
    }
    catch (Exception $e) {
        var_dump($e->getMessage());
        $meta['status'] = get_class($e).': '.$e->getMessage();
        if (WebToPay::$verified) {
            $meta['verified'] = WebToPay::$verified;
        }
        save_response_data($_GET, $meta);
    }
}

else if ('accept' == $answer) {
    try {
        if (!isset($_SESSION['posted'])) {
            throw new Exception('Session expired.');
        }

        $data = load_request_data($_SESSION['posted']);
        if (false === $data) {
            throw new Exception('Missing requested data.');
        }

        if (isset($data['response'])) {
            $respurl = array();
            foreach ($data['response'] as $key => $val) {
                $respurl[] = $key.'='.urlencode($val);
            }
            $data['response_url'] = '?'.implode('&', $respurl);
        }
        else {
            $data['response_url'] = '';
        }

        echo template('base.html', array(
                'content' => template('response.html', $data)
            ));
    }
    catch (Exception $e) {
        echo template('base.html', array(
                'content' => '<div class="error">'
                    . htmlspecialchars($e->getMessage()).'</div>',
            ));
    }
}

else {
    echo template('base.html', array(
            'content' => '<div class="error">Payment rejected.</div>',
        ));
}

