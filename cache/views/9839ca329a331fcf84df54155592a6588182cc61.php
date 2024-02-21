<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'includeTitle' => true,
    'randomImage' => '',
    'totalTickets' => 0,
    'projectCount' => 0,
    'closedTicketsCount' => 0,
    'ticketsInGoals' => 0,
    'doneTodayCount' => 0,
    'totalTodayCount' => 0,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'includeTitle' => true,
    'randomImage' => '',
    'totalTickets' => 0,
    'projectCount' => 0,
    'closedTicketsCount' => 0,
    'ticketsInGoals' => 0,
    'doneTodayCount' => 0,
    'totalTodayCount' => 0,
]); ?>
<?php foreach (array_filter(([
    'includeTitle' => true,
    'randomImage' => '',
    'totalTickets' => 0,
    'projectCount' => 0,
    'closedTicketsCount' => 0,
    'ticketsInGoals' => 0,
    'doneTodayCount' => 0,
    'totalTodayCount' => 0,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div class="">

    <div style="padding:10px 0px">

        <div class="center">
            <span style="font-size:44px; color:var(--main-titles-color);">
                <?php
                    $date = new DateTime();
                    if(!empty($_SESSION['usersettings.timezone'])){
                        $date->setTimezone(new DateTimeZone($_SESSION['usersettings.timezone']));
                    }
                    $date = $date->format(__("language.timeformat"));
                ?>

                <?php echo e($date); ?>

            </span><br />
            <span style="font-size:24px; color:var(--main-titles-color);">
                <?php echo e(__("welcome_widget.hi")); ?> <?php echo e($currentUser['firstname']); ?>

            </span><br />
            <?php $tpl->dispatchTplEvent('afterGreeting'); ?>
            <br />
        </div>

        <div class="tw-flex tw-gap-x-[10px]">

            <div class="bigNumberBox tw-flex-1 tw-flex-grow">
                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">⏱️ <?php echo e($doneTodayCount); ?>/<?php echo e($totalTodayCount); ?> </div>
                    <div class="bigNumberBoxText"><?php echo e(__("welcome_widget.timeboxed_completed")); ?></div>
                </div>
            </div>

            <div class="bigNumberBox tw-flex-1 tw-flex-grow">
                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">🥳 <?php echo e($closedTicketsCount); ?> </div>
                    <div class="bigNumberBoxText"><?php echo e(__("welcome_widget.tasks_completed")); ?></div>
                </div>
            </div>

            <div class="bigNumberBox tw-flex-1 tw-flex-grow ">

                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">📥 <?php echo e($totalTickets); ?> </div>
                    <div class="bigNumberBoxText"><?php echo e(__("welcome_widget.tasks_left")); ?></div>
                </div>
            </div>

            <div class="bigNumberBox tw-flex-1 tw-flex-grow">

                <div class="bigNumberBoxInner">
                    <div class="bigNumberBoxNumber">🎯 <?php echo e($ticketsInGoals); ?> </div>
                    <div class="bigNumberBoxText"><?php echo e(__("welcome_widget.goals_contributing_to")); ?></div>
                </div>
            </div>

        </div>
    </div>

    <div class="clear"></div>

    <?php $tpl->dispatchTplEvent('afterWelcomeMessage'); ?>

    <div class="clear"></div>

</div>

<?php $tpl->dispatchTplEvent('afterWelcomeMessageBox'); ?>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Widgets/Templates/partials/welcome.blade.php ENDPATH**/ ?>