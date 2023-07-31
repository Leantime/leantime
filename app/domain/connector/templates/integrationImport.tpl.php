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
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
        }

        .pageheader {
            background-color: #3b5998;
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
        }

        .notification {
            color: #ff0000;
            margin-bottom: 10px;
        }

        h4 {
            margin-bottom: 10px;
        }

        p {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
            max-width: 250px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tbody tr:hover {
            background-color: #ddd;
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
<div class="pageheader mb-4">
    <div class="pageicon"><span class="fa fa-plug"></span></div>
    <div class="pagetitle">

    </div>
</div>
<div class="container mt-4">
    <div class="maincontent">
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



