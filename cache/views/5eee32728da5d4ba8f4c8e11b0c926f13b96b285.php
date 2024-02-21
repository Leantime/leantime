<?php $tpl->dispatchTplEvent('beforeUserinfoMenuOpen'); ?>

<div class="userinfo">
    <?php $tpl->dispatchTplEvent('afterUserinfoMenuOpen'); ?>
    <?php if(isset($_SESSION["companysettings.logoPath"]) && $_SESSION["companysettings.logoPath"] !== false): ?>
        <a href='<?php echo e(BASE_URL); ?>/users/editOwn/' class="dropdown-toggle profileHandler includeLogo" data-toggle="dropdown">
            <img src="<?php echo e(BASE_URL); ?>/api/users?profileImage=<?php echo e($user['id'] ?? -1); ?>&v=<?php echo e(format($user['modified'] ?? -1)->timestamp()); ?>" class="profilePicture"/>
            <img src="<?php echo e($_SESSION["companysettings.logoPath"]); ?>" class="logo"/>
            <i class="fa fa-caret-down" aria-hidden="true"></i>
        </a>
    <?php else: ?>
        <a href='<?php echo e(BASE_URL); ?>/users/editOwn/' class="dropdown-toggle profileHandler" data-toggle="dropdown">
            <img src="<?php echo e(BASE_URL); ?>/api/users?profileImage=<?php echo e($user['id'] ?? -1); ?>&v=<?php echo e(format($user['modified'] ?? -1)->timestamp()); ?>" class="profilePicture"/>
            <i class="fa fa-caret-down" aria-hidden="true"></i>
        </a>
    <?php endif; ?>
    <ul class="dropdown-menu">
        <?php $tpl->dispatchTplEvent('afterUserinfoDropdownMenuOpen'); ?>
        <li>
            <a href='<?php echo e(BASE_URL); ?>/users/editOwn/'>
                <?php echo __("menu.my_profile"); ?>

            </a>
        </li>
        <li>
            <a href='<?php echo e(BASE_URL); ?>/users/editOwn#theme'>
                <?php echo __("menu.theme"); ?>

            </a>
        </li>
        <li>
            <a href='<?php echo e(BASE_URL); ?>/users/editOwn#settings'>
                <?php echo __("menu.settings"); ?>

            </a>
        </li>

        <li class="nav-header border"><?php echo __("menu.help_support"); ?></li>
        <li>
            <a href='javascript:void(0);'
               onclick="leantime.helperController.showHelperModal('<?php echo e($modal); ?>', 300, 500);">
                <?php echo __("menu.what_is_this_page"); ?>

            </a>
        </li>
        <li>
            <a href='https://leantime.io/knowledge-base' target="_blank">
                <?php echo __("menu.knowledge_base"); ?>

            </a>
        </li>
        <li>
            <a href='https://discord.gg/4zMzJtAq9z' target="_blank">
                <?php echo __("menu.community"); ?>

            </a>
        </li>
        <li>
            <a href='https://leantime.io/contact-us' target="_blank">
                <?php echo __("menu.contact_us"); ?>

            </a>
        </li>
        <li class="border">
            <a href='<?php echo e(BASE_URL); ?>/auth/logout'>
                <?php echo __("menu.sign_out"); ?>

            </a>
        </li>
        <?php $tpl->dispatchTplEvent('beforeUserinfoDropdownMenuClose'); ?>
    </ul>
   <?php $tpl->dispatchTplEvent('beforeUserinfoMenuClose'); ?>
</div>
<?php $tpl->dispatchTplEvent('afterUserinfoMenuClose'); ?>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Auth/Templates/partials/loginInfo.blade.php ENDPATH**/ ?>