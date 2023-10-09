<?php
$menuRepo = app()->make(\Leantime\Domain\Menu\Repositories\Menu::class);
$_SESSION['menuState'] = $menuRepo->getSubmenuState("mainMenu");
if (!$_SESSION['menuState']) {
    $_SESSION['menuState'] = "open";
}
?>

<!DOCTYPE html>
<html dir="<?php echo $this->language->__("language.direction"); ?>" lang="<?php echo $this->language->__("language.code"); ?>">
<head>
    <?php echo $this->frontcontroller->includeAction('pageparts.header'); ?>
</head>

<body>

<div class="mainwrapper menu<?=$_SESSION['menuState']; ?> ">

    <div class="leftpanel">

        <a class="barmenu"  href="javascript:void(0);">
            <span class="fa fa-bars"></span>
        </a>

        <div class="logo">

            <a href="<?=BASE_URL ?>" style="background-image:url('<?php echo str_replace('http:', '', htmlentities($_SESSION["companysettings.logoPath"])); ?>')">&nbsp;</a>
        </div>

        <div class="leftmenu">

            <?php echo $this->frontcontroller->includeAction('menu.menu'); ?>

        </div><!--leftmenu-->

    </div><!-- leftpanel -->

    <div class="header">

        <div class="headerinner">

            <?php echo $this->frontcontroller->includeAction('menu.headMenu'); ?>

        </div>
    </div>

    <div class="rightpanel" style="position: relative;">

        <!--###MAINCONTENT###-->
        <?php echo $this->frontcontroller->includeAction('pageparts.footer'); ?>

    </div><!--rightpanel-->

</div><!--mainwrapper-->
<?php echo $this->frontcontroller->includeAction('pageparts.pageBottom'); ?>
