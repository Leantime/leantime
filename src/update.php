<?php

$config = new leantime\core\config();
$settings = new leantime\core\appSettings();
$install = new leantime\core\install($config, $settings);


?>
<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontController->includeAction('general.header'); ?>

    <link rel="stylesheet" href="<?=BASE_URL?>/css/vars.css.php?color1=<?php echo htmlentities($_SESSION["companysettings.primarycolor"]) ?>&color2=<?php echo htmlentities($_SESSION["companysettings.secondarycolor"]) ?>&v=<?php echo $settings->appVersion; ?>"/>
    <link rel="stylesheet" href="<?=BASE_URL?>/css/main.css?v=<?php echo $settings->appVersion; ?>"/>
    <link rel="stylesheet" href="<?=BASE_URL?>/css/overwrites.css" type="text/css"/>

    <script src="<?=BASE_URL?>/api/i18n"></script>

    <!-- libs -->
    <script src="<?=BASE_URL?>/js/compiled-base-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>
    <script src="<?=BASE_URL?>/js/compiled-extended-libs.min.js?v=<?php echo $settings->appVersion; ?>"></script>

    <!-- app -->
    <script src="<?=BASE_URL?>/js/compiled-app.min.js?v=<?php echo $settings->appVersion; ?>"></script>

    <script type="text/javascript">
        jQuery(document).ready(function(){

            if(jQuery('.login-alert .alert').text() != ''){
                jQuery('.login-alert').fadeIn();
            }

        });
    </script>
</head>


<body class="loginpage" style="height:100%;">

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft">
        <div class="row">
            <div class="col-md-12" style="position:relative;">
                <h1 class="mainWelcome"><?php echo $language->__("headlines.update_database"); ?></h1>
                <span class="iq-objects-04 iq-fadebounce">
				    <span class="iq-round"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12 regRight">

        <div class="regpanel">
            <div class="regpanelinner">

                <a href="<?=BASE_URL ?>" target="_blank"><img src="<?php echo htmlentities($_SESSION["companysettings.logoPath"]); ?>" /></a>

                <div class="pageheader">
                    <div class="pagetitle">
                        <h1><?php echo $this->language->__("headlines.update_database"); ?></h1>
                    </div>
                </div>
                <div class="regcontent"  id="login" style="margin-left: 90px;">
                    <p><?php echo $this->language->__("text.new_db_version"); ?></p><br />

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
                                    <div class='alert alert-success'>".sprintf($this->language->__("text.update_was_successful"),BASE_URL)."</div>
                                </div>";
                        }
                    ?>
                    <?php if($success !== true) { ?>
                    <form action="<?=BASE_URL ?>/update" method="post" class="registrationForm">
                        <input type="hidden" name="updateDB" value="1" />
                       <p><input type="submit" name="updateAction" class="btn btn-primary" value="<?=$this->language->__("buttons.update_now")?>" onClick="this.form.submit(); this.disabled=true; this.value='Updating…'; "/></p>
                    </form>
                    <?php } ?>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
