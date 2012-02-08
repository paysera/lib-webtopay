<h1>Select payment method</h1>
<form action="<?php echo get_address('request.php'); ?>" method="post">
    <?php foreach ($methods->getCountries() as $country): ?>
        <h2><?php echo h($country->getTitle()); ?></h2>
        <?php foreach ($country->getGroups() as $group): ?>
            <h3><?php echo h($group->getTitle()); ?></h3>
            <?php foreach ($group->getPaymentMethods() as $paymentMethod): ?>
                <?php if ($paymentMethod->getLogoUrl()): ?>
                    <label>
                        <input type="radio" class="radio" name="payment" value="<?php echo h($paymentMethod->getKey()); ?>" />
                        <img src="<?php echo h($paymentMethod->getLogoUrl()); ?>" />
                        <?php echo h($paymentMethod->getTitle()); ?>
                    </label>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <?php foreach ($post as $name => $value): ?>
        <input type="hidden" name="<?php echo h($name); ?>" value="<?php echo h($value); ?>" />
    <?php endforeach; ?>
    <input type="submit" value="Buy" />
</form>
