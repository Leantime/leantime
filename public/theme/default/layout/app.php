<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontcontroller->includeAction('pageparts.header'); ?>
</head>

<body>
<div class="mainwrapper">

    <div class="leftpanel" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:-240px;'; ?>">

        <a class="barmenu <?php if(!isset($_SESSION['menuState']) || $_SESSION['menuState'] == 'open') echo 'open'; ?>" href="javascript:void(0);">
            <span class="fa fa-bars"></span>
        </a>

        <div class="logo" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:-260px;'; ?>">

            <a href="<?=BASE_URL ?>" style="background-image:url('<?php echo str_replace('http:','',htmlentities($_SESSION["companysettings.logoPath"])); ?>')">&nbsp;</a>
        </div>

        <div class="leftmenu">

            <?php echo $this->frontcontroller->includeAction('menu.menu'); ?>

        </div><!--leftmenu-->

    </div><!-- leftpanel -->

    <div class="header" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px; width:100%;'; ?>">



        <div class="headerinner" style="<?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px;'; ?>">

            <?php echo $this->frontcontroller->includeAction('menu.headMenu'); ?>

        </div>
    </div>

    <div class="rightpanel" style="position: relative; <?php if(isset($_SESSION['menuState']) && $_SESSION['menuState'] == 'closed') echo 'margin-left:0px;'; ?>">

        <!--###MAINCONTENT###-->
        <?php echo $this->frontcontroller->includeAction('pageparts.footer'); ?>

    </div><!--rightpanel-->

</div><!--mainwrapper-->

<?php echo $this->frontcontroller->includeAction('pageparts.pageBottom'); ?>

