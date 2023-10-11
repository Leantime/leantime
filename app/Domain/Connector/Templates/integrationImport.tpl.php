<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<?php
$providerFields = $tpl->get("providerFields");
$provider = $tpl->get("provider");
$leantimeFields = $tpl->get("leantimeFields");
$numberOfFields = $tpl->get("maxFields");
$values = $tpl->get("values");
$flags = $tpl->get("flags");
$fields = $tpl->get("fields");
$urlAppend = '';
if(isset($integrationId) && is_numeric($integrationId)) {
    $urlAppend = "&integrationId=".$integrationId;
}
?>


<div class="pageheader">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">
        <h1>Integrations</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        <?php echo $tpl->displayNotification(); ?>

        <h4><?=$provider->name ?></h4>

        <p>The following data will be imported into your Leantime instance:</p>

        <table>
            <thead>
            <tr>
                <?php foreach ($fields as $sourceField => $leantimeField): ?>
                    <th><?= $leantimeField['leantimeField'] ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($values as $record): ?>
                <tr>
                    <?php foreach ($record as $value): ?>
                        <td><?= $value ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (!empty($flags)) { ?>
            <p style="font-style: oblique">Please resolve the following errors and reconnect your integration:</p>
            <ul style="padding-left: 20px; margin-bottom: 20px;">
                <?php foreach ($flags as $flag) { ?>
                    <li style="margin-right: 10px; color: red; font-style: oblique;"><?= $flag ?></li>
                <?php } ?>
            </ul>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/connector/integration?provider=<?= $provider->id ?>">Reconnect Integration</a>
        <?php } else { ?>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/connector/integration?provider=<?= $provider->id ?>&step=confirm">Confirm</a>
        <?php } ?>

    </div>
</div>



