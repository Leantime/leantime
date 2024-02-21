<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'ticket' => false,
    'onTheClock' => false
 ]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'ticket' => false,
    'onTheClock' => false
 ]); ?>
<?php foreach (array_filter(([
    'ticket' => false,
    'onTheClock' => false
 ]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor)): ?>

    <div class="inlineDropDownContainer" style="float:right;">

        <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
        </a>
        <ul class="dropdown-menu">
            <li class="nav-header"><?php echo e(__("subtitles.todo")); ?></li>
            <li><a href="#/tickets/showTicket/<?php echo e($ticket["id"]); ?>" class=''><i class="fa fa-edit"></i> <?php echo e(__("links.edit_todo")); ?></a></li>
            <li><a href="#/tickets/moveTicket/<?php echo e($ticket["id"]); ?>" class=""><i class="fa-solid fa-arrow-right-arrow-left"></i> <?php echo e(__("links.move_todo")); ?></a></li>
            <li><a href="#/tickets/delTicket/<?php echo e($ticket["id"]); ?>" class="delete"><i class="fa fa-trash"></i> <?php echo e(__("links.delete_todo")); ?></a></li>
            <li class="nav-header border"><?php echo e(__("subtitles.track_time")); ?></li>
            <?php echo $__env->make('tickets::partials.timerLink', ['parentTicketId' => $ticket['id'], 'onTheClock' => $onTheClock], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </ul>
    </div>

<?php endif; ?>
<?php /**PATH /home/lucas/code/leantime/app/Domain/Tickets/Templates/partials/ticketsubmenu.blade.php ENDPATH**/ ?>