<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'includeTitle' => true,
    'calendar' => [],
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'includeTitle' => true,
    'calendar' => [],
]); ?>
<?php foreach (array_filter(([
    'includeTitle' => true,
    'calendar' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php $tpl->dispatchTplEvent('beforeCalendar'); ?>

<div class="tw-h-full minCalendar">
    <div class="clear"></div>
    <div class="fc-toolbar">
        <div class="fc-left pull-left">
            <div class="fc-button-group pull-left">
                <button class="btn btn-default fc-today-button fc-button fc-state-default fc-corner-left pull-left
       fc-corner-right fc-state-disabled">Today</button>

                <button class="btn btn-link fc-prev-button fc-button fc-state-default fc-corner-left pull-left"
                        type="button">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <button class="btn btn-link fc-next-button fc-button fc-state-default fc-corner-
      right pull-left" type="button">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </div>

        </div>
        <div class="fc-right pull-right">
            <div class="fc-button-group">
                <select class="calendarViewSelect">
                    <option class="fc-agendaDay-button fc-button fc-state-default fc-
           corner-right" value="multiMonthOneMonth" <?php if($tpl->getToggleState("dashboardCalendarView") == 'multiMonthOneMonth'): ?> selected='selected' <?php endif; ?>>Month</option>
                    <option class="fc-agendaWeek-button fc-button fc-state-
          default" value="timeGridDay" <?php if($tpl->getToggleState("dashboardCalendarView") == 'timeGridDay' || empty($tpl->getToggleState("dashboardCalendarView")) ): ?> selected='selected' <?php endif; ?>>Day</option>
                    <option class="fc-agendaWeek-button fc-button fc-state-
          default" value="listWeek" <?php if($tpl->getToggleState("dashboardCalendarView") == 'listWeek'): ?> selected='selected' <?php endif; ?>>List</option>
                </select>
            </div>
        </div>
        <div class="fc-center center tw-pt-[7px] calendarTitle">
            <h2></h2>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <div class="minCalendarWrapper minCalendar tw-h-full" style="height:calc(100% - 55px)"></div>
</div>

<script>

        var eventSources = [];

        var events = {events: [
            <?php $__currentLoopData = $calendar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            {

                title: <?php echo json_encode($event['title']); ?>,

                start: new Date(<?php echo e($event['dateFrom']['y'] . ',' .
                    ($event['dateFrom']['m'] - 1) . ',' .
                    $event['dateFrom']['d'] . ',' .
                    $event['dateFrom']['h'] . ',' .
                    $event['dateFrom']['i']); ?>),
                <?php if(isset($event['dateTo'])): ?>
                    end: new Date(<?php echo e($event['dateTo']['y'] . ',' .
                        ($event['dateTo']['m'] - 1) . ',' .
                        $event['dateTo']['d'] . ',' .
                        $event['dateTo']['h'] . ',' .
                        $event['dateTo']['i']); ?>),
                <?php endif; ?>
                <?php if((isset($event['allDay']) && $event['allDay'] === true)): ?>
                    allDay: true,
                <?php else: ?>
                    allDay: false,
                <?php endif; ?>
                enitityId: <?php echo e($event['id']); ?>,
                <?php if(isset($event['eventType']) && $event['eventType'] == 'calendar'): ?>
                    url: '#/calendar/editEvent/<?php echo e($event['id']); ?>',
                    backgroundColor: '<?php echo e($event['backgroundColor'] ?? "var(--accent2)"); ?>',
                    borderColor: '<?php echo e($event['borderColor'] ?? "var(--accent2)"); ?>',
                    enitityType: "event",
                <?php else: ?>
                    url: '#/tickets/showTicket/<?php echo e($event['id']); ?>?projectId=<?php echo e($event['projectId']); ?>',
                    backgroundColor: '<?php echo e($event['backgroundColor'] ?? "var(--accent2)"); ?>',
                    borderColor: '<?php echo e($event['borderColor'] ?? "var(--accent2)"); ?>',
                    enitityType: "ticket",
                <?php endif; ?>
            },
         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        ]};

        eventSources.push(events);

        <?php
        $externalCalendars = $tpl->get("externalCalendars");

        foreach($externalCalendars as $externalCalendar) { ?>
            eventSources.push(
                {
                    url: '<?=BASE_URL ?>/calendar/externalCal/<?=$externalCalendar['id'] ?>',
                    format: 'ics',
                    color: '<?=$externalCalendar['colorClass'] ?>',
                    editable: false,
                }
            );
        <?php } ?>

        var initialView =   '<?php echo e($tpl->getToggleState("dashboardCalendarView") ? $tpl->getToggleState("dashboardCalendarView") : "timeGridDay"); ?>';
        leantime.calendarController.initWidgetCalendar(".minCalendarWrapper", initialView)



    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>

</script>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Widgets/Templates/partials/calendar.blade.php ENDPATH**/ ?>