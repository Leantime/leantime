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
                    , sourceMapName: "public/js/jsSourceMapAPP.map"
                    , sourceMapUrl: "jsSourceMapAPP.map"
                    , mangle: false
                }
                , src: [
                    "public/js/app/app.js",
                    "public/js/app/core/custom.js",
                    "public/js/app/core/tableHandling.js",
                    "src/domain/**/*.js"
                ]
                , dest: "public/js/compiled-app.min.js"
            },
            lib_src: {
                options: {
                    sourceMap: true
                    , sourceMapName: "public/js/jsSourceMapLIBS.map"
                    , sourceMapUrl: "jsSourceMapLIBS.map"
                    , mangle: false
                }
                , src: [
                    "node_modules/jquery/dist/jquery.js",
                    "node_modules/jquery-migrate/dist/jquery-migrate.js",
                    "node_modules/jquery-ui-dist/jquery-ui.js",
                    "node_modules/jquery.uniform/dist/js/jquery.uniform.standalone.js",
                    "node_modules/croppie/croppie.js",
                    "node_modules/chart.js/dist/Chart.bundle.js",
                    "node_modules/masonry-layout/dist/masonry.pkgd.js",
                    "node_modules/imagesloaded/imagesloaded.pkgd.js",
                    "node_modules/tether-shepherd/dist/js/tether.js",
                    "node_modules/tether-shepherd/dist/js/shepherd.js",
                    "node_modules/datatables.net/js/jquery.dataTables.js",
                    "node_modules/chosen-js/chosen.jquery.js",
                    "node_modules/tinymce/tinymce.js",
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

                ]
                , dest: "public/js/compiled-libs.min.js"
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
                    , sourceMap: true
                    , autoPrefix: ">1%"
                    , cssComb: "none"
                    , ieCompat: true
                    , strictMath: false
                    , strictUnits: false
                    , relativeUrls: true
                    , rootPath: ""
                    , sourceMapRoot: "public/css/"
                    , sourceMapBasePath: "public/less/"
                }
                , files: {
                    "public/css/main.css": "public/less/main.less"
                }
            }
        }
        , watch: {
            scripts: {
                files: ["src/domain/**/*.js"]
                , tasks: ["uglify", "jshint"]
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
    grunt.registerTask("default", ["less:dev", "uglify", "jshint"]);
    grunt.registerTask("Dev-Watch", ["watch"]);
};