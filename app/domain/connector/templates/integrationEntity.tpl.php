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
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 10px;
            background-color: #f7f7f7;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .pageheader {
            background-color: #3b5998;
            color: #fff;
            padding: 10px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin-bottom: 20px; /* Added to create spacing between header and content */
        }

        .pageheader h1 {
            margin: 0;
        }

        .maincontent {
            margin-top: 20px;
        }

        .maincontentinner {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-weight: bold;
            display: block;
        }

        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3b5998;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #2d4373;
        }

        .notification {
            color: #ff0000;
        }
    </style>
</head>

<body>

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


    <div class="container">
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
</div>
<script type="text/javascript">

    jQuery(document).ready(function() {


    });

</script>
</body>

</html>




