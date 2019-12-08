<?php defined('RESTRICTED') or die('Restricted access'); ?>

<ul class="headmenu">

    <?php if ($_SESSION['userdata']['role'] != "user") {



           echo "<li class='timerHeadMenu' id='timerHeadMenu'";
            if($this->get('onTheClock') === false){ echo " style='display:none;' " ;}
           echo"><a href='javascript:void(0);' class='dropdown-toggle' data-toggle='dropdown'>
                <span class='head-icon fa fa-stop'></span>
                ".$this->get('onTheClock')['totalTime']."
                Timer on ".substr($this->escape($this->get('onTheClock')['headline']), 0, 10)."...
            </a>";

                ?>
            <ul class="dropdown-menu">

                <li>
                    <a href='/tickets/showTicket/<?php $this->e($this->get('onTheClock')['id']); ?>'>
                        <span class="fa fa-thumb-tack"></span> View To-Do
                    </a>
                </li>
                <li>
                    <a href='javascript:void(0);' class="punchOut" data-value="<?php $this->e($this->get('onTheClock')['id']); ?>">
                        <span class="fa fa-stop"></span> Stop Timer
                    </a>
                </li>
            </ul>

        </li>

        <li>
            <a href='/timesheets/showMy/'>
                <span class="head-icon fa fa-clock-o"></span>
                <span class='headmenu-label'>My Timesheets</span>
            </a>
        </li>
    <?php } ?>

    <li>
        <a href='/calendar/showMyCalendar'>
            <span class='head-icon iconfa-calendar'></span>
            <span class='headmenu-label'>My Calendar</span>
        </a>
    </li>

    <li class="hidden-gt-sm">
        <a href='/general/logout/'>
            <span class="head-icon fa fa-sign-out-alt"></span>
        </a>
    </li>

</ul><!--headmenu-->
