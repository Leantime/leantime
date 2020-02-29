<?php defined('RESTRICTED') or die('Restricted access'); ?>

<ul class="headmenu">

    <?php if ($_SESSION['userdata']['role'] != "user") { ?>

        <?php if($this->get('onTheClock') !== false){
           echo "<li class='timerHeadMenu'><a href='javascript:void(0);' class='dropdown-toggle' data-toggle='dropdown'>
                <span class='head-icon fa fa-stop'></span>
                ".$this->get('onTheClock')['totalTime']."
                Timer on ".substr($this->escape($this->get('onTheClock')['headline']), 0, 10)."...
            </a>";

                ?>
            <ul class="dropdown-menu">

                <li>
                    <a href='<?=BASE_URL ?>/tickets/showTicket/<?php $this->e($this->get('onTheClock')['id']); ?>'>
                        <span class="fa fa-thumb-tack"></span> View To-Do
                    </a>
                </li>
                <li>
                    <a href='javascript:void(0);' class="punchOut" value="<?php $this->e($this->get('onTheClock')['id']); ?>">
                        <span class="fa fa-stop"></span> Stop Timer
                    </a>
                </li>
            </ul>

<?php
           echo"</li>";
        }?>
        <li>
            <a href='<?=BASE_URL ?>/timesheets/showMy/'>
                <span class="head-icon fa fa-clock-o"></span>
                <span class='headmenu-label'>My Timesheets</span>
            </a>
        </li>
    <?php } ?>

    <li>
        <a href='<?=BASE_URL ?>/calendar/showMyCalendar'>
            <span class='head-icon iconfa-calendar'></span>
            <span class='headmenu-label'>My Calendar</span>
        </a>
    </li>

    <li class="hidden-gt-sm">
        <a href='<?=BASE_URL ?>/general/logout/'>
            <span class="head-icon fa fa-sign-out-alt"></span>
        </a>
    </li>

</ul><!--headmenu-->
