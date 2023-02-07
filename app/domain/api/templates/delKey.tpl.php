<?php
defined('RESTRICTED') or die('Restricted access');
$user = $this->get('user');
?>

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-key"></i></div>
    <div class="pagetitle">
        <h5><?php echo $this->__('label.administration') ?></h5>
        <h1><?php echo $this->__('headlines.delete_key'); ?></h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification() ?>

        <h5 class="subtitle"><?php echo $this->__("subtitles.delete_key"); ?></h5>

            <form method="post">
                <input type="hidden" name="<?=$_SESSION['formTokenName']?>" value="<?=$_SESSION['formTokenValue']?>" />
                <p><?php echo $this->__('text.confirm_key_deletion'); ?></p><br />
                <input type="submit" value="<?php echo $this->__('buttons.yes_delete'); ?>" name="del" class="button" />
                <a class="btn btn-primary" href="<?=BASE_URL ?>/setting/editCompanySettings/#apiKeys"><?php echo $this->__('buttons.back'); ?></a>
            </form>

    </div>
</div>
