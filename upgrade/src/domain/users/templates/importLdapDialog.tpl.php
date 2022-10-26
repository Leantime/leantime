<?php

?>

<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light"><i class="fa fa-arrow-circle-o-right"></i>
        <?php echo $this->__("headlines.import_ldap_users") ?>
    </h4>

    <?php echo $this->displayNotification(); ?>

    <?php if($this->get("confirmUsers") == true) { ?>

        <form class="importModal userImportModal" method="post" action="<?=BASE_URL ?>/users/import">
            <?php foreach($this->get("allLdapUsers") as $user) { ?>
                <input type="checkbox" value="<?php $this->e($user['user']); ?>" id="<?php $this->e($user['user']) ?>" name="users[]" checked="checked"/>
                <label for="<?php $this->e($user['user']) ?>" style="display:inline;"><?php $this->e($user['user']) ?> - <?php $this->e($user['firstname']) ?>,  <?php $this->e($user['lastname']) ?><br />
            <?php } ?>
            <br />
            <input type="hidden" name="importSubmit" value="1"/>
            <input type="submit" value="<?php echo $this->__("buttons.import") ?>" />
        </form>

    <?php }else { ?>

        <form class="importModal userImportModal" method="post" action="<?=BASE_URL ?>/users/import">
            <label><?=$this->__("label.please_enter_password") ?> </label>
            <input type="password" name="password" />
            <input type="hidden" name="pwSubmit" value="1"/>
            <input type="submit" value="<?php echo $this->__("buttons.find_users") ?>" />
        </form>

    <?php } ?>

</div>
