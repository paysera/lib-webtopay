<?php

require_once('../WebToPay.php');

ini_set('display_errors', '1');

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
$fullurl = $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

$request = WebToPay::buildRequest(array(
        'merchantid'    => 1,
        'orderid'       => 1,
        'accepturl'     => $fullurl.$_SERVER['SCRIPT_NAME'].'?answer=accept',
        'cancelurl'     => $fullurl.$_SERVER['SCRIPT_NAME'].'?answer=cancel',
        'callbackurl'   => $fullurl.dirname($_SERVER['SCRIPT_NAME']).'/response.php',
        'sign_password' => 'secret',
    ));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<title>libwebtopay demo</title>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />

<style type="text/css">
label {
    font-weight: bold;
}
label input {
    display: block;
    margin-bottom: 10px;
    width: 100%;
}
input[type=submit] {
    margin: 16px;
    font-weight: bold;
    font-size: 120%;
    float: right;
}
legend {
    font-size: 150%;
    font-weight: bold;
}
</style>

</head>

<body>

<form action="<?php echo WebToPay::PAY_URL; ?>" method="post">
    <fieldset>
        <legend>Request form</legend>

        <p>Request: <em><?php echo WebToPay::PAY_URL; ?></em></p>

        <?php foreach ($request as $key => $val): ?>
        <label>
            <?php echo $key; ?>
            <input name="<?php echo $key ?>" value="<?php echo addslashes($val); ?>" />
        </label>
        <?php endforeach; ?>
    </fieldset>
    <input type="submit" />
</form>
    
</body>
</html>

