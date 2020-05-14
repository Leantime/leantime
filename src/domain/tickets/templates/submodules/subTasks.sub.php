
<?php
    $ticket = $this->get('ticket');
    $statusLabels  = $this->get('statusLabels');
?>

<h4 class="widgettitle title-light"><span class="fa fa-list-ul"></span><?php echo $this->__('subtitles.subtasks'); ?></h4>
<p><?=$this->__('text.what_are_subtasks') ?><br /><br /></p>

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
