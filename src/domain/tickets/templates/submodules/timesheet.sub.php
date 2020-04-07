<?php 
    $values = $this->get('timesheetValues');
    $ticket = $this->get('ticket');
    $userInfo = $this->get('userInfo');
    $remainingHours = $this->get('remainingHours');
    if($remainingHours < 0) {$remainingHours = 0;}
    $currentPay = $this->get('userHours') * $userInfo['wage'];
?>

        <div class="row-fluid">
            <div class="span6">
                

                <h4 class="widgettitle title-light"><span class="fa fa-clock-o"></span><?php echo $this->__('headline.add_time_entry', false); ?></h4>
                <br />

                <form method="post" action="#timesheet" class="stdform">

                    <label for="kind"><?php echo $this->__('label.timesheet_kind') ?></label>
                    <span class="field">
                    <select id="kind" name="kind">
                    <?php foreach ($this->get('kind') as $key => $row) {
                        echo'<option value="'.$key.'"';
                        if($row == $values['kind']) { echo ' selected="selected"';
                        }
                        echo'>'.$this->__(strtolower($row)).'</option>';

                    } ?>
                    </select>
                    </span>

                    <label for="timesheetdate"><?php echo $this->__('label.date') ?>:</label>
                    <input type="text" id="timesheetdate" name="date" class="dates" value="<?php echo $values['date'] ?>" /><br/>

                    <label for="hours"><?php echo $this->__('label.hours') ?></label>
                    <span class="field">
                        <input type="text" id="hours" name="hours" value="<?php echo $values['hours'] ?>" size="7" class="input-small" />
                    </span>
                    <label for="description"><?php echo $this->__('label.description') ?></label>
                    <span class="field">
                        <textarea rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
                    </span>

                    <input type="submit" value="<?php echo $this->__('buttons.save'); ?>" name="saveTimes" class="button" />

                </form>

            </div>
            <div class="span6">
                <h4 class="widgettitle title-light"><span class="fa fa-bar-chart"></span><?php echo $this->__('subtitles.logged_hours_chart'); ?></h4>

                <br />
                <canvas id="canvas"></canvas>
                <p><br />
                    <?php echo $this->__('label.planned_hours'); ?>: <?php echo $ticket->planHours; ?><br />
                    <?php echo $this->__('label.booked_hours') ?>: <?php echo $this->get('timesheetsAllHours'); ?><br />
                    <?php echo $this->__('label.actual_hours_remaining') ?>: <?php echo $remainingHours; ?><br />
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