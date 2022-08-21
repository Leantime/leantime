<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontcontroller->includeAction('general.header'); ?>
</head>

<body>
<div class="mainwrapper">

    <div class="leftpanel" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:-240px;'; ?>">

        <a class="barmenu <?php if(!isset($_SESSION['menuState']) || $_SESSION['menuState'] == 'open') echo 'open'; ?>" href="javascript:void(0);">
            <span class="fa fa-bars"></span>
        </a>

        <div class="logo" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:-260px;'; ?>">

            <a href="<?=BASE_URL ?>" style="background-image:url('<?php echo htmlentities($_SESSION["companysettings.logoPath"]); ?>')">&nbsp;</a>
        </div>

        <div class="leftmenu">

            <?php echo $this->frontcontroller->includeAction('general.menu'); ?>

        </div><!--leftmenu-->

    </div><!-- leftpanel -->

    <div class="header" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px; width:100%;'; ?>">



        <div class="headerinner" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px;'; ?>">


            <div class="userloggedinfo">
                <?php echo $this->frontcontroller->includeAction('general.loginInfo'); ?>
            </div>

            <?php echo $this->frontcontroller->includeAction('general.headMenu'); ?>

        </div>
    </div>

    <div class="rightpanel" style="position: relative; <?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px;'; ?>">

        <!--###MAINCONTENT###-->
        <?php echo $this->frontcontroller->includeAction('general.footer'); ?>

    </div><!--rightpanel-->

</div><!--mainwrapper-->

<?php echo $this->frontcontroller->includeAction('general.pageBottom'); ?>

