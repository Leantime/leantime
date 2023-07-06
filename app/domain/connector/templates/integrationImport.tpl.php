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

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1><?php echo $this->__("headlines.integrations"); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <h4><?=$provider->name ?></h4>

        <p>The following data will be imported into your Leantime instance: </p>

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
            <p style="font-style: oblique">Please resolve the following errors and reconnect your integration.</p>
        <?php } ?>
        <ul>
            <?php foreach ($flags as $flag) { ?>
                <li style="color: red;"><?php echo $flag; ?></li>
            <?php } ?>
        </ul>

        <?php if (empty($flags)) { ?>
            <a class="btn btn-primary" href="<?=BASE_URL?>/connector/integration?provider=<?=$provider->id?>&step=confirm">Confirm</a>
        <?php } else { ?>
            <a class="btn btn-primary" href="<?=BASE_URL?>/connector/integration?provider=<?=$provider->id?>">Reconnect Integration</a>
        <?php } ?>



    </div>
</div>

<script type="text/javascript">


    jQuery(document).ready(function() {


    });

</script>
