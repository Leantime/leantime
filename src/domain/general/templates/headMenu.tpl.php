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

<ul class="headmenu">

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

</ul>