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

<div class="row" style="height:100vh; width: 99%;">
    <div class="col-md-4 hidden-phone regLeft">

        <div class="logo">
            <a href="<?php echo BASE_URL; ?>" target="_blank"><img src="<?php echo e(BASE_URL); ?>/dist/images/logo.svg" /></a>
        </div>

        <div class="welcomeContent">
            <?php echo $tpl->dispatchTplFilter('welcomeText', '<h1 class="mainWelcome">'.$language->__("headlines.welcome_back").'</h1>'); ?>
        </div>

        <?php echo $tpl->dispatchTplFilter('belowWelcomeText', ''); ?>

    </div>
    <div class="col-md-8 col-sm-12 regRight">

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
<?php /**PATH C:\xampp82\htdocs\leantime/app/Views/Templates/layouts/entry.blade.php ENDPATH**/ ?>