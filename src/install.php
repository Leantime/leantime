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
                    <h1>Install Database</h1>
                    <p>This script will set up your database and create an administrator account</p><br />
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

                        if($install->checkIfInstalled() === false) {

                            if (isset($_POST['email']) == false || $_POST['email'] == '') {
                                $error = "Please enter an email address";
                            } else if (isset($_POST['password']) == false || $_POST['password'] == '') {
                                $error = "Please enter a password";
                            } else if (isset($_POST['firstname']) == false || $_POST['firstname'] == '') {
                                $error = "Please enter a firstname";
                            } else if (isset($_POST['lastname']) == false || $_POST['lastname'] == '') {
                                $error = "Please enter a lastname";
                            } else if (isset($_POST['company']) == false || $_POST['company'] == '') {
                                $error = "Please enter a company";
                            } else {

                                $values['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

                                $dbSetupResults = $install->setupDB($values);
                                if($dbSetupResults === true) {

                                    echo "<div class='inputwrapper login-alert'>
                                        <div class='alert alert-success' style='padding:10px;'>
                                        The installation was successful<br />
                                        <br />
                                        You can now login using your workspace URL:
                                        <a href='" . BASE_URL . "'>" . BASE_URL . "</a>
                                        </div>
                                   </div>";
                                }else{
                                    echo "<div class='inputwrapper login-alert'>
                                        <div class='alert alert-error' style='padding:10px;'>
                                        Something went wrong
                                        <br />
                                        ".$dbSetupResults."<br /><br />
                                        Before continuing you should delete all tables from your database.                                            
                                        </div>
                                   </div>";
                                }
                            }

                        }else{

                            $error = "Database already installed. Please login";

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
                        <h3 class="subtitle">Login Info</h3>
                        <input type="email" name="email" class="form-control" placeholder="Email Address" value=""/><br />
                        <input type="password" name="password" class="form-control" placeholder="Password" />
                        <br /><br />
                        <h3 class="subtitle">User Info</h3>
                        <input type="text" name="firstname" class="form-control" placeholder="Firstname" value=""/><br />
                        <input type="text" name="lastname" class="form-control" placeholder="Lastname" value=""/>
                        <input type="text" name="company" class="form-control" placeholder="Company" value=""/>
                        <br /><br />
                        <input type="hidden" name="install" value="Install" />
                        <p><input type="submit" name="installAction" class="btn btn-primary" value="Install" onClick="this.form.submit(); this.disabled=true; this.value='Installing…'; "/></p>

                    </form>

            </div>
        </div>
    </div>

    </div>
</div>

</body>
</html>
