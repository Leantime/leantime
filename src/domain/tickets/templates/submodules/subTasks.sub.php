
<?php
    $ticket = $this->get('ticket');
    $statusLabels  = $this->get('statusLabels');
    $efforts = $this->get('efforts');

?>

<h4 class="widgettitle title-light"><span class="fa fa-list-ul"></span><?php echo $this->__('subtitles.subtasks'); ?></h4>
<p><?=$this->__('text.what_are_subtasks') ?><br /><br /></p>


<ul class="sortableTicketList" >
    <li class="">
        <a href="javascript:void(0);" class="quickAddLink" id="subticket_new_link" onclick="jQuery('#subticket_new').toggle('fast', function() {jQuery(this).find('input[name=headline]').focus();}); jQuery(this).toggle('fast');"><i class="fas fa-plus-circle"></i> <?php echo $this->__("links.quick_add_todo"); ?></a>
        <div class="ticketBox hideOnLoad" id="subticket_new" >

            <form method="post" class="form-group ticketModal" action="<?=BASE_URL."/tickets/showTicket/".$ticket->id."#substasks"; ?>">
                <input type="hidden" value="new" name="subtaskId" />
                <input type="hidden" value="1" name="subtaskSave" />
                <input name="headline" type="text" title="<?php echo $this->__("label.headline"); ?>" style="width:100%" placeholder="<?php echo $this->__("input.placeholders.what_are_you_working_on"); ?>" />
                <input type="submit" value="<?php echo $this->__("buttons.save"); ?>" name="quickadd"  />
                <input type="hidden" name="dateToFinish" id="dateToFinish" value="" />
                <input type="hidden" name="status" value="3" />
                <input type="hidden" name="sprint" value="<?php echo $_SESSION['currentSprint']; ?>" />
                <a href="javascript:void(0);" onclick="jQuery('#subticket_new').toggle('fast'); jQuery('#subticket_new_link').toggle('fast');">
                    <?php echo $this->__("links.cancel"); ?>
                </a>
            </form>

            <div class="clearfix"></div>
        </div>
    </li>


