const pjson = require('./package.json');
const glob = require('glob');
const path = require('path');
const version = pjson.version;

const fs = require("fs");

// Helper to get all files of a given extension in a given directory and its subfolders.
function getFilesRecursive(dir, type) {
    // The list of files that we will return.
    let files = []
    // Loop everything in given location.
    fs.readdirSync(dir).forEach(file => {
        let fileName = `${dir}/${file}`
        // Add if its a file and it is of the correct file type.
        if(fs.statSync(fileName).isFile() && fileName.endsWith(type)) {
            files.push(fileName)
        }
        // Process subfolder.
        if(!fs.statSync(fileName).isFile()) {
            // Recusively loop this function for the subfolder.
            files = files.concat(getFilesRecursive(fileName, type))
        }
    })
    return files
}



let mix = require('laravel-mix');
require('laravel-mix-eslint');
require('mix-tailwindcss');

require('dotenv').config({ path: 'config/.env' });

mix
    .setPublicPath('public/dist')
    .setResourceRoot(`../`);

/*

//Draft for file based js controller loading
getFilesRecursive('app/Domain', '.js').forEach(file => {
    subfolder = file.match(/(.*)[\/\\]/)[1]||''; // 'src/js/libraries'
    subfolder = subfolder.replace('app/Domain', ''); // '/libraries'
    mix.js(file, 'js' + subfolder);
});
*/


 // this is the URL to place assets referenced in the CSS/JS
    mix // this is what to prefix the URL with
    .combine('./public/assets/js/libs/prism/prism.js', `public/dist/js/compiled-footer.${version}.min.js`)
    .js('./public/assets/js/app/htmx.js', `public/dist/js/compiled-htmx.${version}.min.js`)
    .js('./public/assets/js/app/htmx-headSupport.js', `public/dist/js/compiled-htmx-headSupport.${version}.min.js`)
    .combine([
        "./public/assets/js/app/app.js",
        "./public/assets/js/app/core/editors.js",
        "./public/assets/js/app/core/snippets.js",
        "./public/assets/js/app/core/modals.js",
        "./public/assets/js/app/core/tableHandling.js",
        "./public/assets/js/app/core/datePickers.js",
        "./public/assets/js/app/core/dateHelper.js",
        ...glob.sync("./app/Domain/**/*.js").map(f => `./${f}`)
    ], `public/dist/js/compiled-app.${version}.min.js`)
    .combine([
        "./node_modules/jquery/dist/jquery.js",
        "./public/assets/js/libs/bootstrap.min.js",
    ], `public/dist/js/compiled-frameworks.${version}.min.js`)
    .combine([
        "./node_modules/jquery-ui-dist/jquery-ui.js",
        "./node_modules/jquery-ui-touch-punch/jquery.ui.touch-punch.js",
        "./node_modules/chosen-js/chosen.jquery.js",
        "./public/assets/js/libs/jquery.growl.js",
        "./public/assets/js/libs/jquery.form.js",
        "./public/assets/js/libs/jquery.tagsinput.min.js",
        "./public/assets/js/libs/bootstrap-fileupload.min.js",
    ], `public/dist/js/compiled-framework-plugins.${version}.min.js`)
    .combine([
        "./node_modules/luxon/build/global/luxon.js",
        "./node_modules/moment/moment.js",
        "./public/assets/js/libs/jquery.form.js",
        "./node_modules/@popperjs/core/dist/umd/popper.js",
        "./node_modules/tippy.js/dist/tippy-bundle.umd.js",
        "./public/assets/js/libs/slimselect.min.js",
        "./node_modules/canvas-confetti/dist/confetti.browser.js",
        "./public/assets/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js",
        "./public/assets/js/libs/uppy/uppy.js",
        "./node_modules/croppie/croppie.js",
        "./node_modules/packery/dist/packery.pkgd.js",
        "./node_modules/imagesloaded/imagesloaded.pkgd.js",
        "./node_modules/shepherd.js/dist/js/shepherd.js",
        "./node_modules/isotope-layout/dist/isotope.pkgd.js",
        "./node_modules/gridstack/dist/gridstack-all.js",
        "./node_modules/jstree/dist/jstree.js",
        "./node_modules/@assuradeurengilde/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js",
        "./node_modules/leader-line/leader-line.min.js",
        "./public/assets/js/libs/simple-color-picker-master/jquery.simple-color-picker.js",
        "./public/assets/js/libs/emojipicker/vanillaEmojiPicker.js",
    ], `public/dist/js/compiled-global-component.${version}.min.js`)
    .combine([
        "./node_modules/ical.js/build/ical.min.js",
        "./node_modules/fullcalendar/index.global.min.js",
        "./node_modules/@fullcalendar/icalendar/index.global.min.js",
        "./node_modules/@fullcalendar/google-calendar/index.global.min.js",
        "./node_modules/@fullcalendar/luxon3/index.global.min.js",

    ], `public/dist/js/compiled-calendar-component.${version}.min.js`)
    .combine([
        "./node_modules/datatables.net/js/jquery.dataTables.js",
        "./node_modules/datatables.net-rowgroup/js/dataTables.rowGroup.js",
        "./node_modules/datatables.net-rowreorder/js/dataTables.rowReorder.js",
        "./node_modules/datatables.net-buttons/js/dataTables.buttons.js",
        "./node_modules/datatables.net-buttons/js/buttons.html5.js",
        "./node_modules/datatables.net-buttons/js/buttons.print.js",
        "./node_modules/datatables.net-buttons/js/buttons.colVis.js",
    ], `public/dist/js/compiled-table-component.${version}.min.js`)
    .combine([
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
        "./node_modules/tinymce/plugins/textpattern/plugin.js",
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
    ], `public/dist/js/compiled-editor-component.${version}.min.js`)
    .combine([
        "./public/assets/js/libs/simpleGantt/snap.svg-min.js",
        "./public/assets/js/libs/simpleGantt/frappe-gantt.js",
    ], `public/dist/js/compiled-gantt-component.${version}.min.js`)
    .combine([
        "./node_modules/chart.js/dist/chart.js",
        "./node_modules/chartjs-adapter-luxon/dist/chartjs-adapter-luxon.umd.js",
    ], `public/dist/js/compiled-chart-component.${version}.min.js`)
    .less('./public/assets/less/main.less', `public/dist/css/main.${version}.min.css`, {
        sourceMap: true,
    })
    .less('./public/assets/less/editor.less', `public/dist/css/editor.${version}.min.css`, {
        sourceMap: true,
    })
    .less('./public/assets/less/app.less', `public/dist/css/app.${version}.min.css`, {
        sourceMap: true,
    })
    .tailwind()
    .copy('./public/assets/images', 'public/dist/images')
    .copy('./public/assets/fonts', 'public/dist/fonts')
    .copy('./public/assets/lottie', 'public/dist/lottie')
    .copy('./public/assets/css/libs/tinymceSkin/oxide', 'public/dist/css/libs/tinymceSkin/oxide')
    .eslint({
        fix: true,
        extensions: ['js'],
        exclude: [
            'node_modules',
            'public/assets/js/libs',
        ],
        overrideConfig: {
            parser: '@babel/eslint-parser',
        }
    })
    .webpackConfig({
        devtool: 'inline-source-map',
        resolve: {
            alias: {
                'images': path.resolve(__dirname, 'public/assets/images'),
                'js': path.resolve(__dirname, 'public/assets/js'),
                'css': path.resolve(__dirname, 'public/assets/css'),
                'fonts': path.resolve(__dirname, 'public/assets/fonts')
            }
        }
    });




