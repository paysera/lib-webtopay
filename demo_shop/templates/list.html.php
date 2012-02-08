<h1>Shop items</h1>

<?php foreach ($shopItems as $id => $item): ?>
    <h2><?php echo h($item['title']); ?></h2>
    <strong>Price: </strong> <?php echo h($item['price'] / 100), ' ', h($item['currency']); ?><br />
    <a href="<?php echo get_address('buy.php?id=') . $id; ?>">Buy</a>
<?php endforeach; ?>