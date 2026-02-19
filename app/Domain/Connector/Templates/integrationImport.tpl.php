<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<?php
$providerFields = $tpl->get('providerFields');
$provider = $tpl->get('provider');
$leantimeFields = $tpl->get('leantimeFields');
$numberOfFields = $tpl->get('maxFields');
$values = $tpl->get('values');
$flags = $tpl->get('flags');
$fields = $tpl->get('fields');
$urlAppend = '';
if (isset($integrationId) && is_numeric($integrationId)) {
    $urlAppend = '&integrationId='.$integrationId;
}
?>


<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-circle-nodes"></i></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1><?php echo $tpl->__('headlines.integrations'); ?> // <?= $provider->name ?> </h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        <?php $tpl->displaySubmodule('connector-importProgress') ?>
    </div>
    <div class="maincontentinner center">
        <?php echo $tpl->displayNotification(); ?>

        <h5 class="subtitle">Review</h5>

        <?php if (! empty($flags)) { ?>
            <p style="font-style: oblique">Please resolve the following errors and reconnect your integration:</p>
            <ul style="padding-left: 20px; margin-bottom: 20px;">
                <?php
                $messages = [];
            foreach ($flags as $flag) { ?>
                    <?php if (in_array($flag, $messages) === false) { ?>
                        <li style="margin-right: 10px; color: red; font-style: oblique;"><?= $flag ?></li>
                    <?php
                    $messages[] = $flag;
                    } ?>
                <?php } ?>
            </ul>
            <a class="btn btn-primary pull-left" href="<?= BASE_URL ?>/connector/integration?provider=<?= $provider->id ?>&step=fields<?= $urlAppend?>">Go Back</a>
        <?php } else { ?>
            <a class="btn btn-primary right" href="<?= BASE_URL ?>/connector/integration?provider=<?= $provider->id ?>&step=import">Confirm</a>
        <?php } ?>
        <div class="clearall"></div>

        <p>All set, we are importing the data you see below.</p>
        <br />

        <table width="100%">
            <thead>
            <tr>
                <?php foreach ($fields as $sourceField => $leantimeField) { ?>
                    <th><?= $leantimeField['leantimeField'] ?></th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($values as $record) { ?>
                <tr>
                    <?php foreach ($record as $value) { ?>
                        <td><?= $value ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <br />

        <div class="clearall"></div>

    </div>
</div>



