const packageJson = require('./package.json');
const webpack = require('webpack');
const path = require('path');
const { glb } = require('laravel-mix-glob');
const mix = require('laravel-mix');
const fs = require('fs');

// --- Plugin Loading ---
require('laravel-mix-eslint');
require('mix-tailwindcss');
require('dotenv').config({ path: 'config/.env' });

// --- Core Configuration ---
const CONFIG = {
    version: packageJson.version,
    publicPath: 'public/dist',
    resourceRoot: '../',
    enableSourceMaps: false,
    coreVendors: [
        'jquery',
        'htmx.org',
        'tippy.js',
        'canvas-confetti',
    ],
    chunkSize: 244000,
    domainPaths: {
        base: './app/Domain',
        output: 'js/domain'
    }
};

// --- Mix Initialization ---
mix.setResourceRoot(CONFIG.resourceRoot)
   .setPublicPath(CONFIG.publicPath)
   .options({ runtimeChunkPath: 'js' })
   .version();


// Source Maps Configuration
if (CONFIG.enableSourceMaps) {
    mix.sourceMaps();
}

// --- JavaScript Processing ---

// Core application JavaScript and extracting vendor
mix.js('./public/assets/js/app/app.js', `${CONFIG.publicPath}/js/app.js`)
    .extract(CONFIG.coreVendors, `${CONFIG.publicPath}/js/vendor.js`);

// Build domain components
mix.js(
    glb.src('./app/Domain/**/Js/*.js'),
    glb.out({
        baseMap: './app',
        outMap: './public/dist/js'
        })
    );

// Process components and support modules
mix.js('./public/assets/js/app/support/*.mjs', `${CONFIG.publicPath}/js/support`);
mix.js('./public/assets/js/app/components/**/*.mjs', `${CONFIG.publicPath}/js/components`);

// Dynamic imports configuration
const webpackConfig = {
    output: {
        filename: '[name].js',
        chunkFilename: 'js/chunks/[name].js',
        publicPath: '/dist/',
        clean: true,
        libraryTarget: 'umd',
        umdNamedDefine: false, // optional

    },
    optimization: {
        usedExports: false,
    },
    // optimization: {
    //     runtimeChunk: 'single',
    //     splitChunks: {
    //         chunks: 'async',
    //         minSize: 20000,
    //         maxSize: CONFIG.chunkSize,
    //         cacheGroups: {
    //             domain: {
    //                 test: /[\\/]Domain[\\/].*[\\/]Js[\\/]/,
    //                 priority: -10
    //             }
    //         }
    //     }
    // },
    externals: {
        i18n: 'window.leantime.i18n',
    },
    plugins: [
        new webpack.DefinePlugin({
            i18n: 'window.leantime.i18n',
        }),
        new webpack.DefinePlugin({
            'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV)
        }),
        new webpack.ProvidePlugin({
            jQuery: 'jquery',
            $: 'jquery',
            htmx: 'htmx.org'
        }),
    ],
    resolve: {
        alias: {
            'images': path.resolve(__dirname, 'public/assets/images'),
            'js': path.resolve(__dirname, 'public/assets/js'),
            'javascript': path.resolve(__dirname, 'public/assets/js'),
            'css': path.resolve(__dirname, 'public/assets/css'),
            'fonts': path.resolve(__dirname, 'public/assets/fonts'),
            'domain': path.resolve(__dirname, 'app/Domain'),
            '@domain': path.resolve(__dirname, 'public/dist/js/Domain'),
        },
        extensions: [".*",".wasm",".mjs",".js",".jsx",".json",".*"],
    },
    stats: {
        children: false
    }
};

mix.webpackConfig(webpackConfig);

// --- CSS Processing ---
// Process and minify CSS files
mix.postCss('./public/assets/less/editor.css', `${CONFIG.publicPath}/css/editor.${CONFIG.version}.min.css`)
   .postCss('./public/assets/less/app.css', `${CONFIG.publicPath}/css/app.${CONFIG.version}.min.css`)
   .postCss('./public/assets/less/main.css', `${CONFIG.publicPath}/css/main.${CONFIG.version}.min.css`)
   .tailwind();

// Asset Copying
mix.copy('./public/assets/images', `${CONFIG.publicPath}/images`)
   .copy('./public/assets/fonts', `${CONFIG.publicPath}/fonts`)
   .copy('./public/assets/lottie', `${CONFIG.publicPath}/lottie`)
   .copy('./public/assets/css/libs/tinymceSkin/oxide', `${CONFIG.publicPath}/css/libs/tinymceSkin/oxide`);

// Footer and Editor Components
mix.combine('./public/assets/js/libs/prism/prism.js', `${CONFIG.publicPath}/js/compiled-footer.js`)
    .js([
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
    ], `${CONFIG.publicPath}/js/components/editor-component.js`);

// --- Webpack Configuration ---
mix
.babelConfig({
    sourceType: 'unambiguous',
    plugins: ['@babel/plugin-syntax-dynamic-import'],
})
.version();
