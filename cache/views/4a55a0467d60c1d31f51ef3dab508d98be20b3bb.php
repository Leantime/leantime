<?php if($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor, true)): ?>

    <li class='timerHeadMenu' id='timerHeadMenu' hx-get="<?php echo e(BASE_URL); ?>/timesheets/stopwatch/get-status" hx-trigger="timerUpdate from:body">

    <?php if($onTheClock !== false|null): ?>



            <a
                href='javascript:void(0);'
                class='dropdown-toggle'
                data-toggle='dropdown'

            ><?php echo sprintf(
                    __('text.timer_on_todo'),
                    $onTheClock['totalTime'],
                    substr($onTheClock['headline'], 0, 10)
                ); ?></a>

            <ul class="dropdown-menu">
                <li>
                    <a href="#/tickets/showTicket/<?php echo e($onTheClock['id']); ?>">
                        <?php echo __('links.view_todo'); ?>

                    </a>
                </li>
                <li>
                    <a
                        href="javascript:void(0);"
                        class="punchOut"
                        hx-patch="<?php echo e(BASE_URL); ?>/hx/timesheets/stopwatch/stop-timer/"
                        hx-target="#timerHeadMenu"
                        hx-vals='{"ticketId": "<?php echo e($onTheClock['id']); ?>", "action":"stop"}'
                        hx-swap="outerHTML"
                    ><?php echo __('links.stop_timer'); ?></a>
                </li>
            </ul>

    <?php endif; ?>

    </li>

<?php endif; ?>

<?php /**PATH /home/lucas/code/leantime/app/Domain/Timesheets/Templates/partials/stopwatch.blade.php ENDPATH**/ ?>