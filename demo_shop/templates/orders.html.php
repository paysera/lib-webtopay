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
    <?php if (isset($order['response'])): ?>
        <strong>Server response: </strong><ul>
        <?php foreach($order['response'] as $key => $value): ?>
            <li><?php echo h($key), ': ', h($value); ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <?php if (isset($order['additionalResponse'])): ?>
        <strong>Additional server response: </strong><ul>
        <?php foreach($order['additionalResponse'] as $key => $value): ?>
            <li><?php echo h($key), ': ', h($value); ?></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endforeach; ?>

<h1>SMS log</h1>
<?php foreach ($sms as $data): ?>
    <strong>Request: </strong><ul>
    <?php foreach($data['_GET'] as $key => $value): ?>
        <li><?php echo h($key), ': ', h($value); ?></li>
    <?php endforeach; ?>
    </ul>
    <?php if (is_array($data['parsedData'])): ?>
    <strong>Data: </strong><ul>
    <?php foreach($data['parsedData'] as $key => $value): ?>
        <li><?php echo h($key), ': ', h($value); ?></li>
    <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <strong>Data: </strong> <?php echo h($data['parsedData']); ?><br />
    <?php endif; ?>
<?php endforeach; ?>