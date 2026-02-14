import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { viteStaticCopy } from 'vite-plugin-static-copy';
import commonjs from '@rollup/plugin-commonjs';
import path from 'path';

// Virtual module: maps `import $ from 'jquery'` to the global window.jQuery
// loaded by the classic <script> tag in header.blade.php. This avoids bare
// module specifiers that browsers cannot resolve.
function jqueryGlobalPlugin() {
    return {
        name: 'jquery-global',
        enforce: 'pre',
        resolveId(id) {
            if (id === 'jquery') return '\0jquery-global';
        },
        load(id) {
            if (id === '\0jquery-global') {
                return 'var jQ = window.jQuery || window.$; export default jQ; export { jQ as $ };';
            }
        },
    };
}

export default defineConfig({
    plugins: [
        jqueryGlobalPlugin(),
        tailwindcss(),

        laravel({
            input: [
                // CSS entry points
                'resources/css/main.css',
                'resources/css/app.css',
                'resources/css/editor.css',

                // JS entry points
                'resources/js/compiled-htmx.js',
                'resources/js/compiled-htmx-extensions.js',
                'resources/js/compiled-frameworks.js',
                'resources/js/compiled-global-component.js',
                'resources/js/compiled-calendar-component.js',
                'resources/js/compiled-table-component.js',
                'resources/js/compiled-editor-component.js',
                'resources/js/compiled-gantt-component.js',
                'resources/js/compiled-chart-component.js',
                'resources/js/compiled-app.js',
                'resources/js/compiled-footer.js',
                'resources/js/compiled-lottieplayer.js',
            ],
            refresh: true,
        }),

        viteStaticCopy({
            targets: [
                {
                    src: 'public/assets/images/*',
                    dest: 'images',
                },
                {
                    src: 'public/assets/fonts/*',
                    dest: 'fonts',
                },
                {
                    src: 'public/assets/lottie/*',
                    dest: 'lottie',
                },
                {
                    src: 'public/assets/css/libs/tinymceSkin/oxide',
                    dest: 'css/libs/tinymceSkin',
                },
            ],
        }),
    ],

    resolve: {
        alias: {
            // Standard aliases (used in JS)
            'images': path.resolve(__dirname, 'public/assets/images'),
            'js': path.resolve(__dirname, 'public/assets/js'),
            'css': path.resolve(__dirname, 'public/assets/css'),
            'fonts': path.resolve(__dirname, 'public/assets/fonts'),

            // Webpack ~ prefix aliases (used in CSS @font-face, url() refs)
            '~fonts': path.resolve(__dirname, 'public/assets/fonts'),
            '~images': path.resolve(__dirname, 'public/assets/images'),
            '~css': path.resolve(__dirname, 'public/assets/css'),
        },
    },

    build: {
        // Output to public/build (Vite default with laravel plugin)
        // Keep public/dist intact for rollback
        rollupOptions: {
            plugins: [
                commonjs(),
            ],
            output: {
                // Preserve the same bundle names for clarity
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]',

                // Prevent Vite from code-splitting these bundles
                // Each entry point should be a self-contained bundle
                manualChunks: undefined,
            },
        },
        // Increase chunk size warning limit (TinyMCE alone is ~3.6MB)
        chunkSizeWarningLimit: 5000,
    },
});
