<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'state' => $tpl->getToggleState("accordion_content-".$id) == 'closed' ? 'closed' : 'open',
    'id'
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'state' => $tpl->getToggleState("accordion_content-".$id) == 'closed' ? 'closed' : 'open',
    'id'
]); ?>
<?php foreach (array_filter(([
    'state' => $tpl->getToggleState("accordion_content-".$id) == 'closed' ? 'closed' : 'open',
    'id'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div <?php echo e($attributes->merge([ 'class' => 'accordionWrapper' ])); ?>>

    <?php if(isset($actionlink) && $actionlink != ''): ?>
        <div class="pull-right tw-pt-xs">
            <?php echo $actionlink; ?>

        </div>
    <?php endif; ?>

    <a
        href="javascript:void(0)"
        class="accordion-toggle <?php echo e($state); ?>"
        id="accordion_toggle_<?php echo e($id); ?>"
        onclick="leantime.snippets.accordionToggle('<?php echo e($id); ?>');"
    >
        <h5 <?php echo e($title->attributes->merge([
            'class' => 'accordionTitle tw-pb-15 tw-text-l',
            'id' => "accordion_link_$id"
        ])); ?>>
            <i class="fa fa-angle-<?php echo e($state == 'closed' ? 'right' : 'down'); ?>"></i>
            <?php echo $title; ?>

        </h5>
    </a>
    <div <?php echo e($content->attributes->merge([
        'class' => "simpleAccordionContainer $state",
        'id' => "accordion_content-$id",
        'style' => $state =='closed' ? 'display:none;' : ''
    ])); ?>>


        <?php echo $content; ?>

    </div>
</div>
<?php /**PATH /home/lucas/code/leantime/app/Views/Templates/components/accordion.blade.php ENDPATH**/ ?>