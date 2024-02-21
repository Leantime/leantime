<!DOCTYPE html>
<html dir="<?php echo e(__('language.direction')); ?>" lang="<?php echo e(__('language.code')); ?>">
<head>
    <?php echo $__env->make('global::sections.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <style>
        .leantimeLogo { position: fixed; bottom: 10px; right: 10px; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="loginpage" style="height:100%;">

<div class="header hidden-gt-sm tw-p-[10px]" style="background:var(--header-gradient)">
    <a href="<?php echo BASE_URL; ?>" target="_blank">
        <img src="<?php echo e(BASE_URL); ?>/dist/images/logo.svg" class="tw-h-full "/>
    </a>
</div>

<div class="row " style="height:100%; width: 99%;">
    <div class="col-md-6 hidden-phone regLeft">

        <div class="logo tw-absolute tw-top-[50px] tw-left-0 tw-ml-[100px] tw-p-0">
            <a href="<?php echo BASE_URL; ?>" target="_blank"><img src="<?php echo e(BASE_URL); ?>/dist/images/logo.svg" /></a>
        </div>

        <div class="row">
            <div class="col-md-12" style="position:relative;">
                <h1 class="mainWelcome">
                    <?php echo $tpl->dispatchTplFilter('welcomeText', $language->__("headlines.welcome_back")); ?>
                </h1>
                <span class="iq-objects-04 iq-fadebounce">
                    <span class="iq-round"></span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-12 regRight">

        <div class="regpanel">
            <div class="regpanelinner">

                <?php if($logoPath != ''): ?>
                    <a href="<?php echo BASE_URL; ?>" target="_blank">
                        <img src="<?php echo e($logoPath); ?>" class="tw-h-full "/>
                    </a>
                <?php endif; ?>

                <?php if(isset($action, $module)): ?>
                    <?php echo $__env->make("$module::$action", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <?php else: ?>
                    <?php echo $__env->yieldContent('content'); ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <div class="leantimeLogo">
        <img style="height: 25px;" src="<?php echo BASE_URL; ?>/dist/images/logo-powered-by-leantime.png">
    </div>
</div>

<?php echo $__env->make('global::sections.pageBottom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH /home/lucas/code/leantime/app/Views/Templates/layouts/entry.blade.php ENDPATH**/ ?>