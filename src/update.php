<?php

$config = new leantime\core\config();
$settings = new leantime\core\settings();
$install = new leantime\core\install($config, $settings);

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#<?php echo $_SESSION["companysettings.mainColor"] ?>">
    <meta name="identifier-URL" content="<?=BASE_URL?>">

<link rel="shortcut icon" href="<?=BASE_URL ?>/favicon.ico" />
<link rel="apple-touch-icon" href="<?=BASE_URL ?>/apple-touch-icon.png">

<title><?php echo $_SESSION["companysettings.sitename"]; ?></title>

<?php echo $frontController->includeAction('general.header'); ?>

    <link rel="stylesheet" href="<?=BASE_URL ?>/css/style.default.css?v=<?php echo $settings->appVersion; ?>" type="text/css" />
    <link rel="stylesheet" href="<?=BASE_URL ?>/css/style.custom.php?color=<?php echo $_SESSION["companysettings.mainColor"]; ?>&v=<?php echo $settings->appVersion; ?>" type="text/css" />
    <link rel="stylesheet" href="<?=BASE_URL ?>/css/main.css"/>

    <script type="text/javascript" src="<?=BASE_URL ?>/js/libs/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="<?=BASE_URL ?>/js/libs/jquery-migrate-1.1.1.min.js"></script>
    <script type="text/javascript" src="<?=BASE_URL ?>/js/libs/jquery-ui-1.9.2.min.js"></script>
    <script type="text/javascript" src="<?=BASE_URL ?>/js/libs/modernizr.min.js"></script>
    <script type="text/javascript" src="<?=BASE_URL ?>/js/libs/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?=BASE_URL ?>/js/libs/jquery.cookie.js"></script>

    <!--[if lte IE 8]><script language="javascript" type="text/javascript" src="<?=BASE_URL ?>/js/libs/excanvas.min.js"></script><![endif]-->

    <script type="text/javascript">
        jQuery(document).ready(function(){

            if(jQuery('.login-alert .alert').text() != ''){
                jQuery('.login-alert').fadeIn();
            }

        });
    </script>
</head>


<body class="loginpage" style="height:100%;">

<div class="header hidden-gt-sm">

    <div class="logo" style="margin-left:0px;">
        <a href="<?=BASE_URL ?>/" style="background-image:url(<?php echo $config->logoPath; ?>">&nbsp;</a>
    </div>

</div>

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft" style="background:#<?php echo $_SESSION["companysettings.mainColor"]; ?>" >
        <div class="row">
            <div class="col-md-5">

            </div>
            <div class="col-md-6" style="position:relative;">
                <a href="<?=BASE_URL ?>/" target="_blank"><img src="<?php echo $_SESSION["companysettings.logoPath"]; ?>" /></a>
                <h1 style="font-family:Exo;  font-size: 64px; padding-left:15px; font-weight:400;">Drive Impact</h1>
                <span class="iq-objects-04 iq-fadebounce">
				    <span class="iq-round"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12 regRight"  style="box-shadow: -2px 0px 2px #494949; padding-top:14%; border-left: 1px solid #ddd;">

            <div class="regpanel">
            <div class="regpanelinner">
                <div class="pageheader">
                    <div class="pageicon"><span class="iconfa-signin"></span></div>
                    <div class="pagetitle">
                        <h5><?php echo $_SESSION["companysettings.sitename"]; ?></h5>
                        <h1>Update Database</h1>
                        <p>A new version has been installed. To use Leantime you will need to update your database.
                            <br/><br/><strong>Please make sure to backup your database before performing this update!</strong><br />
                        This may take a few minutes.</p><br />
                    </div>
                </div>
                <div class="regcontent"  id="login" style="margin-left: 90px;">

                    <?php
                    $success = false;


                    if(isset($_POST['updateDB'])) {

                        $success = $install->updateDB();

                    }

                    ?>
                    <?php
                        if(is_array($success) === true){
                            echo "
                                <div class='inputwrapper login-alert'>
                                    <div class='alert alert-error'>";
                                    foreach($success as $errorMessage) {
                                        echo $errorMessage."<br />";
                                    }
                            echo "</div>
                                </div>";
                        }

                        if($success === true) {
                            echo "
                                <div class='inputwrapper login-alert'>
                                    <div class='alert alert-success'>Update was successful you can now login. <br /><a href='<?=BASE_URL ?>/'>Go to login</a></div>
                                </div>";
                        }
                    ?>
                    <?php if($success !== true) { ?>
                    <form action="<?=BASE_URL ?>/update" method="post" class="registrationForm">
                        <input type="hidden" name="updateDB" value="1" />
                       <p><input type="submit" name="updateAction" class="btn btn-primary" value="Update Now!" onClick="this.form.submit(); this.disabled=true; this.value='Updating…'; "/></p>
                    </form>
                    <?php } ?>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
