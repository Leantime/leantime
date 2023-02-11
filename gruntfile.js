module.exports = function (grunt) {
    grunt.loadNpmTasks("grunt-contrib-jshint");
    grunt.loadNpmTasks("grunt-contrib-uglify");
    grunt.loadNpmTasks("grunt-contrib-less");
    grunt.loadNpmTasks('grunt-exec');
    grunt.initConfig({
        uglify: {
            app_src: {
                options: {
                    sourceMap: true,
                    sourceMapName: "public/js/jsSourceMapAppSrc.map",
                    sourceMapUrl: "jsSourceMapAppSrc.map",
                    mangle: false
                },
                src: [
                    "public/js/app/app.js",
                    "public/js/app/core/modals.js",
                    "public/js/app/core/custom.js",
                    "public/js/app/core/tableHandling.js",
                    "public/js/app/core/wysiwyg.js",
                    "app/domain/**/*.js",
                    "custom/domain/**/*.js",
                    "app/plugin/**/*.js",
                    "custom/plugin/**/*.js"
                ],
                dest: "public/js/compiled-app.min.js"
            },
            base_lib_src: {
                options: {
                    sourceMap: true,
                    sourceMapName: "public/js/jsSourceMapBaseLib.map",
                    sourceMapUrl: "jsSourceMapBaseLib.map",
                    mangle: false
                },
                src: [
                    "node_modules/jquery/dist/jquery.js",
                    "node_modules/jquery-migrate/dist/jquery-migrate.min.js",
                    "node_modules/jquery-ui-dist/jquery-ui.js",
                    "node_modules/jquery-ui-touch-punch/jquery.ui.touch-punch.js",
                    "node_modules/moment/moment.js",
                    "node_modules/js-cookie/dist/js.cookie.js",
                    "public/js/libs/bootstrap.min.js",
                    "public/js/libs/bootstrap-timepicker.min.js",
                    "public/js/libs/bootstrap-fileupload.min.js",
                    "public/js/libs/jquery.growl.js",
                    "public/js/libs/slimselect.min.js",
                    "public/js/libs/chosen.jquery.min.js",
                    "public/js/libs/jquery.form.js",
                    "public/js/libs/jquery.tagsinput.min.js",
                    "public/js/libs/confetti/js/confetti.js"

                ],
                dest: "public/js/compiled-base-libs.min.js"
            },
            extended_lib_src: {
                options: {
                    sourceMap: true,
                    sourceMapName: "public/js/jsSourceMapExtendedSrc.map"
                    , sourceMapUrl: "jsSourceMapExtendedSrc.map",
                    mangle: false
                },
                src: [
                    "node_modules/croppie/croppie.js",
                    "node_modules/chart.js/dist/chart.js",
                    "node_modules/chartjs-adapter-moment/dist/chartjs-adapter-moment.js",
                    "node_modules/packery/dist/packery.pkgd.js",
                    "node_modules/imagesloaded/imagesloaded.pkgd.js",
                    "node_modules/tether-shepherd/dist/js/tether.js",
                    "node_modules/tether-shepherd/dist/js/shepherd.js",
                    "node_modules/datatables.net/js/jquery.dataTables.js",
                    "node_modules/datatables.net-rowgroup/js/dataTables.rowGroup.js",
                    "node_modules/datatables.net-rowreorder/js/dataTables.rowReorder.js",
                    "node_modules/datatables.net-buttons/js/dataTables.buttons.js",
                    "node_modules/datatables.net-buttons/js/buttons.html5.js",
                    "node_modules/datatables.net-buttons/js/buttons.print.js",
                    "node_modules/datatables.net-buttons/js/buttons.colVis.js",
                    "node_modules/tinymce/tinymce.js",
                    "node_modules/tinymce/icons/default/icons.js",
                    "node_modules/tinymce/jquery.tinymce.js",
                    "node_modules/tinymce/themes/silver/theme.js",
                    "node_modules/tinymce/plugins/autolink/plugin.js",
                    "node_modules/tinymce/plugins/link/plugin.js",
                    "node_modules/tinymce/plugins/textcolor/plugin.js",
                    "node_modules/tinymce/plugins/image/plugin.js",
                    "node_modules/tinymce/plugins/lists/plugin.js",
                    "node_modules/tinymce/plugins/save/plugin.js",
                    "node_modules/tinymce/plugins/preview/plugin.js",
                    "node_modules/tinymce/plugins/media/plugin.js",
                    "node_modules/tinymce/plugins/searchreplace/plugin.js",
                    "node_modules/tinymce/plugins/paste/plugin.js",
                    "node_modules/tinymce/plugins/directionality/plugin.js",
                    "node_modules/tinymce/plugins/fullscreen/plugin.js",
                    "node_modules/tinymce/plugins/noneditable/plugin.js",
                    "node_modules/tinymce/plugins/visualchars/plugin.js",
                    "node_modules/tinymce/plugins/emoticons/plugin.js",
                    "node_modules/tinymce/plugins/emoticons/js/emojis.min.js",
                    "node_modules/tinymce/plugins/template/plugin.js",
                    "node_modules/tinymce/plugins/advlist/plugin.js",
                    "node_modules/tinymce/plugins/autoresize/plugin.js",
                    "node_modules/tinymce/plugins/codesample/plugin.js",


                    "node_modules/isotope-layout/dist/isotope.pkgd.js",
                    "node_modules/jstree/dist/jstree.js",
                    "node_modules/@assuradeurengilde/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js",

                    "public/js/libs/tinymce-plugins/helper.js",
                    "public/js/libs/tinymce-plugins/checklist/index.js",
                    "public/js/libs/tinymce-plugins/shortlink/index.js",
                    "public/js/libs/tinymce-plugins/table/plugin.js",
                    "public/js/libs/tinymce-plugins/bettertable/index.js",
                    "public/js/libs/tinymce-plugins/collapsibleheaders/index.js",
                    "public/js/libs/tinymce-plugins/embed/index.js",
                    "public/js/libs/tinymce-plugins/slashcommands/slashcommands.js",
                    "public/js/libs/tinymce-plugins/mention/plugin.js",

                    "public/js/libs/fullcalendar.min.js",
                    "public/js/libs/simple-color-picker-master/jquery.simple-color-picker.js",
                    "public/js/libs/simpleGantt/moment.min.js",
                    "public/js/libs/simpleGantt/snap.svg-min.js",
                    "public/js/libs/simpleGantt/frappe-gantt.min.js",
                    "public/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js",


                    "public/js/libs/uppy/uppy.js",


                ]
                , dest: "public/js/compiled-extended-libs.min.js"
            }
        }
        , jshint: {
            options: {
                curly: false
                , eqeqeq: false
                , eqnull: true
                , browser: true
                , laxcomma: true
                , globals: {
                    jQuery: true
                }
                , ignores: [
                ]
            }
            , app: [
                "public/js/app/**/*.js"
            ]
        }
        , less: {
            dev: {
                options: {
                    compress: true
                    , yuicompress: true
                    , optimization: 2
                    , autoPrefix: ">1%"
                    , cssComb: "none"
                    , ieCompat: true
                    , strictMath: false
                    , strictUnits: false
                    , relativeUrls: true
                    , rootPath: ""
                }
                , files: {
                    "public/css/main.css": "public/less/main.less"
                }
            }
        },
        exec: {
            composer_install: {
                cmd: 'composer self-update && composer install --no-dev',
                exitCode: [ 0, 255 ]
            }
        }
    });
    grunt.registerTask("Build-All", ["less:dev", "uglify", "jshint"]);

    grunt.registerTask("Build-App-Src", ["uglify:app_src", "jshint"]);
    grunt.registerTask("Build-Base-Lib", ["uglify:base_lib_src"]);
    grunt.registerTask("Build-Extended-Src", ["uglify:extended_lib_src"]);
    grunt.registerTask("Build-Less-Dev", ["less:dev"]);
};
