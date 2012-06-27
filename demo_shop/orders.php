<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';

$data = load_data();

echo template('orders.html', array(
    'orders' => isset($data['orders']) ? $data['orders'] : array(),
    'sms' => isset($data['sms']) ? $data['sms'] : array()
));