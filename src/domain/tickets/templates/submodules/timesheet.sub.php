<?php 
    $values = $this->get('values');
    $ticket = $this->get('ticket');
    $userInfo = $this->get('userInfo');
    $remainingHours = $ticket['planHours'] - $this->get('timesheetsAllHours');
    if($remainingHours < 0) {$remainingHours =0;}
    $currentPay = $this->get('userHours') * $userInfo['wage'];
?>

        <div class="row-fluid">
            <div class="span6">
                

                <h4 class="widgettitle title-light"><span class="fa fa-clock-o"></span><?php echo $language->lang_echo('Add Time Entry', false); ?></h4>
                <br />
                <form method="post" action="#timesheet" class="stdform">

                    <label for="kind"><?php echo $language->lang_echo('KIND') ?></label>
                    <span class="field">
                    <select id="kind" name="kind">
                    <?php foreach ($this->get('kind') as $row) {
                        echo'<option value="'.$row.'"';
                        if($row == $values['kind']) { echo ' selected="selected"';
                        }
                        echo'>'.$language->lang_echo($row).'</option>';

                    } ?>
                    </select>
                    </span>

                    <label for="dateFrom"><?php echo $language->lang_echo('DATE') ?>:</label>
                    <input type="text" id="datepicker" name="date" class="iconfa-calendar dates" value="" /><br/>

                    <label for="hours"><?php echo $language->lang_echo('HOURS') ?></label>
                    <span class="field">
                        <input type="text" id="hours" name="hours" value="<?php echo $values['hours'] ?>" size="7" class="input-small" />
                    </span>
                    <label for="description"><?php echo $language->lang_echo('DESCRIPTION') ?></label>
                    <span class="field">
                        <textarea rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
                    </span>

                    <input type="submit" value="<?php echo $language->lang_echo('SAVE'); ?>" name="saveTimes" class="button" />

                </form>

            </div>
            <div class="span6">
                <h4 class="widgettitle title-light"><span class="fa fa-bar-chart"></span><?php echo $language->lang_echo('Booked Time Chart', false); ?></h4>
                <br />
                <canvas id="canvas"></canvas>
                <p><br />
                    <?php echo $language->lang_echo('PLAN_HOURS'); ?>: <?php echo $ticket['planHours']; ?><br />
                    <?php echo $language->lang_echo('BOOKED_HOURS') ?>: <?php echo $this->get('timesheetsAllHours'); ?><br />
                    <?php echo $language->lang_echo('HOURS_REMAINING') ?>: <?php echo $remainingHours; ?><br />
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

            echo"labels.push('".date("m/d",  strtotime($hours['utc']))."');
                    ";
            echo"d2.push(".$sum.");
                    ";
            echo "d3.push(".$ticket['planHours'].");
                    ";

        } ?>

        leantime.ticketsController.initTimeSheetChart(labels, d2, d3, "canvas")

    });

</script>