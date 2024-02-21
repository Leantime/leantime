<!DOCTYPE html>
<html dir="<?php echo e(__('language.direction')); ?>" lang="<?php echo e(__('language.code')); ?>">
<head>
    <?php echo $__env->make('global::sections.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>

    <?php echo $__env->make('global::sections.appAnnouncement', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="mainwrapper menu<?php echo e($_SESSION['menuState'] ?? "closed"); ?>">

        <div class="header">

            <div class="headerinner">
                <a class="btnmenu" href="javascript:void(0);"></a>

                <a class="barmenu" href="javascript:void(0);">
                    <span class="fa fa-bars"></span>
                </a>

                <div class="logo">
                    <a
                        href="<?php echo e(BASE_URL); ?>"
                        style="background-image: url('<?php echo e(BASE_URL); ?>/dist/images/logo.svg')"
                    >&nbsp;</a>
                </div>

                <?php echo $__env->make('menu::headMenu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div><!-- headerinner -->

        </div><!-- header -->



        <div class="" style="position: relative">
            <div class="leftpanel">
                <div class="leftmenu">
                    <?php echo $__env->make('menu::menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div><!-- leftmenu -->
            </div>
            <div class="rightpanel <?php echo e($section); ?>">
                <div class="primaryContent">
                    <?php if(isset($action, $module)): ?>
                        <?php echo $__env->make("$module::$action", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <?php else: ?>
                        <?php echo $__env->yieldContent('content'); ?>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                    <?php echo $__env->make('global::sections.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div><!-- rightpanel -->

    </div><!-- mainwrapper -->

    <?php echo $__env->make('global::sections.pageBottom', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
    <?php echo $__env->make('help::helpermodal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</body>

</html>
<?php /**PATH /home/lucas/code/leantime/app/Views/Templates/layouts/app.blade.php ENDPATH**/ ?>