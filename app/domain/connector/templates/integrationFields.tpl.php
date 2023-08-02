<?php
$providerFields = $this->get("providerFields");
$provider = $this->get("provider");
$leantimeFields = $this->get("leantimeFields");
$numberOfFields = $this->get("maxFields");
$flags = $this->get("flags");
$urlAppend = '';

if (isset($integrationId) && is_numeric($integrationId)) {
    $urlAppend = "&integrationId=" . $integrationId;
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

<body>

    <div class="pageheader">
        <div class="pageicon"><span class="fa fa-plug"></span></div>
        <div class="pagetitle">
            <h1>Integrations</h1>
        </div>
    </div>
    <div class="maincontent">
        <div class="maincontentinner">

            <?php echo $this->displayNotification(); ?>

            <h3>Map Your Source Fields to Leantime Fields</h3>
            <p class="mb-2"><strong><?= $provider->name ?></strong></p>

            <?php foreach ($flags as $flag) { ?>
                <p style = "color: red"><?= $flag ?></p>
            <?php } ?>

            <form method="post" action="<?= BASE_URL ?>/connector/integration/?provider=<?= $provider->id ?>&step=import<?= $urlAppend ?>">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Source Field</th>
                        <th>Leantime Field</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($providerFields as $key => $entity) { ?>
                        <tr>
                            <td><?= $entity ?></td>
                            <td>
                                <select class="form-control" name="field_<?= md5($entity) ?>">
                                    <?php foreach ($leantimeFields as $key2 => $fields) { ?>
                                        <option value="<?= $entity ?>|<?= $key2 ?>" <?= $entity == $fields['name'] ? "selected='selected'" : "" ?>>
                                            <?= $fields['name'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary">Next</button>
            </form>
        </div>
    </div>
</body>

</html>
