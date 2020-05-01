<?php defined('RESTRICTED') or die('Restricted access'); ?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-lock"></span></div>
    <div class="pagetitle">
        <h1><?php echo $this->__('label.twoFA'); ?></h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="row-fluid">
            <div class="span12">

                <div class="widget">
                    <h4 class="widgettitle"><?php echo $this->__('label.twoFA_setup'); ?></h4>
                    <div class="widgetcontent">
                        <?php if(!$this->get('twoFAEnabled')) { ?>
                            <h5>1. <?php echo $this->__('text.twoFA_qr'); ?></h5>
                            <img src="<?php echo $this->get("qrData"); ?>"/>

                            <form action="" method="post" class='stdform'>
                                <h5>2. <?php echo $this->__('text.twoFA_verify_code'); ?></h5>
                                <p>
                                    <span><?php echo $this->__('label.twoFACode_short'); ?>:</span>
                                    <input type="text" class="input" name="twoFACode" id="twoFACode"/><br/>
                                </p>

                                <input type="hidden" name="secret" value="<?php echo $this->get("secret"); ?>" />

                                <p class='stdformbutton'>
                                    <input type="submit" name="save" id="save"
                                           value="<?php echo $this->__('buttons.save'); ?>" class="button"/>
                                </p>
                            </form>
                        <?php } else { ?>
                            <form action="" method="post" class='stdform'>
                                <h5><?php echo $this->__('text.twoFA_already_enabled'); ?></h5>
                                <input type="hidden" name="<?=$_SESSION['formTokenName']?>" value="<?=$_SESSION['formTokenValue']?>" />
                                <p class='stdformbutton'>
                                    <input type="submit" name="disable" id="disable"
                                           value="<?php echo $this->__('buttons.remove'); ?>" class="button"/>
                                    <a href="<?=BASE_URL?>/users/editOwn" class="btn"><?php echo $this->__('buttons.back'); ?></a>
                                </p>
                            </form>
                        <?php } ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
