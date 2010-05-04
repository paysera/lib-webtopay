<form action="<?php echo $action; ?>" method="post">
    <fieldset>
        <legend><?php echo $title; ?></legend>

        <p>Request URL: <em><?php echo $action; ?></em></p>

        <?php foreach ($data as $key => $val): ?>
        <label>
            <?php echo $key; ?>
            <div class="input-preview"><?php echo htmlspecialchars($val); ?></div>
            <input type="hidden" name="<?php echo $key ?>" value="<?php echo addslashes($val); ?>" />
        </label>
        <?php endforeach; ?>
    </fieldset>
    <input type="submit" value="Submit" />
</form>
