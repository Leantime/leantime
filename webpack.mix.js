const pjson = require('./package.json');
const glob = require('glob');
const path = require('path');
const version = pjson.version;
const webpack = require('webpack');

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
mix
    // this is what to prefix the URL with
    .combine('./public/assets/js/libs/prism/prism.js', `public/dist/js/compiled-footer.${version}.min.js`)
    .js('./public/assets/js/app/app-new.js', `public/dist/js/compiled-app.${version}.js`)
    .extract([
        'jquery',
        'jquery-ui',
        'htmx.org',
        'tippy.js',
        'moment',
        'luxon',
        'canvas-confetti'
    ])
    .minify(`./public/dist/js/compiled-app.${version}.js`)
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
    .postCss('./public/assets/less/editor.css', `public/dist/css/editor.${version}.min.css`)
    .postCss('./public/assets/less/app.css', `public/dist/css/app.${version}.min.css`)
    .postCss('./public/assets/less/main.css', `public/dist/css/main.${version}.min.css`)


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
    .tailwind()
    .webpackConfig(() => {
        return {
            // entry: {
            //     selects: './public/assets/js/app/core/selects.module.mjs',
            //     datePickers: './public/assets/js/app/core/datePickers.module.mjs',
            // },
            // output: {
            //     filename: '[name].js',
            //     chunkFilename: '[name].chunk.js',
            //     path: path.resolve('public/dist'),
            //     clean: true,
            // },
            resolve: {
                alias: {
                    'images': path.resolve(__dirname, 'public/assets/images'),
                    'js': path.resolve(__dirname, 'public/assets/js'),
                    'css': path.resolve(__dirname, 'public/assets/css'),
                    'fonts': path.resolve(__dirname, 'public/assets/fonts'),
                    'domain': path.resolve(__dirname, 'app/Domain'),
                },
                extensions: [".*",".wasm",".mjs",".js",".jsx",".json",".*"]
            },
            externals: {
                i18n: 'window.leantime.i18n',
            },
            plugins: [
                new webpack.DefinePlugin({
                    i18n: 'window.leantime.i18n',
                }),
                new webpack.ProvidePlugin({
                    jQuery: 'jquery',
                }),
            ],
            module: {
                rules: [
                    {
                        test: path.resolve(__dirname, 'node_modules/leader-line/'),
                        use: [{
                            loader: 'skeleton-loader',
                            options: { procedure: content => `${content}export default LeaderLine;` }
                        }]
                    },
                    // {
                    //     test: /\.mjs$/,
                    //     exclude: /node_modules/,
                    //     use: {
                    //         loader: 'babel-loader',
                    //         options: {
                    //             presets: ['@babel/preset-env']
                    //         }
                    //     }
                    // },
                ],
            },
            optimization: {
                //  runtimeChunk: 'single',
                // splitChunks: {
                //     cacheGroups: {
                //         vendor: {
                //             test: /[\\/]node_modules[\\/]/,
                //             name: 'vendor',
                //             chunks: 'all'
                //         },
                //     }
                // },
             }
        //     entry: {
        //         index: {
        //             import: path.resolve('public/assets/js/app', 'app-new.js'),
        //         },
        //         selects: {
        //             import: path.resolve('public/assets/js/app/core', 'selects.module.mjs'),
        //             dependOn: 'index',
        //         },
        //         datePickers: {
        //             import: path.resolve('public/assets/js/app/core', 'datePickers.module.mjs'),
        //             dependOn: 'index',
        //         },
        //     },
        //     output: {
        //         filename: '[name].bundle.js',
        //         chunkFilename: '[name].chunk.js',
        //         path: path.resolve('public/dist/js'),
        //         clean: true,
        //     },
        //     optimization: {
        //         runtimeChunk: 'single',
        //         splitChunks: {
        //             chunks: 'all',
        //         },
        //         // splitChunks: {
        //         //     cacheGroups: {
        //         //         vendor: {
        //         //             test: /[\\/]node_modules[\\/]/,
        //         //             name: 'vendor',
        //         //             chunks: 'all'
        //         //         },
        //         //     }
        //         // },
        //     },
        //
         }
    })
    .babelConfig({
        sourceType: 'unambiguous',
        presets: ['@babel/preset-env']
    });

