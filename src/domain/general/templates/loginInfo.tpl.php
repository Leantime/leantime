<?php defined('RESTRICTED') or die('Restricted access'); ?>

<div class="userinfo">

    <a href='<?=BASE_URL ?>/users/editOwn/' class="dropdown-toggle profileHandler" data-toggle="dropdown">
        <img src="<?php echo $this->get('profilePicture'); ?>" alt="Picture of <?php $this->get('userName'); ?>" class="profilePicture"/><?php echo $this->get('userName'); ?>

        <i class="fa fa-caret-down" aria-hidden="true"></i>
    </a>
    <ul class="dropdown-menu">

        <li>
            <a href='<?=BASE_URL ?>/users/editOwn/'>
                <span class="fa fa-user"></span> My Profile
            </a>
        </li>
        <?php if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'manager' ) { ?>
        <li class="nav-header border">Administration</li>
            <li <?php if($module == 'projects') echo" class='active' "; ?>>
                <?php echo $this->displayLink('projects.showAll', '<span class="fa fa-suitcase"></span>'.$language->lang_echo('All Projects', false).'') ?>
            </li>
            <?php if ($_SESSION['userdata']['role'] == 'admin'){ ?>
                <li <?php if($module == 'clients') echo" class='active' "; ?>>
                    <?php echo $this->displayLink('clients.showAll', '<span class="fa fa-address-book"></span>'.$language->lang_echo('All Clients/Products', false).'') ?>
                </li>
                <li <?php if($module == 'users') echo" class='active' "; ?>>
                    <?php echo $this->displayLink('users.showAll', '<span class="fa fa-users"></span>'.$language->lang_echo('User Management', false).'') ?>
                </li>
                <li <?php if($module == 'setting') echo" class='active' "; ?>>
                    <?php echo $this->displayLink('setting.editCompanySettings', '<span class="fa fa-cogs"></span>'.$language->lang_echo('Company Settings', false).'') ?>
                </li>
            <?php } ?>
        <?php } ?>
        <li class="nav-header border">Help & Support</li>
        <li>
            <a href='javascript:void(0);'
               onclick="leantime.helperController.showHelperModal('<?php echo $this->get('modal'); ?>');">
                <span class="fa fa-map-signs"></span> Show me around
            </a>
        </li>
        <li>
            <a href='http://help.leantime.io' target="_blank">
                <span class="fa fa-question"></span> Knowledge Base
            </a>
        </li>
        <li>
            <a href='https://leantime.io/contact-us' target="_blank">
                <span class="fa fa-phone"></span> Contact Us
            </a>
        </li>

        <li class="border">
            <a href='<?=BASE_URL ?>/index.php?logout=1'>
                <i class="fa fa-sign-out-alt" aria-hidden="true"></i> Sign Out
            </a>
        </li>
    </ul>


</div>
