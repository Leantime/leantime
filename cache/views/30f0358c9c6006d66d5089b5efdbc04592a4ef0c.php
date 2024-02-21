<title><?php echo $tpl->dispatchTplFilter('page_title', $sitename); ?></title>

<meta name="description" content="<?php echo e($sitename); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="theme-color" content="<?php echo e($primaryColor); ?>">
<meta name="color-scheme" content="<?php echo e($themeColorMode); ?>">
<meta name="theme" content="<?php echo e($theme); ?>">
<meta name="identifier-URL" content="<?php echo BASE_URL; ?>">
<meta name="leantime-version" content="<?php echo e($version); ?>">

<?php $tpl->dispatchTplEvent('afterMetaTags'); ?>

<link rel="shortcut icon" href="<?php echo BASE_URL; ?>/dist/images/favicon.png"/>
<link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>/dist/images/apple-touch-icon.png">

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/dist/css/main.<?php echo $version; ?>.min.css"/>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/dist/css/app.<?php echo $version; ?>.min.css" />

<?php $tpl->dispatchTplEvent('afterLinkTags'); ?>

<script src="<?php echo BASE_URL; ?>/api/i18n?v=<?php echo $version; ?>"></script>

<script src="<?php echo BASE_URL; ?>/dist/js/compiled-htmx.<?php echo $version; ?>.min.js"></script>

<!-- libs -->
<script src="<?php echo BASE_URL; ?>/dist/js/compiled-frameworks.<?php echo $version; ?>.min.js"></script>
<script src="<?php echo BASE_URL; ?>/dist/js/compiled-framework-plugins.<?php echo $version; ?>.min.js"></script>
<script src="<?php echo BASE_URL; ?>/dist/js/compiled-global-component.<?php echo $version; ?>.min.js"></script>
<script src="<?php echo BASE_URL; ?>/dist/js/compiled-calendar-component.<?php echo $version; ?>.min.js"></script>
<script src="<?php echo BASE_URL; ?>/dist/js/compiled-table-component.<?php echo $version; ?>.min.js"></script>
<script src="<?php echo BASE_URL; ?>/dist/js/compiled-editor-component.<?php echo $version; ?>.min.js"></script>
<script src="<?php echo BASE_URL; ?>/dist/js/compiled-gantt-component.<?php echo $version; ?>.min.js"></script>
<script src="<?php echo BASE_URL; ?>/dist/js/compiled-chart-component.<?php echo $version; ?>.min.js"></script>

<?php $tpl->dispatchTplEvent('afterScriptLibTags'); ?>

<!-- app -->
<script src="<?php echo BASE_URL; ?>/dist/js/compiled-app.<?php echo $version; ?>.min.js"></script>
<?php $tpl->dispatchTplEvent('afterMainScriptTag'); ?>

<!-- theme & custom -->
<?php $__currentLoopData = $themeScripts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $script): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <script src="<?php echo $script; ?>"></script>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__currentLoopData = $themeStyles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $style): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <link rel="stylesheet" <?php if(isset($style['id'])): ?> id="<?php echo e($style['id']); ?>" <?php endif; ?> href="<?php echo $style['url']; ?>" />
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $tpl->dispatchTplEvent('afterScriptsAndStyles'); ?>

<!-- Replace main theme colors -->
<style id="colorSchemeSetter">
    <?php $__currentLoopData = $accents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $accent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($accent !== false): ?>
           :root { --accent<?php echo e($loop->iteration); ?>: <?php echo e($accent); ?>; }
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</style>

<style id="fontStyleSetter">:root { --primary-font-family: '<?php echo e($themeFont); ?>', 'Helvetica Neue', Helvetica, sans-serif; }</style>

<?php $tpl->dispatchTplEvent('afterThemeColors'); ?>
<?php /**PATH /home/lucas/code/leantime/app/Views/Templates/sections/header.blade.php ENDPATH**/ ?>