<?php

$config = new leantime\core\config();
$settings = new leantime\core\appSettings();
$install = new leantime\core\install($config, $settings);

if ($install->checkIfInstalled()) {
    header('Location: '. BASE_URL);
    exit;
}

?>
<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontController->includeAction('general.header'); ?>

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
        <a href="<?=BASE_URL?>" style="background-image:url(<?php echo htmlentities($_SESSION["companysettings.logoPath"]);?>)">&nbsp;</a>
    </div>

</div>

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft" style="background:#<?php echo $_SESSION["companysettings.mainColor"]; ?>" >
        <div class="row">
            <div class="col-md-5">

            </div>
            <div class="col-md-6" style="position:relative;">
                <a href="<?=BASE_URL ?>/" target="_blank"><img src="<?php echo htmlentities($_SESSION["companysettings.logoPath"]); ?>" /></a>
                <h1 style="font-family:Exo;  font-size: 64px; padding-left:15px; font-weight:400;"><?php echo $this->language->__("headlines.drive_impact"); ?></h1>
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
                    <h1><?php echo $this->language->__("headlines.installation"); ?></h1>
                    <p><?php echo $this->language->__("text.this_script_will_set_up_leantime"); ?></p><br />
                </div>
            </div>
            <div class="regcontent"  id="login" style="margin-left: 90px;">

                <?php
                $error = false;
                $values = array(
                    'email'			=>"",
                    'password'		=>"",
                    'firstname'		=>"",
                    'lastname'		=>""
                );

                if(isset($_POST['install'])) {

                        $values = array(
                            'email'			=>($_POST['email']),
                            'password'		=>$_POST['password'],
                            'firstname'		=>($_POST['firstname']),
                            'lastname'		=>($_POST['lastname']),
                            'company'		=>($_POST['company'])
                        );

                        if (isset($_POST['email']) == false || $_POST['email'] == '') {
                            $error = $this->language->__("notification.enter_email");
                        } else if (isset($_POST['password']) == false || $_POST['password'] == '') {
                            $error = $this->language->__("notification.enter_password");
                        } else if (isset($_POST['firstname']) == false || $_POST['firstname'] == '') {
                            $error = $this->language->__("notification.enter_firstname");
                        } else if (isset($_POST['lastname']) == false || $_POST['lastname'] == '') {
                            $error = $this->language->__("notification.enter_lastname");
                        } else if (isset($_POST['company']) == false || $_POST['company'] == '') {
                            $error = $this->language->__("notification.enter_company");
                        } else {

                            $values['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

                            $dbSetupResults = $install->setupDB($values);
                            if($dbSetupResults === true) {

                                echo "<div class='inputwrapper login-alert'>
                                    <div class='alert alert-success' style='padding:10px;'>
                                        ".sprintf($this->language->__("notifications.installation_success"),BASE_URL)."
                                    </div>
                               </div>";
                            }else{
                                echo "<div class='inputwrapper login-alert'>
                                    <div class='alert alert-error' style='padding:10px;'>
                                        ".sprintf($this->language->__("notifications.installation_success"),$dbSetupResults)."   
                                    </div>
                               </div>";
                            }
                        }
                }

                ?>
                <?php
                    if($error !== false){
                        echo "
                            <div class='inputwrapper login-alert'>
                                <div class='alert alert-error'>".$error."</div>
                            </div>";
                    }
                ?>
                    <form action="<?=BASE_URL ?>/install" method="post" class="registrationForm">
                        <h3 class="subtitle"><?=$this->language->__("subtitles.login_info");?></h3>
                        <input type="email" name="email" class="form-control" placeholder="<?=$this->language->__("label.email");?>" value=""/><br />
                        <input type="password" name="password" class="form-control" placeholder="<?=$this->language->__("label.password");?>" />
                        <br /><br />
                        <h3 class="subtitle"><?=$this->language->__("subtitles.user_info");?></h3>
                        <input type="text" name="firstname" class="form-control" placeholder="<?=$this->language->__("label.firstname");?>" value=""/><br />
                        <input type="text" name="lastname" class="form-control" placeholder="<?=$this->language->__("label.lastname");?>" value=""/>
                        <input type="text" name="company" class="form-control" placeholder="<?=$this->language->__("label.company_name");?>" value=""/>
                        <br /><br />
                        <input type="hidden" name="install" value="Install" />
                        <p><input type="submit" name="installAction" class="btn btn-primary" value="<?=$this->language->__("buttons.install");?>" onClick="this.form.submit(); this.disabled=true; this.value='<?=$this->language->__("buttons.install");?>'; "/></p>

                    </form>
            </div>
        </div>
    </div>

    </div>
</div>

</body>
</html>
