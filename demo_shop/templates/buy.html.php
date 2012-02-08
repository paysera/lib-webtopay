<h1>Buy item</h1>

<h2><?php echo h($item['title']); ?></h2>
<strong>Price: </strong> <?php echo h($item['price'] / 100), ' ', h($item['currency']); ?><br />

<form action="<?php echo get_address('paymentMethod.php'); ?>" method="post">
    <label>Email:* <input name="p_email" /></label>
    <label>Name: <input name="p_firstname" /></label>
    <label>Surname: <input name="p_lastname" /></label>
    <label>Address: <input name="p_street" /></label>
    <label>City: <input name="p_city" /></label>
    <label>State: <input name="p_state" /></label>
    <label>ZIP code: <input name="p_zip" /></label>
    <input type="hidden" name="id" value="<?php echo h($id); ?>" />
    <input type="submit" value="Submit" />
</form>
