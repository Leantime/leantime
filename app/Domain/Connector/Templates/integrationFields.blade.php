@extends($layout)

@section('content')

<?php
$providerFields = $tpl->get("providerFields");
$provider = $tpl->get("provider");
$leantimeFields = $tpl->get("leantimeFields");
$numberOfFields = $tpl->get("maxFields");
$flags = $tpl->get("flags");
$urlAppend = '';

if (isset($integrationId) && is_numeric($integrationId)) {
    $urlAppend = "&integrationId=" . $integrationId;
}
?>

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-circle-nodes"></i></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h1><?php echo $tpl->__("headlines.connector"); ?> // <?=$provider->name ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">
        @include("connector::includes.importProgress")
    </div>
    <div class="row">
        <div class="col-md-9">
            <div class="maincontentinner center">

                @displayNotification()
                <h5 class="subtitle">Match Fields</h5>
                <p class="mb-2">Match the fields from your source to the corresponding fields in Leantime</p><br />

                <form method="post" action="<?= BASE_URL ?>/connector/integration/?provider=<?= $provider->id ?>&step=parse<?= $urlAppend ?>">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="center">Source Field</th>
                            <th class="center">Leantime Field</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($providerFields as $key => $entity) { ?>
                            <tr>
                                <td class="center"><?= $entity ?></td>
                                <td class="center">
                                    <select class="form-control" name="field_<?= md5($entity) ?>">
                                        <?php foreach ($leantimeFields as $key2 => $fields) { ?>
                                            <option value="<?= $entity ?>|<?= $key2 ?>" <?= $entity == $fields['name'] ? "selected='selected'" : "" ?>>
                                                <?= $fields['name'] ?>
                                            </option>
                                        <?php } ?>
                                        <option value="">Don't map</option>
                                    </select>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <div class="left">
                        <a href="<?= BASE_URL ?>/connector/integration/?provider=<?= $provider->id ?>" class="btn btn-default pull-left">Back</a>
                    </div>
                    <div class="right">
                        <button type="submit" class="btn btn-primary">Next</button>
                    </div>
                    <div class="clearall"></div>
                </form>
            </div>
        </div>
        <div class="col-md-3">
            <div class="maincontentinner">
            <h5 class="subtitle">Requirements for a successful impot</h5>
            <p>Please review these requirements and make sure your import and mapping covers everything.</p>
            <?php foreach ($flags as $flag) { ?>
                <hr />
                <p style="padding-left:10px"><strong><?= $flag ?></strong></p>

            <?php } ?>
            </div>
        </div>

    </div>

</div>
