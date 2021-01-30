<?php

$config = new leantime\core\config();
$settings = new leantime\core\settings();
$install = new leantime\core\install($config, $settings);

?>
<!DOCTYPE html>
<html dir="<?php echo $language->__("language.direction"); ?>" lang="<?php echo $language->__("language.code"); ?>">
<head>
    <?php echo $frontController->includeAction('general.header'); ?>

    <link rel="stylesheet" href="<?=BASE_URL?>/css/main.css?v=<?php echo $settings->appVersion; ?>"/>
    <link rel="stylesheet" href="<?=BASE_URL?>/css/style.default.css?v=<?php echo $settings->appVersion; ?>" type="text/css" />
    <link rel="stylesheet" href="<?=BASE_URL?>/css/style.custom.php?color=<?php echo htmlentities($_SESSION["companysettings.mainColor"]); ?>&v=<?php echo $settings->appVersion; ?>" type="text/css" />

    <script src="<?=BASE_URL?>/js/compiled-base-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>

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
        <a href="<?=BASE_URL ?>/" style="background-image:url(<?php echo htmlentities($_SESSION["companysettings.logoPath"]);?>)">&nbsp;</a>
    </div>

</div>

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft" style="background:#<?php echo $_SESSION["companysettings.mainColor"]; ?>" >
        <div class="row">
            <div class="col-md-5">

            </div>
            <div class="col-md-6" style="position:relative;">
                <a href="<?=BASE_URL ?>/" target="_blank"><img src="<?php echo htmlentities($_SESSION["companysettings.logoPath"]); ?>" /></a>
                <h1 style="font-family:Exo;  font-size: 64px; padding-left:15px; font-weight:400;"><?php echo $language->__("headlines.drive_impact"); ?></h1>
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
                        <h5><?php echo htmlentities($_SESSION["companysettings.sitename"]); ?></h5>
                        <h1><?php echo $language->__("headlines.update_database"); ?></h1>
                        <p><?php echo $language->__("text.new_db_version"); ?></p><br />
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
                                    <div class='alert alert-success'>".sprintf($language->__("text.update_was_successful"),BASE_URL)."</div>
                                </div>";
                        }
                    ?>
                    <?php if($success !== true) { ?>
                    <form action="<?=BASE_URL ?>/update" method="post" class="registrationForm">
                        <input type="hidden" name="updateDB" value="1" />
                       <p><input type="submit" name="updateAction" class="btn btn-primary" value="<?=$language->__("buttons.update_now")?>" onClick="this.form.submit(); this.disabled=true; this.value='Updating…'; "/></p>
                    </form>
                    <?php } ?>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
