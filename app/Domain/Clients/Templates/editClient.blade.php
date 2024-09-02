<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$values = $tpl->get('values');
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa <?php echo $tpl->getModulePicture() ?>"></span></div>
    <div class="pagetitle">
        <h5>Administration</h5>
        <h1><?php echo $tpl->__('EDIT_CLIENT'); ?></h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification() ?>

        <form action="" method="post" class="stdform">

            <div class="widget">
            <h4 class="widgettitle"><?php echo $tpl->__('OVERVIEW'); ?></h4>
            <div class="widgetcontent">

                <label for="name"><?php echo $tpl->__('NAME') ?></label>
                <input type="text" name="name" id="name" value="<?php echo $values['name'] ?>" /><br />

                <label for="email"><?php echo $tpl->__('EMAIL') ?></label>
                <input type="text" name="email" id="email" value="<?php echo $values['email'] ?>" /><br />

                <label for="internet"><?php echo $tpl->__('URL') ?></label> <input
                    type="text" name="internet" id="internet"
                    value="<?php echo $values['internet'] ?>" /><br />

                <label for="street"><?php echo $tpl->__('STREET') ?></label> <input
                    type="text" name="street" id="street"
                    value="<?php echo $values['street'] ?>" /><br />

                <label for="zip"><?php echo $tpl->__('ZIP') ?></label> <input type="text"
                    name="zip" id="zip" value="<?php echo $values['zip'] ?>" /><br />

                <label for="city"><?php echo $tpl->__('CITY') ?></label> <input type="text"
                    name="city" id="city" value="<?php echo $values['city'] ?>" /><br />

                <label for="state"><?php echo $tpl->__('STATE') ?></label> <input
                    type="text" name="state" id="state"
                    value="<?php echo $values['state'] ?>" /><br />

                <label for="country"><?php echo $tpl->__('COUNTRY') ?></label> <input
                    type="text" name="country" id="country"
                    value="<?php echo $values['country'] ?>" /><br />

                <label for="phone"><?php echo $tpl->__('PHONE') ?></label> <input
                    type="text" name="phone" id="phone"
                    value="<?php echo $values['phone'] ?>" /><br />

                <input type="submit" name="save" id="save"
                    value="<?php echo $tpl->__('SAVE') ?>" class="button" />

                </div>
            </div>

        </form>

    </div>
</div>
