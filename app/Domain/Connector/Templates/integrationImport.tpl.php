<?php
$providerFields = $this->get("providerFields");
$provider = $this->get("provider");
$leantimeFields = $this->get("leantimeFields");
$numberOfFields = $this->get("maxFields");
$values = $this->get("values");
$flags = $this->get("flags");
$fields = $this->get("fields");
$urlAppend = '';
if(isset($integrationId) && is_numeric($integrationId)) {
    $urlAppend = "&integrationId=".$integrationId;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->__("headlines.integrations"); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">
        <h1>Integrations</h1>
    </div>
</div>
<body>
<div class="maincontent">
    <div class="maincontentinner">
        <?php echo $this->displayNotification(); ?>

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

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>



