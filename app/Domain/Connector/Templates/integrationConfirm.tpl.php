<?php
$providerFields = $this->get("providerFields");
$provider = $this->get("provider");
$leantimeFields = $this->get("leantimeFields");
$numberOfFields = $this->get("maxFields");
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


        <h3>Your data has been successfully integrated into Leantime!</h3>

    </div>
</div>
</body>

</html>

