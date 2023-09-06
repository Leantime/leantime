<?php
    defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
?>

<?php $tpl->dispatchTplEvent('beforeUserinfoMenuOpen'); ?>
<div class="userinfo">
    <?php $tpl->dispatchTplEvent('afterUserinfoMenuOpen'); ?>
    <a href='<?=BASE_URL ?>/users/editOwn/' class="dropdown-toggle profileHandler" data-toggle="dropdown">
        <img src="<?=BASE_URL ?>/api/users?profileImage=<?php echo $tpl->get('userId'); ?>" class="profilePicture"/>
        <i class="fa fa-caret-down" aria-hidden="true"></i>
    </a>
    <ul class="dropdown-menu">
        <?php $tpl->dispatchTplEvent('afterUserinfoDropdownMenuOpen'); ?>
        <li>
            <a href='<?=BASE_URL ?>/users/editOwn/'>
                <?=$tpl->__("menu.my_profile")?>
            </a>
        </li>

        <li class="nav-header border"><?=$tpl->__("menu.help_support")?></li>
        <li>
            <a href='javascript:void(0);'
               onclick="leantime.helperController.showHelperModal('<?php echo $tpl->get('modal'); ?>', 300, 500);">
                <?=$tpl->__("menu.what_is_this_page")?>
            </a>
        </li>
        <li>
            <a href='https://docs.leantime.io' target="_blank">
                <?=$tpl->__("menu.knowledge_base")?>
            </a>
        </li>
        <li>
            <a href='https://community.leantime.io' target="_blank">
                <?=$tpl->__("menu.community")?>
            </a>
        </li>
        <li>
            <a href='https://leantime.io/contact-us' target="_blank">
                <?=$tpl->__("menu.contact_us")?>
            </a>
        </li>
        <li class="border">
            <a href='<?=BASE_URL ?>/auth/logout'>
                <?=$tpl->__("menu.sign_out")?>
            </a>
        </li>
        <?php $tpl->dispatchTplEvent('beforeUserinfoDropdownMenuClose'); ?>
    </ul>
    <?php $tpl->dispatchTplEvent('beforeUserinfoMenuClose'); ?>
</div>
<?php $tpl->dispatchTplEvent('afterUserinfoMenuClose'); ?>
