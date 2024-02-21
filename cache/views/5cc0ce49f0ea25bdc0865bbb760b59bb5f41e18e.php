<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    "size",
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    "size",
]); ?>
<?php foreach (array_filter(([
    "size",
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div style="
        display:inline-block;
        width:<?php echo e($size); ?>;
        height: <?php echo e($size); ?>;
        vertical-align: middle;
        background:url(<?php echo e(BASE_URL); ?>/dist/images/loading-animation.svg);
        background-size: contain;"></div>
<?php /**PATH /home/lucas/code/leantime/app/Views/Templates/components/loader.blade.php ENDPATH**/ ?>