<?php

ini_set('display_errors', '1');

session_start();

function template($__filename__, $vars) {
    extract($vars, EXTR_SKIP);
    ob_start();
    include 'templates/'.$__filename__.'.php';
    return ob_get_clean();
}

function get_address() {
    $protocol = isset($_SERVER['HTTPS']) &&
                $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
    return $protocol.'://'.$_SERVER['HTTP_HOST'];
}

function redirect_to($url) {
    header('Location: '.$url);
    exit;
}

function get_request_data_file($data) {
    $items = array(
            'var/data',
            $data['merchantid'],
            $data['orderid'],
        );
    return implode('-', $items).'.php';
}

function save_request_data($request) {
    $data = array('request' => $request);
    $data = serialize($data);

    $file = get_request_data_file($request);

    $fp = fopen($file, 'w');
    fwrite($fp, $data);
    fclose($fp);
}

function save_response_data($response, $meta) {
    $data = load_request_data($response);

    if (false == $data) {
        return false;
    }

    $data['meta'] = $meta;
    $data['response'] = $response;
    $data = serialize($data);

    $file = get_request_data_file($response);

    $fp = fopen($file, 'w');
    fwrite($fp, $data);
    fclose($fp);
}

function load_request_data($data) {
    $file = get_request_data_file($data);

    if (is_file($file)) {
        $data = file_get_contents($file);
        return unserialize($data);
    }
    else {
        return false;
    }
}

