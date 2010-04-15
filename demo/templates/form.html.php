<form action="<?php echo $action; ?>" method="post">
    <fieldset>
        <legend><?php echo $title; ?></legend>

        <?php if (isset($error)): ?>
        <div class="error"><strong>Error:</strong> <?php echo $error; ?></div>
        <?php endif; ?>

        <?php foreach ($data as $key => $val): ?>
        <label>
            <?php echo $key; ?>
            <?php if ('account_password' == $key): ?>
            <input class="text-input" name="<?php echo $key ?>" value="<?php echo addslashes($val); ?>" type="password" />
            <?php else: ?>
            <input class="text-input" name="<?php echo $key ?>" value="<?php echo addslashes($val); ?>" />
            <?php endif; ?>
        </label>
        <?php endforeach; ?>
    </fieldset>
    <input type="submit" />
</form>
