module.exports = function (grunt) {
    grunt.loadNpmTasks("grunt-contrib-jshint");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks("grunt-contrib-uglify");
    grunt.loadNpmTasks("grunt-contrib-less");
    grunt.loadNpmTasks('grunt-exec');
    grunt.initConfig({
        uglify: {
            app_src: {
                options: {
                    sourceMap: true
                    , sourceMapName: "public/js/jsSourceMapAppSrc.map"
                    , sourceMapUrl: "jsSourceMapAppSrc.map",
                    mangle: false
                }
                , src: [
                    "public/js/app/app.js",
                    "public/js/app/core/custom.js",
                    "public/js/app/core/tableHandling.js",
                    "src/domain/**/*.js"
                ]
                , dest: "public/js/compiled-app.min.js"
            },
            base_lib_src: {
                options: {
                    sourceMap: true
                    , sourceMapName: "public/js/jsSourceMapBaseLib.map"
                    , sourceMapUrl: "jsSourceMapBaseLib.map",
                    mangle: false
                }
                , src: [
                    "node_modules/jquery/dist/jquery.js",
                    "node_modules/jquery-migrate/dist/jquery-migrate.js",
                    "node_modules/jquery-ui-dist/jquery-ui.js",
                    "node_modules/jquery.uniform/dist/js/jquery.uniform.standalone.js",
                    "public/js/libs/bootstrap.min.js",
                    "public/js/libs/bootstrap-timepicker.min.js",
                    "public/js/libs/bootstrap-fileupload.min.js",
                    "public/js/libs/jquery.jgrowl.js",
                    "public/js/libs/chosen.jquery.min.js",
                    "public/js/libs/jquery.form.js",
                    "public/js/libs/jquery.tagsinput.min.js"

                ]
                , dest: "public/js/compiled-base-libs.min.js"
            },
            extended_lib_src: {
                options: {
                    sourceMap: true
                    , sourceMapName: "public/js/jsSourceMapExtendedSrc.map"
                    , sourceMapUrl: "jsSourceMapExtendedSrc.map",
                    mangle: false
                }
                , src: [
                    "node_modules/croppie/croppie.js",
                    "node_modules/chart.js/dist/Chart.bundle.js",
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
                    "node_modules/tinymce/plugins/pagebreak/plugin.js",
                    "node_modules/tinymce/plugins/table/plugin.js",
                    "node_modules/tinymce/plugins/save/plugin.js",
                    "node_modules/tinymce/plugins/insertdatetime/plugin.js",
                    "node_modules/tinymce/plugins/preview/plugin.js",
                    "node_modules/tinymce/plugins/media/plugin.js",
                    "node_modules/tinymce/plugins/searchreplace/plugin.js",
                    "node_modules/tinymce/plugins/print/plugin.js",
                    "node_modules/tinymce/plugins/paste/plugin.js",
                    "node_modules/tinymce/plugins/directionality/plugin.js",
                    "node_modules/tinymce/plugins/fullscreen/plugin.js",
                    "node_modules/tinymce/plugins/noneditable/plugin.js",
                    "node_modules/tinymce/plugins/visualchars/plugin.js",
                    "node_modules/tinymce/plugins/nonbreaking/plugin.js",
                    "node_modules/tinymce/plugins/template/plugin.js",
                    "node_modules/tinymce/plugins/advlist/plugin.js",
                    "node_modules/isotope-layout/dist/isotope.pkgd.js",
                    "public/js/libs/fullcalendar.min.js",
                    "public/js/libs/simple-color-picker-master/jquery.simple-color-picker.js",
                    "public/js/libs/colorpicker.js",
                    "public/js/libs/simpleGantt/moment.min.js",
                    "public/js/libs/simpleGantt/snap.svg-min.js",
                    "public/js/libs/simpleGantt/frappe-gantt.min.js",
                    "public/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js"
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
        }
        , watch: {
            scripts: {
                files: ["src/domain/**/*.js"]
                , tasks: ["uglify:app_src", "jshint"]
                , options: {
                    nospawn: true
                }
            }
            , styles: {
                files: ["src/domain/**/*.less"]
                , tasks: ["less"]
                , options: {
                    nospan: true
                }
            },
            composer_json: {
                files: [ 'composer.json', 'composer.lock' ],
                tasks: [ 'exec:composer_install' ],
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
    grunt.registerTask("Dev-Watch", ["watch"]);
};