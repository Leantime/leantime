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
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .pageheader {
            background-color: #388dac;
            color: #fff;
            padding: 10px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .pageicon {
            font-size: 24px;
        }

        .pagetitle {
            padding-top: 10px;
        }

        .pagetitle h1 {
            margin: 0;
            font-size: 28px;
        }

        .maincontent {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }

        .notification {
            color: #ff0000;
            margin-bottom: 10px;
        }

        h4 {
            margin-bottom: 10px;
        }

        h3 {
            margin-bottom: 20px;
            color: #1f74b9;
            font-size: 24px;
            font-weight: bold;
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
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background-color: #ff5e00;
        }

        .btn:hover {
            background-color: #2d4373;
        }
    </style>
</head>

<body>
<div class="pageheader">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">

            </div>
        </div>
    </div>
</div>

<div class="maincontent">

        <?php echo $this->displayNotification(); ?>


        <h3>Your data has been successfully integrated into Leantime!</h3>

    </div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

