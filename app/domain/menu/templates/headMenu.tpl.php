<?php defined('RESTRICTED') or die('Restricted access'); ?>

<?php
$currentLink = $this->get('current');
$module = '';
$action = '';

if (is_array($currentLink)) {
    $module = $currentLink[0] ?? '';
    $action = $currentLink[1] ?? '';
}

?>

<?php $this->dispatchTplEvent('beforeHeadMenu'); ?>
<ul class="headmenu">
    <?php $this->dispatchTplEvent('afterHeadMenuOpen'); ?>
    <li>
        <a href='<?=BASE_URL ?>/dashboard/home' <?php if ($module == 'dashboard' && $action == 'home') {
            echo"class='active'";
                 } ?> data-tippy-content="<?=$this->__("popover.home") ?>">
            <?=$this->__("menu.home")?>
        </a>
    </li>
    <li>
        <a href='<?=BASE_URL ?>/projects/showMy' <?php if ($module == 'projects' && $action == 'showMy') {
            echo"class='active'";
                 } ?> data-tippy-content="<?=$this->__("popover.my_portfolio") ?>">
            <?=$this->__("menu.my_portfolio")?>
        </a>
    </li>
    <?php if ($login::userIsAtLeast($roles::$editor, true)) { ?>
        <?php if ($this->get('onTheClock') !== false) {
            echo "<li class='timerHeadMenu' id='timerHeadMenu'>";
            echo"<a href='javascript:void(0);' class='dropdown-toggle' data-toggle='dropdown'>
                    " . sprintf($this->__('text.timer_on_todo'), $this->get('onTheClock')['totalTime'], substr($this->escape($this->get('onTheClock')['headline']), 0, 10)) . "
                </a>";

            ?>
            <ul class="dropdown-menu">
                <li>
                    <a href='#/tickets/showTicket/<?php $this->e($this->get('onTheClock')['id']); ?>'>
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
            <a href='<?=BASE_URL ?>/timesheets/showMy/' <?php if ($module == 'timesheets' && $action == 'showMy') {
                echo"class='active'";
                     } ?> data-tippy-content="<?=$this->__("popover.my_timesheets") ?>">
                <?=$this->__("menu.my_timesheets")?>
            </a>
        </li>

        <li>
            <a href='<?=BASE_URL ?>/calendar/showMyCalendar' <?php if ($module == 'calendar' && $action == 'showMyCalendar') {
                echo"class='active'";
                     } ?> data-tippy-content="<?=$this->__("popover.my_calendar") ?>">
                <?=$this->__("menu.my_calendar")?>
            </a>
        </li>



    <?php } ?>

    <li class="notificationDropdown">
        <?php
            $notifications = $this->get('notifications');
            $notificationCount = $this->get('newNotificationCount');

            $totalNotificationCount = 0;
            $totalMentionCount = 0;
            $totalNewMentions = 0;
            $totalNewNotifications = 0;

        foreach ($notifications as $notif) {
            if ($notif['type'] == 'mention') {
                $totalMentionCount++;
                if ($notif['read'] == 0) {
                    $totalNewMentions++;
                }
            } else {
                $totalNotificationCount++;
                if ($notif['read'] == 0) {
                    $totalNewNotifications++;
                }
            }
        }
        ?>
        <a href='javascript:void(0);' class="dropdown-toggle profileHandler notificationHandler" data-toggle="dropdown" data-tippy-content="<?=$this->__("popover.notifications") ?>">
            <span class="fa-solid fa-bell"></span>
            <?php if ($notificationCount > 0) {
                echo "<span class='notificationCounter'>" . $notificationCount . "</span>";
            } ?>
        </a>
        <div class="dropdown-menu" id="notificationsDropdown">
            <div class="dropdownTabs">
                <a href="javascript:void(0);" class="notifcationTabs active" id="notificationsListLink" onclick="toggleNotificationTabs('notifications')" >Notifications (<?=$totalNewNotifications?>)</a>
                <a href="javascript:void(0);" class="notifcationTabs" id="mentionsListLink" onclick="toggleNotificationTabs('mentions')">Mentions (<?=$totalNewMentions?>)</a>
            </div>
            <div class="scroll-wrapper">

                <ul id="notificationsList" class="notifcationViewLists">
                    <?php
                    if ($totalNotificationCount === 0) {?>
                        <p style="padding:10px"><?=$this->__('text.no_notifications') ?></p>
                    <?php }
                    ?>
                <?php foreach ($notifications as $notif) {
                    if ($notif['type'] !== 'mention') {?>
                    <li <?php if ($notif['read'] == 0) {
                        echo" class='new' ";
                        } ?> data-url="<?=$notif['url'] ?>" data-id="<?=$notif['id'] ?>">
                        <a href="<?=$notif['url'] ?>">
                        <span class="notificationProfileImage">
                            <img src="<?= BASE_URL ?>/api/users?profileImage=<?=$notif['authorId'] ?>" />
                        </span>
                        <span class="notificationTitle"><?=$notif['message'] ?></span>
                        <span class="notificationDate"><?=$this->getFormattedDateString($notif['datetime']) ?> <?=$this->getFormattedTimeString($notif['datetime']) ?></span>
                        </a>
                    </li>


                    <?php }
                } ?>
                </ul>

                <ul id="mentionsList" style="display:none;" class="notifcationViewLists">
                    <?php
                    if ($totalMentionCount === 0) {?>
                            <p style="padding:10px"><?=$this->__('text.no_notifications') ?></p>
                    <?php } ?>
                    <?php foreach ($notifications as $notif) {
                        if ($notif['type'] === 'mention') {?>
                            <li <?php if ($notif['read'] == 0) {
                                echo" class='new' ";
                                } ?> data-url="<?=$notif['url'] ?>" data-id="<?=$notif['id'] ?>">
                                <a href="<?=$notif['url'] ?>">
                                    <span class="notificationProfileImage">
                                        <img src="<?= BASE_URL ?>/api/users?profileImage=<?=$notif['authorId'] ?>" />
                                    </span>
                                        <span class="notificationTitle"><?=$notif['message'] ?></span>
                                        <span class="notificationDate"><?=$this->getFormattedDateString($notif['datetime']) ?> <?=$this->getFormattedTimeString($notif['datetime']) ?></span>
                                    </li>
                                </a>


                        <?php }
                    } ?>

                </ul>

            </div>
        </div>
    </li>

    <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
        <li class="appsDropdown">
            <a href='javascript:void(0);' class="dropdown-toggle profileHandler" data-toggle="dropdown" data-tippy-content="<?=$this->__("popover.company") ?>">
                <img src="<?=BASE_URL ?>/dist/images/svg/apps-grid-icon.svg" style="width:13px; vertical-align: middle;">

            </a>
            <ul class="dropdown-menu" >



                <li class="nav-header"><?=$this->__("header.management")?></li>
                <li>
                    <a href="<?=BASE_URL ?>/timesheets/showAll"><?=$this->__("menu.all_timesheets") ?></a>
                </li>


                <li <?php if ($module == 'projects') {
                    echo" class='active' ";
                    } ?>>
                    <a href='<?=BASE_URL ?>/projects/showAll/'>
                        <?=$this->__("menu.all_projects")?>
                    </a>
                </li>

                <?php if ($login::userIsAtLeast($roles::$admin)) { ?>
                    <li <?php if ($module == 'clients') {
                        echo" class='active' ";
                        } ?>>
                        <a href='<?=BASE_URL ?>/clients/showAll/'>
                            <?=$this->__("menu.all_clients")?>
                        </a>
                    </li>
                    <li <?php if ($module == 'users') {
                        echo" class='active' ";
                        } ?>>
                        <a href='<?=BASE_URL ?>/users/showAll/'>
                            <?=$this->__("menu.all_users")?>
                        </a>
                    </li>

                    <?php if ($login::userIsAtLeast($roles::$owner)) { ?>
                        <li class="nav-header border"><?=$this->__("label.administration")?></li>

                        <li <?php if ($module == 'plugins') {
                            echo" class='active' ";
                            } ?>>
                            <a href='<?=BASE_URL ?>/plugins/show/'>
                                <?=$this->__("menu.plugins")?>
                            </a>
                        </li>

                        <li <?php if ($module == 'setting') {
                            echo" class='active' ";
                            } ?>>
                            <a href='<?=BASE_URL ?>/setting/editCompanySettings/'>
                                <?=$this->__("menu.company_settings")?>
                            </a>
                        </li>

                        <?php $this->dispatchTplEvent('companyMenuEnd', ["module"=>$module]); ?>

                    <?php } ?>

                <?php } ?>
            </ul>

        </li>
    <?php } ?>
    <li>
        <div class="userloggedinfo">
            <?php echo $this->frontcontroller->includeAction('auth.loginInfo'); ?>
        </div>
    </li>

    <?php $this->dispatchTplEvent('beforeHeadMenuClose'); ?>
</ul>
<?php $this->dispatchTplEvent('afterHeadMenu'); ?>


<script>
    function toggleNotificationTabs(active) {

            jQuery(".notifcationTabs").removeClass("active");
            jQuery('#'+active+'ListLink').addClass("active");
            jQuery('.notifcationViewLists').hide();
            jQuery('#'+active+'List').show();


    }

    jQuery(document).ready(function(){


        jQuery(".notificationHandler").on("click", function() {
            jQuery.ajax(
                {
                    type: 'PATCH',
                    url: leantime.appUrl+'/api/notifications',
                    data:
                        {
                            id : "all",
                            action: "read"
                        }
                }
            ).done(
                function () {
                    jQuery(".notifcationViewLists li.new").removeClass("new");
                    jQuery(".notificationCounter").fadeOut();
                }
            );
        });


        jQuery(".notificationDropdown .dropdown-menu").on("click", function(e) {
            e.stopPropagation();
        });

        jQuery("#notificationsDropdown li").click(function(){

           var url = jQuery(this).attr("data-url");
           var id = jQuery(this).attr("data-id");

           window.location.href = url;
        });
    });
</script>
