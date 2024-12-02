const packageJson = require('./package.json');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');

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


// Source Maps Configuration
if (CONFIG.enableSourceMaps) {
    mix.sourceMaps();
}

// --- JavaScript Processing ---

// Core application JavaScript and extracting vendor
mix.js('./public/assets/js/app/app.js', `${CONFIG.publicPath}/js/app.js`)
    .extract(CONFIG.coreVendors, `${CONFIG.publicPath}/js/vendor.js`);

// Build global components
mix.js(
    glb.src('./public/assets/js/app/components/*'),
    glb.out({
        baseMap: './public/assets/js/app/components',
        outMap: './public/dist/js/components'
    })
);

mix.alias(
    glb.src('./public/assets/js/app/components/*'),
    glb.out({
        baseMap: './public/assets/js/app/components',
        outMap: './public/dist/js/components'
    })
);

// Build domain components
mix.js(
    glb.src('./app/Domain/**/Js/*'),
    glb.out({
        baseMap: './app',
        outMap: './public/dist/js'
        })
    );


// Dynamic imports configuration
const webpackConfig = {
    output: {
        filename: '[name].js',
        chunkFilename: 'js/chunks/[name].js',
        publicPath: '/dist/',
        //libraryTarget: 'umd',
        //umdNamedDefine: true, // optional
        clean: true,
        library: {
            type: 'umd',
        },
    },
    // experiments: {
    //     outputModule: true
    // },
    module: {
        rules: [
            {
                test: /skin.css$/i,
                use: [MiniCssExtractPlugin.loader, 'css-loader'],
            },
            {
                test: /content.css$/i,
                use: ['css-loader'],
            },
        ],
    },
    optimization: {
        usedExports: false,
        chunkIds: 'named',
        splitChunks: {
            chunks: 'async',
            cacheGroups: {
                tinymceVendor: {
                    test: /[\/]node_moduleslink:tinymce[\/]link:.*js|.*skin.css[\/]|[\/]plugins[\/]/,
                    name: 'tinymce',
                    priority: -20,
                    reuseExistingChunk: true,
                },
                default: {
                    priority: -40,
                    reuseExistingChunk: true,
                }
            },
        }
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
        new webpack.ProvidePlugin({
            jQuery: 'jquery',
            $: 'jquery',
            htmx: 'htmx.org'
        }),
        new MiniCssExtractPlugin(),
    ],
    resolve: {
        alias: {
            'images': path.resolve(__dirname, 'public/assets/images'),
            'js': path.resolve(__dirname, 'public/assets/js'),
            'javascript': path.resolve(__dirname, 'public/assets/js'),
            'css': path.resolve(__dirname, 'public/assets/css'),
            'fonts': path.resolve(__dirname, 'public/assets/fonts'),
            'domain': path.resolve(__dirname, 'app/Domain'),
            'dist': path.resolve(__dirname, 'public/dist/js'),
            '@domain': path.resolve(__dirname, 'public/dist/js/Domain'),
            '@components': path.resolve(__dirname, 'public/assets/js/app/components'),
        },
        extensions: ['.mjs', '.js'],
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
// --- Webpack Configuration ---
mix
.babelConfig({
    sourceType: 'unambiguous',
    presets: [
        ['@babel/preset-modules']
    ],
    plugins: [
        '@babel/plugin-proposal-unicode-property-regex'
    ]
})
.version();
