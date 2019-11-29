<?php 
    $values = $this->get('values');
    $ticket = $this->get('ticket');
    $userInfo = $this->get('userInfo');
    $remainingHours = $this->get('remainingHours');
    if($remainingHours < 0) {$remainingHours = 0;}
    $currentPay = $this->get('userHours') * $userInfo['wage'];
?>

        <div class="row-fluid">
            <div class="span6">
                

                <h4 class="widgettitle title-light"><span class="fa fa-clock-o"></span><?php echo $this->__('Add Time Entry', false); ?></h4>
                <br />

                <form method="post" action="#timesheet" class="stdform">

                    <label for="kind"><?php echo $this->__('KIND') ?></label>
                    <span class="field">
                    <select id="kind" name="kind">
                    <?php foreach ($this->get('kind') as $row) {
                        echo'<option value="'.$row.'"';
                        if($row == $values['kind']) { echo ' selected="selected"';
                        }
                        echo'>'.$this->__($row).'</option>';

                    } ?>
                    </select>
                    </span>

                    <label for="dateFrom"><?php echo $this->__('DATE') ?>:</label>
                    <input type="text" id="datepicker" name="date" class="iconfa-calendar dates" value="" /><br/>

                    <label for="hours"><?php echo $this->__('HOURS') ?></label>
                    <span class="field">
                        <input type="text" id="hours" name="hours" value="<?php echo $values['hours'] ?>" size="7" class="input-small" />
                    </span>
                    <label for="description"><?php echo $this->__('DESCRIPTION') ?></label>
                    <span class="field">
                        <textarea rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
                    </span>

                    <input type="submit" value="<?php echo $this->__('SAVE'); ?>" name="saveTimes" class="button" />

                </form>

            </div>
            <div class="span6">
                <h4 class="widgettitle title-light"><span class="fa fa-bar-chart"></span><?php echo $this->__('Booked Time Chart', false); ?></h4>

                <br />
                <canvas id="canvas"></canvas>
                <p><br />
                    <?php echo $this->__('PLAN_HOURS'); ?>: <?php echo $ticket->planHours; ?><br />
                    <?php echo $this->__('BOOKED_HOURS') ?>: <?php echo $this->get('timesheetsAllHours'); ?><br />
                    <?php echo $this->__('HOURS_REMAINING') ?>: <?php echo $remainingHours; ?><br />
                </p>
            </div>
        </div>

<script type="text/javascript">

    jQuery(document).ready(function($) {

        var d2 = [];
        var d3 = [];
        var labels = [];
        <?php
        $sum = 0;
        foreach ($this->get('ticketHours') as $hours){
            $sum = $sum + $hours['summe'];

            echo"labels.push('".date($this->__("language.dateformat"),  strtotime($hours['utc']))."');
                    ";
            echo"d2.push(".$sum.");
                    ";
            echo "d3.push(".$ticket->planHours.");
                    ";

        } ?>

        leantime.ticketsController.initTimeSheetChart(labels, d2, d3, "canvas")

    });

</script>