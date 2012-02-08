<?php

require_once 'includes/helpers.php';
require_once 'includes/config.php';

$id = $_GET['id'];
if (!isset($shopItems[$id])) {
    redirect_to(get_address());
}
$item = $shopItems[$id];

echo template('buy.html', array(
    'item' => $item,
    'id' => $id,
));

