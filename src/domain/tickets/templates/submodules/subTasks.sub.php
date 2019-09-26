<?php
    $ticket = $this->get('ticket');
    $tickets = $this->get('objTicket');
?>

<h4 class="widgettitle title-light"><span class="fa fa-list-ul"></span><?php echo $language->lang_echo('Subtasks', false); ?></h4>
<p>Use Subtasks to break down your main ToDo into smaller chunks of work. You can add plan hours and remaining hours right here. <strong>We recommend Subtasks be less than 4 hours long.</strong>
<br />Once you add subtasks planned and remaining hours on the main ToDo will be updated as well.<br /><br /></p>

<table cellpadding="0" cellspacing="0" border="0" class="allTickets table table-bordered"
    id="allTickets">
    
    <thead>
        <tr>
            <th width="15%"><?php echo $language->lang_echo('TITLE'); ?></th>
            <th  width="30%"><?php echo $language->lang_echo('DESCRIPTION'); ?></th>
            <th width="15%"><?php echo $language->lang_echo('STATUS'); ?></th>
            <th width="10%"><?php echo $language->lang_echo('PLANNED HOURS', false); ?></th>
            <th width="10%"><?php echo $language->lang_echo('Remaining Hours', false); ?></th>
            <th width="10%">Actions</th>
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
                <td><input type="text" value="<?php echo $subticket['headline']; ?>" name="headline"/></td>
                <td><textarea  name="description" style="width:80%"><?php echo $subticket['description'] ?></textarea></td>
                <td style="width:150px;" ><select class="span11 status-select" name="status" style="width:150px;"  data-placeholder="<?php echo $language->lang_echo($tickets->getStatusPlain($subticket['status'])); ?>">
                        <?php foreach($tickets->statePlain as $key => $row2) {?>
                            <option value="<?php echo $key; ?>"
                                <?php if($subticket['status'] == $key) {echo"selected='selected'";
                                }?>
                            ><?php echo $language->lang_echo($tickets->getStatusPlain($key)); ?></option>
                        <?php } ?>
                    </select>
                </td>
            <td><input type="text" value="<?php echo $subticket['planHours']; ?>" name="planHours"/></td>
            <td><input type="text" value="<?php echo $subticket['hourRemaining']; ?>" name="hourRemaining"/></td>
                <td><input type="hidden" value="<?php echo $subticket['id']; ?>" name="subtaskId" />
                    <input type="submit" value="&#xf0c7; Save" name="subtaskSave"/>
                    <input type="submit" value="&#xf1f8; Delete" name="subtaskDelete"/></td>
            </form>
            
        </tr>
    <?php } ?>
    <?php if(count($this->get('allSubTasks')) === 0) : ?>
        <tr>
            <td colspan="6"><?php echo $language->lang_echo('You have not created any Subtasks yet.', false); ?></td>
        </tr>
    <?php endif; ?>
    <tr><td colspan="6" style="background:#ccc;">Create a new Subtask:</td></tr>
    <tr>
        <form method="post" action="#subtasks">
        <td><input type="text" value="" name="headline"/></td>
        <td><textarea  name="description" style="width:80%"></textarea></td>
        <td style="width:150px;">
            <select class="span11 status-select" name="status"  style="width:150px;" data-placeholder="<?php echo $language->lang_echo($tickets->getStatusPlain("3")); ?>">
                <?php foreach($tickets->statePlain as $key => $row2) {?>
                    <option value="<?php echo $key; ?>"
                    ><?php echo $language->lang_echo($tickets->getStatusPlain($key)); ?></option>
                <?php } ?>
            </select>
        </td>
        <td><input type="text" value="" name="planHours" style="width:100px;"/></td>
        <td><input type="text" value="" name="hourRemaining" style="width:100px;"/></td>
        <td><input type="hidden" value="new" name="subtaskId" /><input type="submit" value="&#xf0c7; Save" name="subtaskSave"/></td>
        </form>
    </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3"><strong><?php echo $language->lang_echo('Total Hours', false) ?>:</strong></td>
            <td><strong><?php echo $sumPlanHours; ?></strong></td>
            <td><strong><?php echo $sumEstHours; ?></strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>
