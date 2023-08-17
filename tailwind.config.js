/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./app/{views,domain}/**/*.{tpl,sub,inc,blade}.php'],
  prefix: 'tw-', // temporary prefix until bootstrap is removed
  theme: {
    extend: {},
  },
  plugins: [],
}
