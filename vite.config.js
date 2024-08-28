import { defineConfig } from 'vite';
import { resolve } from 'path';
import { version } from './package.json';
import eslint from 'vite-plugin-eslint';
import tailwind from 'vite-plugin-tailwind';
import concat from 'rollup-plugin-concat';
import alias from '@rollup/plugin-alias';
import terser from '@rollup/plugin-terser';
import less from 'rollup-plugin-less';
import { sync } from 'glob';

export default defineConfig({
  base: './',
  publicDir: 'public/dist',
  build: {
    outDir: 'public/dist',
    lib: {
      entry: './public/assets/js/app/app-new.js',
      fileName: 'compiled-app-new',
      format: 'umd',
      name: 'leantime',
      exports: 'default',
    },
    rollupOptions: {
      external: ['leantime'], // <--- Add this line
      input: [
        './public/assets/js/app/htmx.js',
        './public/assets/js/app/htmx-headSupport.js',
      ],
      output: [
        {
          dir: 'public/dist/js', // <--- Update this line
          entryFileNames: '[name].js', // <--- Add this line
          format: 'esm',
        },
      ],
    },
  },
  resolve: {
    alias: {
      'images': resolve(__dirname, 'public/assets/images'),
      'js': resolve(__dirname, 'public/assets/js'),
      'css': resolve(__dirname, 'public/assets/css'),
      'fonts': resolve(__dirname, 'public/assets/fonts'),
    },
  },
  plugins: [
    eslint({
      fix: true,
      extensions: ['js'],
      exclude: [
        'node_modules',
        'public/assets/js/libs',
      ],
      overrideConfig: {
        parser: '@babel/eslint-parser',
      },
    }),
    tailwind(),
    concat({
      files: [
        './public/assets/js/libs/prism/prism.js',
      ],
      output: `compiled-footer.${version}.min.js`,
    }),
    concat({
      files: [
        './public/assets/js/app/htmx.js',
      ],
      output: `compiled-htmx.${version}.min.js`,
    }),
    concat({
      files: [
        './public/assets/js/app/htmx-headSupport.js',
      ],
      output: `compiled-htmx-headSupport.${version}.min.js`,
    }),
    concat({
      files: [
        './public/assets/js/app/app-new.js',
      ],
      output: `compiled-app-new.${version}.min.js`,
    }),
    concat({
      files: [
        './public/assets/js/app/app.js',
        './public/assets/js/app/core/editors.js',
        './public/assets/js/app/core/snippets.js',
        './public/assets/js/app/core/modals.js',
        './public/assets/js/app/core/tableHandling.js',
        './public/assets/js/app/core/datePickers.js',
        './public/assets/js/app/core/dateHelper.js',
        ...sync('./app/Domain/**/*.js').map(f => `./${f}`),
      ],
      output: `compiled-app.${version}.min.js`,
    }),
    concat({
      files: [
        './node_modules/jquery/dist/jquery.js',
        './public/assets/js/libs/bootstrap.min.js',
      ],
      output: `compiled-frameworks.${version}.min.js`,
    }),
    concat({
      files: [
        './node_modules/jquery-ui-dist/jquery-ui.js',
        './node_modules/jquery-ui-touch-punch/jquery.ui.touch-punch.js',
        './node_modules/chosen-js/chosen.jquery.js',
        './public/assets/js/libs/jquery.growl.js',
        './public/assets/js/libs/jquery.form.js',
        './public/assets/js/libs/jquery.tagsinput.min.js',
        './public/assets/js/libs/bootstrap-fileupload.min.js',
      ],
      output: `compiled-framework-plugins.${version}.min.js`,
    }),
    concat({
      files: [
        './node_modules/luxon/build/global/luxon.js',
        './node_modules/moment/moment.js',
        './public/assets/js/libs/jquery.form.js',
        './node_modules/@popperjs/core/dist/umd/popper.js',
        './node_modules/tippy.js/dist/tippy-bundle.umd.js',
        './public/assets/js/libs/slimselect.min.js',
        './node_modules/canvas-confetti/dist/confetti.browser.js',
        './public/assets/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js',
        './public/assets/js/libs/uppy/uppy.js',
        './node_modules/croppie/croppie.js',
        './node_modules/packery/dist/packery.pkgd.js',
        './node_modules/imagesloaded/imagesloaded.pkgd.js',
        './node_modules/shepherd.js/dist/js/shepherd.js',
        './node_modules/isotope-layout/dist/isotope.pkgd.js',
        './node_modules/gridstack/dist/gridstack-all.js',
        './node_modules/jstree/dist/jstree.js',
        './node_modules/@assuradeurengilde/fontawesome-iconpicker/dist/js/fontawesome-iconpicker.js',
        './node_modules/leader-line/leader-line.min.js',
        './public/assets/js/libs/simple-color-picker-master/jquery.simple-color-picker.js',
        "./public/assets/js/libs/emojipicker/vanillaEmojiPicker.js",
      ],
      output: `public/dist/js/compiled-global-component.${version}.min.js`
    }),
    concat({
      files: [
        './public/assets/js/libs/emojipicker/vanillaEmojiPicker.js',
      ],
      output: `compiled-global-component.${version}.min.js`,
    }),
    concat({
      files: [
        './node_modules/ical.js/build/ical.min.js',
        './node_modules/fullcalendar/index.global.min.js',
        './node_modules/@fullcalendar/icalendar/index.global.min.js',
        './node_modules/@fullcalendar/google-calendar/index.global.min.js',
        './node_modules/@fullcalendar/luxon3/index.global.min.js',
      ],
      output: `compiled-calendar-component.${version}.min.js`,
    }),
    concat({
      files: [
        './node_modules/datatables.net/js/jquery.dataTables.js',
        './node_modules/datatables.net-rowgroup/js/dataTables.rowGroup.js',
        './node_modules/datatables.net-rowreorder/js/dataTables.rowReorder.js',
        './node_modules/datatables.net-buttons/js/dataTables.buttons.js',
        './node_modules/datatables.net-buttons/js/buttons.html5.js',
        './node_modules/datatables.net-buttons/js/buttons.print.js',
        './node_modules/datatables.net-buttons/js/buttons.colVis.js',
      ],
      output: `compiled-table-component.${version}.min.js`,
    }),
    concat({
      files: [
        './node_modules/tinymce/tinymce.js',
        './node_modules/tinymce/icons/default/icons.js',
        './node_modules/tinymce/jquery.tinymce.js',
      ],
      output: `compiled-editor-component.${version}.min.js`,
    }),
    alias({
      entries: [
        { find: 'images', replacement: resolve(__dirname, 'public/assets/images') },
        { find: 'js', replacement: resolve(__dirname, 'public/assets/js') },
        { find: 'css', replacement: resolve(__dirname, 'public/assets/css') },
        { find: 'fonts', replacement: resolve(__dirname, 'public/assets/fonts') },
      ],
    }),
    terser({
      output: {
        comments: false,
      },
    }),
    less({
      output: 'compiled-styles.css',
    }),
  ],
});
