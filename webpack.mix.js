const pjson = require('./package.json');
const glob = require('glob');
const path = require('path');
const version = pjson.version;

let mix = require('laravel-mix');
require('laravel-mix-eslint');

mix
    .setPublicPath('public/dist') // this is the URL to place assets referenced in the CSS/JS
    .setResourceRoot('/dist') // this is what to prefix the URL with
    .js('./public/assets/js/libs/prism/prism.js', `public/dist/js/compiled-footer.${version}.min.js`)
    .combine([
        "./public/assets/js/app/app.js",
        "./public/assets/js/app/core/modals.js",
        "./public/assets/js/app/core/custom.js",
        "./public/assets/js/app/core/tableHandling.js",
        "./public/assets/js/app/core/wysiwyg.js",
        ...[
            ...glob.sync("./app/domain/**/*.js"),
            ...glob.sync("./custom/domain/**/*.js"),
            ...glob.sync("./app/plugin/**/*.js"),
            ...glob.sync("./custom/plugin/**/*.js"),
        ].map(f => `./${f}`)
    ], `public/dist/js/compiled-app.${version}.min.js`)
    .combine([
        "./node_modules/jquery/dist/jquery.min.js",
        "./node_modules/jquery-migrate/dist/jquery-migrate.min.js",
        "./node_modules/jquery-ui-dist/jquery-ui.js",
        "./node_modules/jquery-ui-touch-punch/jquery.ui.touch-punch.js",
        "./node_modules/moment/moment.js",
        "./node_modules/js-cookie/dist/js.cookie.js",
        "./node_modules/@popperjs/core/dist/umd/popper.js",
        "./node_modules/tippy.js/dist/tippy-bundle.umd.js",
        "./node_modules/chosen-js/chosen.jquery.js",
        "./public/assets/js/libs/bootstrap.min.js",
        //"./public/assets/js/libs/bootstrap-timepicker.min.js",
        "./public/assets/js/libs/bootstrap-fileupload.min.js",
        "./public/assets/js/libs/jquery.growl.js",
        "./public/assets/js/libs/slimselect.min.js",
        "./public/assets/js/libs/jquery.form.js",
        "./public/assets/js/libs/jquery.tagsinput.min.js",
        "./public/assets/js/libs/confetti/js/confetti.js",
        "./node_modules/fullcalendar/index.global.min.js",
    ], `public/dist/js/compiled-base-libs.${version}.min.js`)
    .combine([
        "./node_modules/croppie/croppie.js",
        "./node_modules/chart.js/dist/chart.js",
        "./node_modules/chartjs-adapter-moment/dist/chartjs-adapter-moment.js",
        "./node_modules/packery/dist/packery.pkgd.js",
        "./node_modules/imagesloaded/imagesloaded.pkgd.js",
        "./node_modules/tether-shepherd/dist/js/tether.js",
        "./node_modules/tether-shepherd/dist/js/shepherd.js",
        "./node_modules/datatables.net/js/jquery.dataTables.js",
        "./node_modules/datatables.net-rowgroup/js/dataTables.rowGroup.js",
        "./node_modules/datatables.net-rowreorder/js/dataTables.rowReorder.js",
        "./node_modules/datatables.net-buttons/js/dataTables.buttons.js",
        "./node_modules/datatables.net-buttons/js/buttons.html5.js",
        "./node_modules/datatables.net-buttons/js/buttons.print.js",
        "./node_modules/datatables.net-buttons/js/buttons.colVis.js",
        "./node_modules/tinymce/tinymce.js",
        "./node_modules/tinymce/icons/default/icons.js",
        "./node_modules/tinymce/jquery.tinymce.js",
        "./node_modules/tinymce/themes/silver/theme.js",
        "./node_modules/tinymce/plugins/autolink/plugin.js",
        "./node_modules/tinymce/plugins/link/plugin.js",
        "./node_modules/tinymce/plugins/textcolor/plugin.js",
        "./node_modules/tinymce/plugins/image/plugin.js",
        "./node_modules/tinymce/plugins/imagetools/plugin.js",
        "./node_modules/tinymce/plugins/lists/plugin.js",
        "./node_modules/tinymce/plugins/save/plugin.js",
        "./node_modules/tinymce/plugins/autosave/plugin.js",
        "./node_modules/tinymce/plugins/media/plugin.js",
        "./node_modules/tinymce/plugins/searchreplace/plugin.js",
        "./node_modules/tinymce/plugins/paste/plugin.js",
        "./node_modules/tinymce/plugins/directionality/plugin.js",
        "./node_modules/tinymce/plugins/fullscreen/plugin.js",
        "./node_modules/tinymce/plugins/noneditable/plugin.js",
        "./node_modules/tinymce/plugins/visualchars/plugin.js",
        "./node_modules/tinymce/plugins/emoticons/plugin.js",
        "./node_modules/tinymce/plugins/emoticons/js/emojis.min.js",
        "./node_modules/tinymce/plugins/advlist/plugin.js",
        "./node_modules/tinymce/plugins/autoresize/plugin.js",
        "./node_modules/tinymce/plugins/codesample/plugin.js",
        "./node_modules/isotope-layout/dist/isotope.pkgd.js",
        "./node_modules/jstree/dist/jstree.js",
        "./node_modules/@assuradeurengilde/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js",
        "./node_modules/leader-line/leader-line.min.js",
        "./public/assets/js/libs/tinymce-plugins/helper.js",
        "./public/assets/js/libs/tinymce-plugins/checklist/index.js",
        "./public/assets/js/libs/tinymce-plugins/shortlink/index.js",
        "./public/assets/js/libs/tinymce-plugins/table/plugin.js",
        "./public/assets/js/libs/tinymce-plugins/bettertable/index.js",
        "./public/assets/js/libs/tinymce-plugins/collapsibleheaders/index.js",
        "./public/assets/js/libs/tinymce-plugins/embed/index.js",
        "./public/assets/js/libs/tinymce-plugins/slashcommands/slashcommands.js",
        "./public/assets/js/libs/tinymce-plugins/mention/plugin.js",
        "./public/assets/js/libs/tinymce-plugins/advancedTemplate/plugin.js",
        "./public/assets/js/libs/simple-color-picker-master/jquery.simple-color-picker.js",
        "./public/assets/js/libs/simpleGantt/moment.min.js",
        "./public/assets/js/libs/simpleGantt/snap.svg-min.js",
        "./public/assets/js/libs/simpleGantt/frappe-gantt.min.js",
        "./public/assets/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js",
        "./public/assets/js/libs/uppy/uppy.js",
    ], `public/dist/js/compiled-extended-libs.${version}.min.js`)
    .less('./public/assets/less/main.less', `public/dist/css/main.${version}.min.css`)
    .copy('./public/assets/images', 'public/dist/images')
    .copy('./public/assets/fonts', 'public/dist/fonts')
    .eslint({
        fix: true,
        extensions: ['js'],
        exclude: [
            'node_modules',
            'public/assets/js/libs',
        ],
    })
    .webpackConfig({
        resolve: {
            alias: {
                'images': path.resolve(__dirname, 'public/assets/images'),
                'js': path.resolve(__dirname, 'public/assets/js'),
                'css': path.resolve(__dirname, 'public/assets/css'),
                'fonts': path.resolve(__dirname, 'public/assets/fonts'),
            }
        },
    });
