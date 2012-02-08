<?php

ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Vilnius');

session_start();

function template($filename, $vars, $encode = true) {
    if ($encode) {
        $vars = escapeEntities($vars);
    }
    extract($vars, EXTR_SKIP);
    ob_start();
    include 'templates/' . $filename . '.php';
    return ob_get_clean();
}

function escapeEntities($vars) {
    foreach ($vars as &$var) {
        if (is_array($var)) {
            $var = escapeEntities($var);
        } else {
            $var = htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
        }
    }
    return $vars;
}

function get_address() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
    return $protocol.'://'.$_SERVER['HTTP_HOST'];
}

function redirect_to($url) {
    header('Location: '.$url);
    exit;
}

function get_request_data_file($data) {
    $items = array(
        'var/data',
        $data['projectid'],
        $data['orderid'],
    );
    return implode('-', $items).'.php';
}

function save_request_data($request) {
    $file = get_request_data_file($request);

    $request = array('request' => $request);

    $fp = fopen($file, 'w');
    fwrite($fp, '<?php return ' . var_export($request, true) . ';');
    fclose($fp);
}

function save_response_data($response, $meta) {
    $file = get_request_data_file($response);
    $data = load_data($response);

    if (false == $data) {
        return false;
    }

    $data['meta'] = $meta;
    $data['response'] = $response;

    $fp = fopen($file, 'w');
    fwrite($fp, '<?php return ' . var_export($data, true) . ';');
    fclose($fp);
}

function load_data($data) {
    $file = get_request_data_file($data);
    if (is_file($file)) {
        return include $file;
    }
    else {
        return false;
    }
}

