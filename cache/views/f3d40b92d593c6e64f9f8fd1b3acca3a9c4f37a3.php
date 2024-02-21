<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'parentTicketId' => false,
    'onTheClock' => false
 ]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'parentTicketId' => false,
    'onTheClock' => false
 ]); ?>
<?php foreach (array_filter(([
    'parentTicketId' => false,
    'onTheClock' => false
 ]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<li id="timerContainer-<?php echo e($parentTicketId); ?>"
    hx-get="<?php echo e(BASE_URL); ?>/tickets/timerButton/get-status/<?php echo e($parentTicketId); ?>"
    hx-trigger="timerUpdate from:body"
    hx-swap="outerHTML"
    class="timerContainer">

    <?php if($onTheClock === false): ?>
        <a href="javascript:void(0);" data-value="<?php echo e($parentTicketId); ?>"
           hx-patch="<?php echo e(BASE_URL); ?>/hx/timesheets/stopwatch/start-timer/"
           hx-target="#timerHeadMenu"
           hx-swap="outerHTML"
           hx-vals='{"ticketId": "<?php echo e($parentTicketId); ?>", "action":"start"}'>
            <span class="fa-regular fa-clock"></span> <?php echo e(__("links.start_work")); ?>

        </a>
    <?php endif; ?>

    <?php if($onTheClock !== false && $onTheClock["id"] == $parentTicketId): ?>
    <a href="javascript:void(0);" data-value="<?php echo e($parentTicketId); ?>"
       hx-patch="<?php echo e(BASE_URL); ?>/hx/timesheets/stopwatch/stop-timer/"
       hx-target="#timerHeadMenu"
       hx-vals='{"ticketId": "<?php echo e($parentTicketId); ?>", "action":"stop"}'
       hx-swap="outerHTML">
        <span class="fa fa-stop"></span>

        <?php if(is_array($onTheClock) == true): ?>
            <?php echo sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), $onTheClock["since"])); ?>

        <?php else: ?>
            <?php echo sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), time())); ?>

        <?php endif; ?>
    </a>
    <?php endif; ?>
    <?php if($onTheClock !== false && $onTheClock["id"] != $parentTicketId): ?>
        <span class='working'>
            <?php echo e(__("text.timer_set_other_todo")); ?>

        </span>
    <?php endif; ?>
</li>

<?php /**PATH /home/lucas/code/leantime/app/Domain/Tickets/Templates/partials/timerLink.blade.php ENDPATH**/ ?>