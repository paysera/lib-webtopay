<?php
$config = array(
    'sign_password' => '73cdb059d0f29f275a34b370f8e4f900',        // your password
    'projectid' => 6028,                                          // your project id

    'test' => 1,            // disable in production
    'accepturl' => get_address('response.php?answer=accept'),
    'cancelurl' => get_address('response.php?answer=cancel'),
    'callbackurl' => get_address('response.php?answer=callback'),
);

$shopItems = array(            // just sample shop items; unrelated to WebToPay library
    array(
        'title' => 'Item A',
        'price' => 100,
        'currency' => 'LTL',
    ),
    array(
        'title' => 'Item B',
        'price' => 2000,
        'currency' => 'EUR',
    ),
    array(
        'title' => 'Item C',
        'price' => 4990,
        'currency' => 'LTL',
    ),
);