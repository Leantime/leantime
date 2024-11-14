<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<div class="showDialogOnLoad" style="display:none;">

    <h4 class="widgettitle title-light"><i class="fa fa-arrow-circle-o-right"></i>
        <?php echo $tpl->__('headlines.import_ldap_users') ?>
    </h4>

    <?php echo $tpl->displayNotification(); ?>

    <?php if ($tpl->get('confirmUsers')) { ?>
        <form class="importModal userImportModal" method="post" action="<?= BASE_URL ?>/users/import">
            <?php foreach ($tpl->get('allLdapUsers') as $user) { ?>
                <input type="checkbox" value="<?php $tpl->e($user['user']); ?>" id="<?php $tpl->e($user['user']) ?>" name="users[]" checked="checked"/>
                <label for="<?php $tpl->e($user['user']) ?>" style="display:inline;"><?php $tpl->e($user['user']) ?> - <?php $tpl->e($user['firstname']) ?>,  <?php $tpl->e($user['lastname']) ?><br />
            <?php } ?>
            <br />
            <input type="hidden" name="importSubmit" value="1"/>
            <input type="submit" value="<?php echo $tpl->__('buttons.import') ?>" />
        </form>

    <?php } else { ?>
        <form class="importModal userImportModal" method="post" action="<?= BASE_URL ?>/users/import">
            <label><?= $tpl->__('label.please_enter_password') ?> </label>
            <input type="password" name="password" />
            <input type="hidden" name="pwSubmit" value="1"/>
            <input type="submit" value="<?php echo $tpl->__('buttons.find_users') ?>" />
        </form>

    <?php } ?>

</div>
