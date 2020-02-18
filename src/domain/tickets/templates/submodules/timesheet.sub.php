<?php 

$values = $this->get('timesheetValues');
$ticket = $this->get('ticket');
$userInfo = $this->get('userInfo');
$remainingHours = $ticket['planHours'] - $this->get('timesheetsAllHours'); 
if($remainingHours < 0) {$remainingHours =0;
}
$currentPay = $this->get('userHours') * $userInfo['wage'];
?>

<script type="text/javascript">
        
        jQuery(document).ready(function($) { 
            $("#datepicker").datepicker();

            var d2 = [];
            var d3 = [];
    <?php 
    $sum = 0;
    foreach ($this->get('ticketHours') as $hours){
        $sum = $sum + $hours['summe'];
        echo"d2.push([".(strtotime($hours['utc']) * 1000).", ".$sum."]);
				 "; 
        echo "d3.push([".(strtotime($hours['utc']) * 1000).", ".$ticket['planHours']."]);
				 ";
            
    } ?>
            
            var stack = 0, bars = false, lines = true, steps = false;
            
            jQuery.plot(jQuery("#bargraph"), [ {label: "Planned Hours", data: d3  }, {label: "Booked Hours", data: d2  }], 
            {
                series: {
                    
                    lines: { show: lines, fill: true, steps: steps },
                    bars: { show: bars, barWidth: 0.6 }
                },
                
                grid: { hoverable: true, clickable: true, borderColor: '#666', borderWidth: 1, labelMargin: 10 },
                colors: ['#ff9900',"#1b75bb" ],
                xaxis: {
                    mode: "time",
                    timeformat: "%m/%d",
                    monthNames: ["jan", "feb", "mar", "apr", "maj", "jun", "jul", "aug", "sep", "okt", "nov", "dec"]
                },
                cursor: {
                  show: true,
                  tooltipLocation:'sw'
                }
            });
        
        
        });
        
                 
</script>

<style type='text/css'>

</style>

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
                <input type="text" id="datepicker" name="date" class="iconfa-calendar" value="" /><br/>
            
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
                <div id="bargraph" style="height:300px; width:100%;"></div>
                
                <p><br />
        <?php echo $language->lang_echo('PLAN_HOURS'); ?>: <?php echo $ticket['planHours']; ?><br />
        <?php echo $language->lang_echo('BOOKED_HOURS') ?>: <?php echo $this->get('timesheetsAllHours'); ?><br />
        <?php echo $language->lang_echo('HOURS_REMAINING') ?>: <?php echo $remainingHours; ?><br />
                </p>
                <!--<p>
        <?php if ($userInfo['wage']!=0 && $userInfo['wage']!=null) { ?>
            <?php echo $language->lang_echo('TOTAL_COST'); ?>: $<?php echo $this->get('ticketPrice'); ?><br/>
            <?php echo $language->lang_echo('YOUR_PAY'); ?>: $<?php echo $currentPay; ?><br/>
        <?php } ?>
                </p>-->
        </div>
        </div>
