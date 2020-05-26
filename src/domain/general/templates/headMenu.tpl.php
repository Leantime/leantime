<?php defined('RESTRICTED') or die('Restricted access'); ?>

<ul class="headmenu">

    <li>
        <a href='<?=BASE_URL ?>/projects/showMy'>
            <?=$this->__("menu.my_portfolio")?>
        </a>
    </li>
    <?php if ($login::userIsAtLeast("developer")) {

        if($this->get('onTheClock') !== false){
        echo "<li class='timerHeadMenu' id='timerHeadMenu'";
        echo"><a href='javascript:void(0);' class='dropdown-toggle' data-toggle='dropdown'>
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

        <li>
            <a href='<?=BASE_URL ?>/timesheets/showMy/'>
                <?=$this->__("menu.my_timesheets")?>
            </a>
        </li>
    <?php } ?>

    <li>
        <a href='<?=BASE_URL ?>/calendar/showMyCalendar'>
            <?=$this->__("menu.my_calendar")?>
        </a>
    </li>
    <li class="hidden-gt-sm">
        <a href='<?=BASE_URL ?>/general/logout/'>
            <?=$this->__("menu.sign_out_icon")?>
        </a>
    </li>

</ul>