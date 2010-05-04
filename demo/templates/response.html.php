<table style="width:100%;">
    <tr>
        <td style="width:50%;vertical-align:top;">
            <?php echo template('preview.html', array(
                    'title'     => 'Request',
                    'data'      => $request,
                )) ?>
        </td>

        <td style="width:50%;vertical-align:top;">
            <?php if (empty($response)): ?>
            <fieldset>
                <legend>Response</legend>
                Waiting for callback...
            </fieldset>
            <?php else: ?>
            <?php echo template('preview.html', array(
                    'title'         => 'Response',
                    'data'          => $response,
                    'meta'          => $meta,
                    'response_url'  => $response_url,
                )) ?>
            <?php endif; ?>
        </td>
    </tr>
</table>

