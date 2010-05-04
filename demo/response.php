<?php

require_once 'helpers.php';
require_once('../WebToPay.php');

$answer = isset($_GET['answer']) ? $_GET['answer'] : 'cancel';

if ('sms' == $answer) {
    try {
        WebToPay::checkResponse($_GET, array(
                'sign_password' => '526a3c835fc3a39e1369fa7446b3537f',
                'log' => 'var/mokejimai.log',
            ));

        $meta['status'] = 'OK';
        $meta['verified'] = WebToPay::$verified;

        echo 'OK ok';
    }
    catch (Exception $e) {
        $meta['status'] = get_class($e).': '.$e->getMessage();
        if (WebToPay::$verified) {
            $meta['verified'] = WebToPay::$verified;
        }
        echo 'FAIL '.WebToPay::$verified;
        echo '<p>'.$meta['status'].'</p>';
    }
}
elseif ('callback' == $answer) {
    $meta = array(
            'time'      => date('Y-m-d H:i:s'),
            'verified'  => 'none',
        );

    try {
        $response = WebToPay::getPrefixed($_GET, WebToPay::PREFIX);
        $data = load_request_data($response);
        if (false === $data) {
            throw new Exception('Missing requested data.');
        }

        $request = $data['request'];

        WebToPay::checkResponse($_GET, array(
                'projectid'     => $request['projectid'],
                'orderid'       => $request['orderid'],
                'amount'        => $request['amount'],
                'currency'      => $request['currency'],
                'sign_password' => $data['sign_password'],
            ));

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

