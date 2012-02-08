<?php

ini_set('display_errors', '1');
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Vilnius');

session_start();

function template($filename, $vars = null) {
    if ($vars === null) {
        $content = $filename;
    } else {
        extract($vars, EXTR_SKIP);
        ob_start();
        include 'templates/' . $filename . '.php';
        $content = ob_get_clean();
    }

    ob_start();
    include 'templates/base.html.php';
    return ob_get_clean();
}

function h($var) {
    return htmlspecialchars($var, ENT_QUOTES, 'UTF-8');
}

function removeQuotes($post) {
    if (get_magic_quotes_gpc()) {
        foreach ($post as &$var) {
            if (is_array($var)) {
                $var = removeQuotes($var);
            } else {
                $var = stripslashes($var);
            }
        }
    }
    return $post;
}

function get_address($scriptName = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
    return $protocol.'://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/' . $scriptName;
}

function redirect_to($url) {
    header('Location: '.$url);
    exit;
}

function save_data($data) {
    $fp = fopen('var/data.php', 'w');
    fwrite($fp, '<?php return ' . var_export($data, true) . ';');
    fclose($fp);
}

function load_data() {
    $file = 'var/data.php';
    if (is_file($file)) {
        return include $file;
    } else {
        return array();
    }
}

