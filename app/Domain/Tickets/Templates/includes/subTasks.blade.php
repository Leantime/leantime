
<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
    $ticket = $tpl->get('ticket');
$statusLabels  = $tpl->get('statusLabels');
$efforts = $tpl->get('efforts');

?>

<p><?=$tpl->__('text.what_are_subtasks') ?><br /><br /></p>


<ul class="sortableTicketList" style="margin-bottom:120px;">
    <li class="">
        <a href="javascript:void(0);" class="quickAddLink" id="subticket_new_link" onclick="jQuery('#subticket_new').toggle('fast', function() {jQuery(this).find('input[name=headline]').focus();}); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> {{ __("links.quick_add_todo") }}</a>
        <div class="ticketBox hideOnLoad" id="subticket_new" >

            <form method="post" class="form-group formModal" action="<?=BASE_URL . "/tickets/showTicket/" . $ticket->id . "#substasks"; ?>">
                <input type="hidden" value="new" name="subtaskId" />
                <input type="hidden" value="1" name="subtaskSave" />
                <input name="headline" type="text" title="{{ __("label.headline") }}" style="width:100%" placeholder="{{ __("input.placeholders.what_are_you_working_on") }}" />
                <input type="submit" value="{{ __("buttons.save") }}" name="quickadd"  />
                <input type="hidden" name="dateToFinish" id="dateToFinish" value="" />
                <input type="hidden" name="status" value="3" />
                <input type="hidden" name="sprint" value="<?php echo session("currentSprint"); ?>" />
                <a href="javascript:void(0);" onclick="jQuery('#subticket_new').toggle('fast'); jQuery('#subticket_new_link').toggle('fast');">
                    {{ __("links.cancel") }}
                </a>
            </form>

            <div class="clearfix"></div>
        </div>
    </li>


<?php
$sumPlanHours = 0;
$sumEstHours = 0;
foreach ($tpl->get('allSubTasks') as $subticket) {
    $sumPlanHours = $sumPlanHours + $subticket['planHours'];
    $sumEstHours = $sumEstHours + $subticket['hourRemaining'];

    if ($subticket['dateToFinish'] == "0000-00-00 00:00:00" || $subticket['dateToFinish'] == "1969-12-31 00:00:00") {
        $date = $tpl->__("text.anytime");
    } else {
        $date = new DateTime($subticket['dateToFinish']);
        $date = $date->format($tpl->__("language.dateformat"));
    }
    ?>
    <li class="ui-state-default" id="ticket_<?php echo $subticket['id']; ?>" >
        <div class="ticketBox fixed priority-border-<?=$subticket['priority']?>" data-val="<?php echo $subticket['id']; ?>" >

            <div class="row">
                <div class="col-md-12" style="padding:0 15px;">
                    <?php if ($login::userIsAtLeast($roles::$editor)) {  ?>
                        <div class="inlineDropDownContainer" >
                            <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="{{ BASE_URL }}/tickets/showTicket/<?=$ticket->id ?>?delSubtask=<?php echo $subticket["id"]; ?>" class="delete formModal"><i class="fa fa-trash"></i> {{ __("links.delete_todo") }}</a></li>

                            </ul>
                        </div>
                    <?php } ?>

                    <a href="#/tickets/showTicket/<?=$subticket['id']?>"><?=$tpl->escape($subticket['headline'])?></a>

                </div>
            </div>
            <div class="row">
                <div class="col-md-9" style="padding:0 15px;">
                    <div class="row">
                        <div class="col-md-4">
                            {{ __("label.due") }}<input type="text" title="{{ __("label.due") }}" value="<?php echo $date ?>" class="duedates secretInput quickDueDates" data-id="<?php echo $subticket['id'];?>" name="date" />
                        </div>
                        <div class="col-md-4">
                            {{ __("label.planned_hours") }}<input type="text" value="<?php echo $tpl->e($subticket['planHours']); ?>" name="planHours" data-label="planHours-<?=$subticket['id']?>" class="small-input secretInput asyncInputUpdate" style="width:40px"/>
                        </div>
                        <div class="col-md-4">
                            {{ __("label.estimated_hours_remaining") }}<input type="text" value="<?php echo $tpl->e($subticket['hourRemaining']); ?>" name="hourRemaining" data-label="hourRemaining-<?=$subticket['id']?>" class="small-input secretInput asyncInputUpdate" style="width:40px"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-3" style="padding-top:3px;" >
                    <div class="right">
                        <div class="dropdown ticketDropdown effortDropdown show">
                            <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink<?=$subticket['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                if ($subticket['storypoints'] != '' && $subticket['storypoints'] > 0) {
                                                                    echo $efforts["" . $subticket['storypoints']];
                                                                } else {
                                                                    echo $tpl->__("label.story_points_unkown");
                                                                }?>
                                                                </span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink<?=$subticket['id']?>">
                                <li class="nav-header border"><?=$tpl->__("dropdown.how_big_todo")?></li>
                                <?php foreach ($efforts as $effortKey => $effortValue) {
                                    echo"<li class='dropdown-item'>
                                        <a href='javascript:void(0);' data-value='" . $subticket['id'] . "_" . $effortKey . "' id='ticketEffortChange" . $subticket['id'] . $effortKey . "'>" . $effortValue . "</a>";
                                    echo"</li>";
                                }?>
                            </ul>
                        </div>

                        <?php

                        if (isset($statusLabels[$subticket['status']])) {
                            $class = $statusLabels[$subticket['status']]["class"];
                            $name = $statusLabels[$subticket['status']]["name"];
                        } else {
                            $class = 'label-important';
                            $name = 'new';
                        }
                        ?>
                        <div class="dropdown ticketDropdown statusDropdown colorized show">
                            <a class="dropdown-toggle f-left status <?=$class ?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$subticket['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                echo $name;
                                                                ?>
                                                                </span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$subticket['id']?>">
                                <li class="nav-header border"><?=$tpl->__("dropdown.choose_status")?></li>

                                <?php foreach ($statusLabels as $key => $label) {
                                    echo"<li class='dropdown-item'>
                                        <a href='javascript:void(0);' class='" . $label["class"] . "' data-label='" . $tpl->escape($label["name"]) . "' data-value='" . $subticket['id'] . "_" . $key . "_" . $label["class"] . "' id='ticketStatusChange" . $subticket['id'] . $key . "' >" . $tpl->escape($label["name"]) . "</a>";
                                    echo"</li>";
                                }?>
                            </ul>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </li>

<?php } ?>
</ul>
