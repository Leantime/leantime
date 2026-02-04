const packageJson = require('./package.json');
const mix = require('laravel-mix');
const path = require('path');

require('dotenv').config({ path: 'config/.env' });

const version = packageJson.version;
const postCssPlugins = [
    require('postcss-import'),
    require('@tailwindcss/nesting'),
    require('postcss-url')({ url: 'rebase' }),
    require('tailwindcss')('./tailwind.components.config.js'),
    require('autoprefixer'),
];

mix
    .setPublicPath('public/dist')
    .setResourceRoot('../')
    .postCss('./public/assets/less/app.css', `public/dist/css/app-components.${version}.min.css`, postCssPlugins)
    .postCss('./public/assets/less/editor.css', `public/dist/css/editor-components.${version}.min.css`, postCssPlugins)
    .postCss('./public/assets/less/main.css', `public/dist/css/main-components.${version}.min.css`, postCssPlugins)
    .copy('./public/assets/fonts', 'public/dist/fonts')
    .copy('./public/assets/images', 'public/dist/images')
    .webpackConfig({
        resolve: {
            alias: {
                'images': path.resolve(__dirname, 'public/assets/images'),
                'js': path.resolve(__dirname, 'public/assets/js'),
                'css': path.resolve(__dirname, 'public/assets/css'),
                'fonts': path.resolve(__dirname, 'public/assets/fonts'),
            },
        },
    });
