<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';

echo template('list.html', array(
    'shopItems' => $shopItems,
));

