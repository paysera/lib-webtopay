<fieldset>
    <legend><?php echo $title; ?></legend>

    <?php if (!empty($response_url)): ?>
    Response URL: 
    <a href="<?php echo $response_url; ?>">
        <?php echo substr($response_url, 0, 24).'...'; ?>
    </a>
    <hr />
    <p>&nbsp;</p>
    <?php endif; ?>

    <?php if (!empty($meta)): ?>
    <?php foreach ($meta as $key => $val): ?>
    <label>
        <?php echo $key; ?>
        <div class="input-preview"><?php echo htmlspecialchars($val); ?></div>
        <?php endforeach; ?>
        </label>
    <hr />
    <p>&nbsp;</p>
    <?php endif; ?>

    <?php foreach ($data as $key => $val): ?>
    <label>
        <?php echo $key; ?>
        <div class="input-preview"><?php echo htmlspecialchars($val); ?></div>
    </label>
    <?php endforeach; ?>
</fieldset>
