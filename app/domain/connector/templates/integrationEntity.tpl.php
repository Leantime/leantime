<?php
    $providerEntities = $this->get("providerEntities");
    $provider = $this->get("provider");
    $leantimeEntities = $this->get("leantimeEntities");
    $integrationId = $this->get("integrationId");

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

            <h3>Align Systems Here</h3>
            <p><strong><?= $provider->name ?></strong></p>

            <p>What entities do you want to map?</p>

            <form method="post" action="<?= BASE_URL ?>/connector/integration/?provider=<?= $provider->id ?>&step=fields<?= $urlAppend ?>">
                <div class="form-group">
                    <label for="leantimeEntities">Leantime</label>
                    <select name="leantimeEntities" id="leantimeEntities">
                        <?php foreach ($leantimeEntities as $key => $entity) { ?>
                            <option value="<?= $key ?>"><?= $entity['name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="providerEntities"><?= $provider->name ?></label>
                    <select name="providerEntities" id="providerEntities">
                        <?php foreach ($providerEntities as $key => $entity) { ?>
                            <option value="<?= $key ?>"><?= $entity['name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <input type="submit" value="Next" class="btn" />
            </form>
        </div>
    </div>
<script type="text/javascript">

    jQuery(document).ready(function() {


    });

</script>
</body>

</html>




