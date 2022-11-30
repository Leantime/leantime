<?php defined('RESTRICTED') or die('Restricted access'); ?>

<?php
$currentLink = $this->get('current');
$module = '';
$action = '';

if(is_array($currentLink)) {

    $module = $currentLink[0]??'';
    $action = $currentLink[1]??'';
}

?>

<?php $this->dispatchTplEvent('beforeHeadMenu'); ?>
<ul class="headmenu">
    <?php $this->dispatchTplEvent('afterHeadMenuOpen'); ?>
    <li>
        <a href='<?=BASE_URL ?>/dashboard/home' <?php if($module == 'dashboard' && $action=='home') echo"class='active'"; ?>>
            <?=$this->__("menu.home")?>
        </a>
    </li>
    <?php if($login::userIsAtLeast($roles::$editor, true)) { ?>
        <li>
            <a href='<?=BASE_URL ?>/timesheets/showMy/' <?php if($module == 'timesheets' && $action=='showMy') echo"class='active'"; ?>>
                <?=$this->__("menu.my_timesheets")?>
            </a>
        </li>

        <li>
            <a href='<?=BASE_URL ?>/calendar/showMyCalendar' <?php if($module == 'calendar' && $action=='showMyCalendar') echo"class='active'"; ?>>
                <?=$this->__("menu.my_calendar")?>
            </a>
        </li>

        <?php if($this->get('onTheClock') !== false){

            echo "<li class='timerHeadMenu' id='timerHeadMenu'>";
                echo"<a href='javascript:void(0);' class='dropdown-toggle' data-toggle='dropdown'>
                    ".sprintf($this->__('text.timer_on_todo'), $this->get('onTheClock')['totalTime'], substr($this->escape($this->get('onTheClock')['headline']), 0, 10))."
                </a>";

                ?>
                <ul class="dropdown-menu">
                    <li>
                        <a href='<?=BASE_URL ?>/tickets/showTicket/<?php $this->e($this->get('onTheClock')['id']); ?>'>
                            <?=$this->__("links.view_todo")?>
                        </a>
                    </li>
                    <li>
                        <a href='javascript:void(0);' class="punchOut" data-value="<?php $this->e($this->get('onTheClock')['id']); ?>">
                            <?=$this->__("links.stop_timer")?>
                        </a>
                    </li>
                </ul>
            </li>

        <?php } ?>

    <?php } ?>
    <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
    <li class="appsDropdown">
        <a href='javascript:void(0);' class="dropdown-toggle profileHandler" data-toggle="dropdown" >
            <img src="<?=BASE_URL ?>/images/svg/apps-grid-icon.svg" style="width:13px; vertical-align: middle;">
                &nbsp;<i class="fas fa-caret-down"></i>
        </a>

        <ul class="dropdown-menu">


                <li class="nav-header"><?=$this->__("header.management")?></li>
                <li>
                        <a href="<?=BASE_URL ?>/timesheets/showAll"><?=$this->__("menu.all_timesheets") ?></a>
                    </li>


                <li <?php if($module == 'projects') echo" class='active' "; ?>>
                    <a href='<?=BASE_URL ?>/projects/showAll/'>
                        <?=$this->__("menu.all_projects")?>
                    </a>
                </li>

                <?php if ($login::userIsAtLeast($roles::$admin)) { ?>
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

                <?php if ($login::userIsAtLeast($roles::$owner)) { ?>

                    <li class="nav-header border"><?=$this->__("label.administration")?></li>

                    <li <?php if($module == 'setting') echo" class='active' "; ?>>
                        <a href='<?=BASE_URL ?>/setting/editCompanySettings/'>
                            <?=$this->__("menu.company_settings")?>
                        </a>
                    </li>
                <?php } ?>

            <?php } ?>
        </ul>
    </li>
    <?php } ?>
    <?php $this->dispatchTplEvent('beforeHeadMenuClose'); ?>
</ul>
<?php $this->dispatchTplEvent('afterHeadMenu'); ?>
