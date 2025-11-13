<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$values = $tpl->get('timesheetValues');
$ticket = $tpl->get('ticket');
$userInfo = $tpl->get('userInfo');
$remainingHours = $tpl->get('remainingHours');
$timesheetEntries = $tpl->get('timesheetEntries');
$kindMap = $tpl->get('kind');
if ($remainingHours < 0) {
    $remainingHours = 0;
}
$currentPay = $tpl->get('userHours') * $userInfo['wage'];
?>

        <div class="row">
            <div class="col-md-6">


                <h4 class="widgettitle title-light"><span class="fa fa-clock-o"></span><?php echo $tpl->__('headline.add_time_entry', false); ?></h4>
                <br />

                <form method="post" action="<?= BASE_URL.'/tickets/showTicket/'.$ticket->id.''?>#timesheet" class="formModal">

                    <label for="kind"><?php echo $tpl->__('label.timesheet_kind') ?></label>
                    <span class="field">
                    <select id="kind" name="kind">
                    <?php foreach ($kindMap as $key => $row) {
                        echo '<option value="'.$key.'"';
                        if ($row == $values['kind']) {
                            echo ' selected="selected"';
                        }
                        echo '>'.$tpl->__(strtolower($row)).'</option>';
                    } ?>
                    </select>
                    </span>

                    <label for="timesheetdate"><?php echo $tpl->__('label.date') ?>:</label>
                    <input type="text" id="timesheetdate" name="date" class="dates" value="<?php echo format($values['date'])->date() ?>" /><br/>

                    <label for="hours"><?php echo $tpl->__('label.hours') ?></label>
                    <span class="field">
                        <input type="text" id="hours" name="hours" value="<?php echo $values['hours'] ?>" size="7" class="input-small" />
                    </span>
                    <label for="description"><?php echo $tpl->__('label.description') ?></label>
                    <span class="field">
                        <textarea rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
                    </span>
                    <input type="hidden" name="saveTimes" value="1" />
                    <input type="submit" value="<?php echo $tpl->__('buttons.save'); ?>" name="saveTimes" class="button" />

                </form>

            </div>
            <div class="col-md-6">
                <h4 class="widgettitle title-light"><span class="fa fa-bar-chart"></span><?php echo $tpl->__('subtitles.logged_hours_chart'); ?></h4>

                <br />
                <canvas id="canvas"></canvas>
                <p><br />
                    <?php echo $tpl->__('label.planned_hours'); ?>: <?php echo $ticket->planHours; ?><br />
                    <?php echo $tpl->__('label.booked_hours') ?>: <?php echo $tpl->get('timesheetsAllHours'); ?><br />
                    <?php echo $tpl->__('label.actual_hours_remaining') ?>: <?php echo $remainingHours; ?><br />
                </p>

                <div style="margin-top: 20px;">
                    <h4 class="widgettitle title-light"><span class="fa fa-list"></span><?php echo $tpl->__('subtitles.logged_hours_chart'); ?> <?php echo $tpl->__('label.list'); ?></h4>

                    <?php if (! empty($timesheetEntries)) { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" style="background-color: #fff; border-collapse: collapse;">
                                <thead style="background-color: #f9f9f9;">
                                <tr>
                                    <th style="font-weight: bold; padding: 12px; border: 1px solid #e0e0e0;"><?php echo $tpl->__('label.date'); ?></th>
                                    <th style="font-weight: bold; padding: 12px; border: 1px solid #e0e0e0;"><?php echo $tpl->__('label.user'); ?></th>
                                    <th style="font-weight: bold; padding: 12px; border: 1px solid #e0e0e0;"><?php echo $tpl->__('label.type'); ?></th>
                                    <th style="font-weight: bold; padding: 12px; border: 1px solid #e0e0e0;"><?php echo $tpl->__('label.hours'); ?></th>
                                    <th style="font-weight: bold; padding: 12px; border: 1px solid #e0e0e0;"><?php echo $tpl->__('label.description'); ?></th>
                                </tr>
                                </thead>
                                <tbody style="background-color: #fff;">
                                <?php foreach ($timesheetEntries as $entry) {
                                    $date = new \DateTime($entry['workDate']);

                                    $displayDate = $date->format('Y-m-d H:i:s');

                                    if ($displayDate === '') {
                                    $displayDate = (string) $entry['workDate'];
                                    }

                                    $userName = trim(($entry['firstname'] ?? '').' '.($entry['lastname'] ?? ''));
                                    if ($userName === '') {
                                        $userName = '#'.($entry['userId'] ?? '');
                                    }

                                    $kindLabel = $entry['kind'] ?? '';
                                    if (! empty($entry['kind']) && isset($kindMap[$entry['kind']])) {
                                        $kindLabel = $tpl->__(strtolower($kindMap[$entry['kind']]));
                                    }

                                    $hoursFormatted = format($entry['hours'])->decimal();
                                    $description = $entry['description'] ?? '';
                                    ?>
                                    <tr>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($displayDate, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($kindLabel, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo htmlspecialchars($hoursFormatted, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="padding: 10px; border: 1px solid #e0e0e0;"><?php echo nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <p class="text-muted"><?php echo $tpl->__('label.no_results'); ?></p>
                    <?php } ?>
                </div>
            </div>
        </div>

<script type="text/javascript">

    jQuery(document).ready(function($) {

        var d2 = [];
        var d3 = [];
        var labels = [];
        <?php
        $sum = 0;
$ticketHours = $tpl->get('ticketHours');
foreach ($ticketHours as $hours) {
    $sum = $sum + $hours['summe'];
    try {
        echo "labels.push('".dtHelper()->parseDbDateTime($hours['utc'])->setToUserTimezone()->format(
            'Y-m-d'
        )."');
                    ";
        echo 'd2.push('.$sum.');
                    ';
        echo 'd3.push('.$ticket->planHours.');
                    ';
    } catch (\Exception $e) {
        // not much we can do at this point. Ignore the datapoint
    }
} ?>

        leantime.ticketsController.initTimeSheetChart(labels, d2, d3, "canvas")

    });

</script>
