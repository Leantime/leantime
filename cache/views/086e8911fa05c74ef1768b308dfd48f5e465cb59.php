<?php $tpl->dispatchTplEvent('beforeFooterOpen'); ?>

<div class="footer">

    <?php $tpl->dispatchTplEvent('afterFooterOpen'); ?>

    <div class="row">
        <div class="col-md-6">
            © <?php echo e(date("Y")); ?> by <a href="http://leantime.io" target="_blank">Leantime</a>
        </div>
        <div class="col-md-6 align-right">
            <a href="http://leantime.io" target="_blank">
                <img
                    style="height: 18px; opacity:0.5; vertical-align:sub;"
                    src="<?php echo BASE_URL; ?>/dist/images/logo-powered-by-leantime.png"
                />
                <span style="color:var(--primary-font-color); opacity:0.5;">v<?php echo e($version); ?></span>
            </a>
        </div>
    </div>



    <?php $tpl->dispatchTplEvent('beforeFooterClose'); ?>

</div>

<?php $tpl->dispatchTplEvent('afterFooter'); ?>
<?php /**PATH /home/lucas/code/leantime/app/Views/Templates/sections/footer.blade.php ENDPATH**/ ?>