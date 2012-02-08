<h1>Orders</h1>

<?php foreach ($orders as $id => $order): ?>
    <h2><?php echo h($order['item']['title']); ?></h2>
    <strong>Price: </strong> <?php echo h($order['item']['price'] / 100), ' ', h($order['item']['currency']); ?><br />
    <strong>Status: </strong> <?php echo h($order['status']); ?><br />
    <strong>Additional parameters: </strong><ul>
    <?php foreach($order['additionalData'] as $key => $value): ?>
        <li><?php echo h($key), ': ', h($value); ?></li>
    <?php endforeach; ?>
    </ul>
<?php endforeach; ?>