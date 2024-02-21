<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'count' => 1,
    'includeHeadline' => false,
    'type' => 'text'
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'count' => 1,
    'includeHeadline' => false,
    'type' => 'text'
]); ?>
<?php foreach (array_filter(([
    'count' => 1,
    'includeHeadline' => false,
    'type' => 'text'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php if($includeHeadline == 'true'): ?>
    <div class="loading-text">
        <p style="width:40%">Loading...</p>
        <br />
    </div>
    <br />
<?php endif; ?>

<?php if($type == 'card'): ?>
    <?php for($i = 0; $i < $count; $i++): ?>
        <div class="loading-text tw-w-full">
            <div class="row tw-mb-l">
                <div class="col-md-6">
                    <p style="width:30%">Loading...</p>
                    <p style="width:60%">Loading...</p>
                    <p style="width:20%">Loading...</p>
                </div>
                <div class="col-md-6 tw-text-right">
                    <p style="width:5%" class="tw-float-right">Loading...</p><div class="clearall"></div>
                    <div class="clearall"></div><br />
                    <p style="width:20%" class="tw-float-right tw-ml-sm">Loading...</p>&nbsp;<p style="width:25%" class="tw-float-right tw-ml-sm">Loading...</p>&nbsp;<p style="width:10%" class="tw-float-right tw-ml-sm">Loading...</p>
                </div>
            </div>
        </div>
    <?php endfor; ?>
<?php endif; ?>

<?php if($type == 'text'): ?>
    <?php for($i = 0; $i < $count; $i++): ?>
        <div class="loading-text">
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
        </div>
    <?php endfor; ?>
<?php endif; ?>

<?php if($type == 'longtext'): ?>
    <?php for($i = 0; $i < $count; $i++): ?>
        <div class="loading-text">
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
            <br />
            <p style="width:60%">Loading...</p>
            <p style="width:65%">Loading...</p>
            <p style="width:55%">Loading...</p>
            <p style="width:50%">Loading...</p>
            <p style="width:20%">Loading...</p>
            <br />
            <p style="width:90%">Loading...</p>
            <p style="width:90%">Loading...</p>
        </div>
    <?php endfor; ?>
<?php endif; ?>

<?php if($type == 'line'): ?>
    <?php for($i = 0; $i < $count; $i++): ?>
        <div class="loading-text">
            <p style="width:40%">Loading...</p>
            <br />
        </div>
    <?php endfor; ?>
<?php endif; ?>

<?php if($type == 'project'): ?>
    <?php for($i = 0; $i < $count; $i++): ?>
        <div class="loading-text">
            <p style="margin-left:10px; margin-right:10px; width:30px; height:30px; float:left;">Loading...</p>
            <p style="width:200px; margin-left:50px;"></p>
            <br />
        </div>
    <?php endfor; ?>
<?php endif; ?>

<?php if($type == 'plugincard'): ?>
    <div class="row">
    <?php for($i = 0; $i < $count; $i++): ?>
        <div class="col-md-4">
            <div class="loading-text">
                <div class="row tw-mb-l">
                    <div class="col-md-12">
                        <p style="width:100%; height:80px;">Loading...</p>
                    </div>
                </div>
                <div class="row tw-mb-l">
                    <div class="col-md-6">
                        <p style="width:60%">Loading...</p>
                        <p style="width:20%">Loading...</p>
                    </div>
                    <div class="col-md-6 tw-text-right">
                        <p style="width:5%" class="tw-float-right">Loading...</p><div class="clearall"></div>
                        <div class="clearall"></div><br />
                        <p style="width:20%" class="tw-float-right tw-ml-sm">Loading...</p>&nbsp;<p style="width:25%" class="tw-float-right tw-ml-sm">Loading...</p>&nbsp;<p style="width:10%" class="tw-float-right tw-ml-sm">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor; ?>
    </div>
<?php endif; ?>


<?php /**PATH /home/lucas/code/leantime/app/Views/Templates/components/loadingText.blade.php ENDPATH**/ ?>