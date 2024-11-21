<?php
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$values = $tpl->get('timesheetValues');
$ticket = $tpl->get('ticket');
$userInfo = $tpl->get('userInfo');
$remainingHours = $tpl->get('remainingHours');
if ($remainingHours < 0) {
    $remainingHours = 0;
}
$currentPay = $tpl->get('userHours') * $userInfo['wage'];
?>

        <div class="row">
            <div class="col-md-6">


                <h4 class="widgettitle title-light"><span class="fa fa-clock-o"></span><?php echo $tpl->__('headline.add_time_entry', false); ?></h4>
                <br />

                <form method="post" action="<?=BASE_URL . "/tickets/showTicket/" . $ticket->id . ""?>#timesheet" class="formModal">

                    <x-global::forms.select 
                    id="kind" 
                    name="kind" 
                    labelText="{!! __('label.timesheet_kind') !!}"
                >
                    @foreach ($tpl->get('kind') as $key => $row)
                        <x-global::forms.select.select-option 
                            value="{{ $key }}" 
                            :selected="$row == $values['kind']">
                            {!! __('' . strtolower($row)) !!}
                        </x-global::forms.select.select-option>
                    @endforeach
                </x-global::forms.select>
                
                    </span>

                    <label for="timesheetdate">{{ __("label.date") }}:</label>
                    <input type="text" id="timesheetdate" name="date" class="dates" value="<?php echo format($values['date'])->date() ?>" /><br/>

                    <label for="hours">{{ __("label.hours") }}</label>
                    <span class="field">
                        <input type="text" id="hours" name="hours" value="<?php echo $values['hours'] ?>" size="7" class="input-small" />
                    </span>
                    <label for="description">{{ __("label.description") }}</label>
                    <span class="field">
                        <textarea rows="5" cols="50" id="description" name="description"><?php echo $values['description']; ?></textarea><br />
                    </span>
                    <input type="hidden" name="saveTimes" value="1" />
                    <input type="submit" value="{{ __("buttons.save") }}" name="saveTimes" class="button" />

                </form>

            </div>
            <div class="col-md-6">
                <h4 class="widgettitle title-light"><span class="fa fa-bar-chart"></span>{{ __("subtitles.logged_hours_chart") }}</h4>

                <br />
                <canvas id="canvas"></canvas>
                <p><br />
                    {{ __("label.planned_hours") }}: <?php echo $ticket->planHours; ?><br />
                    {{ __("label.booked_hours") }}: <?php echo $tpl->get('timesheetsAllHours'); ?><br />
                    {{ __("label.actual_hours_remaining") }}: <?php echo $remainingHours; ?><br />
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
        $ticketHours = $tpl->get('ticketHours');
        foreach ($ticketHours as $hours) {
            $sum = $sum + $hours['summe'];

            echo"labels.push('" . date("Y-m-d", strtotime($hours['utc']??'')) . "');
                    ";
            echo"d2.push(" . $sum . ");
                    ";
            echo "d3.push(" . $ticket->planHours . ");
                    ";
        } ?>

        leantime.ticketsController.initTimeSheetChart(labels, d2, d3, "canvas")

    });

</script>