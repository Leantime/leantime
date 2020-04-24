<?php defined('RESTRICTED') or die('Restricted access'); ?>

<div class="userinfo">

    <a href='<?=BASE_URL ?>/users/editOwn/' class="dropdown-toggle profileHandler" data-toggle="dropdown">
        <img src="<?php echo $this->get('profilePicture'); ?>" alt="Picture of <?php $this->get('userName'); ?>" class="profilePicture"/>
        <?php echo $this->get('userName'); ?>
        <i class="fa fa-caret-down" aria-hidden="true"></i>
    </a>
    <ul class="dropdown-menu">
        <li>
            <a href='<?=BASE_URL ?>/users/editOwn/'>
                <?=$this->__("menu.my_profile")?>
            </a>
        </li>
        <?php if ($login::userIsAtLeast("clientManager")) { ?>

        <li class="nav-header border"><?=$this->__("label.administration")?></li>

            <li <?php if($module == 'projects') echo" class='active' "; ?>>
                <a href='<?=BASE_URL ?>/projects/showAll/'>
                    <?=$this->__("menu.all_projects")?>
                </a>
            </li>

            <li <?php if($module == 'clients') echo" class='active' "; ?>>
                <a href='<?=BASE_URL ?>/clients/showAll/'>
                    <?=$this->__("menu.all_clients")?>
                </a>
            </li>
            <li <?php if($module == 'users') echo" class='active' "; ?>>
                <a href='<?=BASE_URL ?>/users/showAll/'>
                    <?=$this->__("menu.all_users")?>
                </a>
            </li>

            <?php if ($login::userIsAtLeast("admin")) { ?>
                <li <?php if($module == 'setting') echo" class='active' "; ?>>
                    <a href='<?=BASE_URL ?>/setting/editCompanySettings/'>
                        <?=$this->__("menu.company_settings")?>
                    </a>
                </li>
            <?php } ?>

        <?php } ?>
        <li class="nav-header border"><?=$this->__("menu.help_support")?></li>
        <li>
            <a href='javascript:void(0);'
               onclick="leantime.helperController.showHelperModal('<?php echo $this->get('modal'); ?>');">
                <?=$this->__("menu.show_me_around")?>
            </a>
        </li>
        <li>
            <a href='http://docs.leantime.io' target="_blank">
                <?=$this->__("menu.knowledge_base")?>
            </a>
        </li>
        <li>
            <a href='https://leantime.io/contact-us' target="_blank">
                <?=$this->__("menu.contact_us")?>
            </a>
        </li>
        <li class="border">
            <a href='<?=BASE_URL ?>/index.php?logout=1'>
                <?=$this->__("menu.sign_out")?>
            </a>
        </li>
    </ul>
</div>