<?php
$sumPlanHours = 0;
$sumEstHours = 0;
foreach($this->get('allSubTasks') as $subticket) {
$sumPlanHours = $sumPlanHours + $subticket['planHours'];
$sumEstHours = $sumEstHours + $subticket['hourRemaining'];

    if($subticket['dateToFinish'] == "0000-00-00 00:00:00" || $subticket['dateToFinish'] == "1969-12-31 00:00:00") {
        $date = $this->__("text.anytime");

    }else {
        $date = new DateTime($subticket['dateToFinish']);
        $date = $date->format($this->__("language.dateformat"));

    }
?>
    <li class="ui-state-default" id="ticket_<?php echo $subticket['id']; ?>" >
        <div class="ticketBox fixed priority-border-<?=$subticket['priority']?>" data-val="<?php echo $subticket['id']; ?>">

            <div class="row">
                <div clss="col-md-12" style="padding:0 15px;">
                    <input type="text" name="subtaskheadline" value="<?=$subticket['headline']?>" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4" style="padding:0 15px;">
                    <?php echo $this->__("label.due"); ?><input type="text" title="<?php echo $this->__("label.due"); ?>" value="<?php echo $date ?>" class="duedates secretInput" data-id="<?php echo $subticket['id'];?>" name="date" />
                </div>
                <div class="col-md-8" style="padding-top:3px;" >
                    <div class="right">

                        <div class="dropdown ticketDropdown effortDropdown show">
                            <a class="dropdown-toggle f-left  label-default effort" href="javascript:void(0);" role="button" id="effortDropdownMenuLink<?=$subticket['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                    if($subticket['storypoints'] != '' && $subticket['storypoints'] > 0) {
                                                                        echo $efforts[$subticket['storypoints']];
                                                                    }else{
                                                                        echo $this->__("label.story_points_unkown");
                                                                    }?>
                                                                </span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="effortDropdownMenuLink<?=$subticket['id']?>">
                                <li class="nav-header border"><?=$this->__("dropdown.how_big_todo")?></li>
                                <?php foreach($efforts as $effortKey => $effortValue){
                                    echo"<li class='dropdown-item'>
                                                                            <a href='javascript:void(0);' data-value='".$subticket['id']."_".$effortKey."' id='ticketEffortChange".$subticket['id'].$effortKey."'>".$effortValue."</a>";
                                    echo"</li>";
                                }?>
                            </ul>
                        </div>

                        <div class="dropdown ticketDropdown statusDropdown colorized show">
                            <a class="dropdown-toggle f-left status <?=$statusLabels[$subticket['status']]["class"]?>" href="javascript:void(0);" role="button" id="statusDropdownMenuLink<?=$subticket['id']?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text"><?php
                                                                    echo $statusLabels[$subticket['status']]["name"];
                                                                    ?>
                                                                </span>
                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink<?=$subticket['id']?>">
                                <li class="nav-header border"><?=$this->__("dropdown.choose_status")?></li>

                                <?php foreach($statusLabels as $key=>$label){
                                    echo"<li class='dropdown-item'>
                                                                            <a href='javascript:void(0);' class='".$label["class"]."' data-label='".$this->escape($label["name"])."' data-value='".$subticket['id']."_".$key."_".$label["class"]."' id='ticketStatusChange".$subticket['id'].$key."' >".$this->escape($label["name"])."</a>";
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























<table cellpadding="0" cellspacing="0" border="0" class="allTickets table table-bordered"
    id="allTickets">
    
    <thead>
        <tr>
            <th width="15%"><?php echo $this->__('label.headline'); ?></th>
            <th  width="30%"><?php echo $this->__('label.description'); ?></th>
            <th width="15%"><?php echo $this->__('label.todo_status'); ?></th>
            <th width="10%"><?php echo $this->__('label.planned_hours'); ?></th>
            <th width="10%"><?php echo $this->__('label.actual_hours_remaining'); ?></th>
            <th width="10%"><?php echo $this->__('label.actions'); ?></th>
        </tr>
    </thead>
    <tbody>

    <?php
    $sumPlanHours = 0;
    $sumEstHours = 0;
    foreach($this->get('allSubTasks') as $subticket) {
        $sumPlanHours = $sumPlanHours + $subticket['planHours'];
        $sumEstHours = $sumEstHours + $subticket['hourRemaining'];
        ?>
        <tr>
            <form method="post" action="#subtasks">
                <td><input type="text" value="<?php $this->e($subticket['headline']); ?>" name="headline"/></td>
                <td><textarea  name="description" style="width:80%"><?php $this->e($subticket['description']) ?></textarea></td>
                <td style="width:150px;" ><select class="span11 status-select" name="status" style="width:150px;"  data-placeholder="">
                        <?php foreach($statusLabels as $key=>$label){?>
                            <option value="<?php echo $key; ?>"
                                <?php if($subticket['status'] == $key) {echo"selected='selected'";
                                }?>
                            ><?php echo $this->escape($statusLabels[$key]["name"]); ?></option>
                        <?php } ?>
                    </select>
                </td>
            <td><input type="text" value="<?php echo $this->e($subticket['planHours']); ?>" name="planHours" class="small-input"/></td>
            <td><input type="text" value="<?php echo $this->e($subticket['hourRemaining']); ?>" name="hourRemaining" class="small-input"/></td>
                <td><input type="hidden" value="<?php echo $subticket['id']; ?>" name="subtaskId" />
                    <input type="submit" value="<?php echo $this->__('buttons.save'); ?>" name="subtaskSave"/>
                    <input type="submit" value="<?php echo $this->__('buttons.delete'); ?>" class="delete" name="subtaskDelete"/></td>
            </form>
            
        </tr>
    <?php } ?>
    <?php if(count($this->get('allSubTasks')) === 0) : ?>
        <tr>
            <td colspan="6"><?php echo $this->__('text.no_subtasks'); ?></td>
        </tr>
    <?php endif; ?>
    <tr><td colspan="6" style="background:#ccc;"><strong><?php echo $this->__('text.create_new_subtask'); ?></strong></td></tr>
    <tr>
        <form method="post" action="#subtasks">
        <td><input type="text" value="" name="headline"/></td>
        <td><textarea  name="description" style="width:80%"></textarea></td>
        <td style="width:150px;">
            <select class="span11 status-select" name="status"  style="width:150px;" data-placeholder="">
                <?php foreach($statusLabels as $key=>$label){?>
                    <option value="<?php echo $key; ?>"
                    ><?php echo $this->escape($label["name"]); ?></option>
                <?php } ?>
            </select>
        </td>
        <td><input type="text" value="" name="planHours" style="width:100px;"/></td>
        <td><input type="text" value="" name="hourRemaining" style="width:100px;"/></td>
        <td><input type="hidden" value="new" name="subtaskId" /><input type="submit" value="<?php echo $this->__('buttons.save'); ?>" name="subtaskSave"/></td>
        </form>
    </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong><?php echo $this->__('label.total_hours') ?></strong></td>
            <td><strong><?php echo $sumPlanHours; ?></strong></td>
            <td><strong><?php echo $sumEstHours; ?></strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>
