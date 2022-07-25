<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontController->includeAction('general.header'); ?>
</head>

<script type="text/javascript">
    jQuery(document).ready(function(){
        
        if(jQuery('.login-alert .alert-error').text() != ''){
            jQuery('.login-error').fadeIn();
        }
        
        if(jQuery('.login-alert .alert-success').text() != ''){
            jQuery('.login-success').fadeIn();
        }

    });
</script>

<body class="loginpage" style="height:100%;">

<div class="header hidden-gt-sm">

    <div class="logo" style="margin-left:0px;">
        <a href="<?=BASE_URL ?>" style="background-image:url(<?php echo htmlentities($_SESSION["companysettings.logoPath"]);?>)">&nbsp;</a>
    </div>

</div>

<body class="loginpage" style="height:100%;">

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft">
        <div class="row">
            <div class="col-md-12" style="position:relative;">
                <h1 class="mainWelcome"><?php echo $language->__("headlines.welcome_back"); ?></h1>
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
                         <h1><?php echo $this->language->__("headlines.reset_password"); ?></h1>
                    </div>
                </div>
                <div class="regcontent">
        
                    <form id="resetPassword" action="" method="post">
            
                        <div class="inputwrapper login-alert login-error">
                            <div class="alert alert-error"><?php echo $login->error;?></div>
                        </div>
                        <div class="inputwrapper login-alert login-success">
                            <div class="alert alert-success"><?php echo $login->success;?></div>
                        </div>

                        <?php
                        if((isset($_GET["hash"]) === true && $login->validateResetLink()) || $login->resetInProgress === true) { ?>

                            <p><?php echo $this->language->__("text.enter_new_password"); ?><br /><br /></p>
                            <div class="">
                                <input type="password" name="password" id="password" placeholder="<?php echo $this->language->__("input.placeholders.enter_new_password"); ?>" />
                            </div>
                            <div class=" ">
                                <input type="password" name="password2" id="password2" placeholder="<?php echo $this->language->__("input.placeholders.confirm_password"); ?>" />
                            </div>
                            <div class="">
                                <div class="forgotPwContainer">
                                    <a href="<?=BASE_URL ?>/" class="forgotPw"><?php echo $this->language->__("links.back_to_login"); ?></a>
                                </div>
                                <input type="submit" name="resetPassword" value="<?php echo $this->language->__("buttons.reset_password"); ?>" />
                            </div>

                        <?php }else{ ?>
                            <p><?php echo $this->language->__("text.enter_email_address_to_reset"); ?><br /><br /></p>
                            <div class="">
                                <input type="text" name="username" id="username" placeholder="<?php echo $this->language->__("input.placeholders.enter_email"); ?>" />
                            </div>

                            <div class="">
                                <div class="forgotPwContainer">
                                    <a href="<?=BASE_URL ?>/" class="forgotPw"><?php echo $this->language->__("links.back_to_login"); ?></a>
                                </div>

                                <input type="submit" name="resetPassword" value="<?php echo $this->language->__("buttons.reset_password"); ?>" />
                            </div>
                        <?php } ?>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
