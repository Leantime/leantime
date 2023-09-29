/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ['./app/{Views,Domain}/**/*.{tpl,sub,inc,blade}.php'],
    prefix: 'tw-', // temporary prefix until bootstrap is removed
    theme: {
        extend: {},
        colors: {
            'primary': 'var(--primary-color)',
        },
        fontSize: {
            'sm': 'var(--font-size-sm)',
            'base': 'var(--base-font-size)',
            'l': 'var(--font-size-l)',
            'xl': 'var(--font-size-xl)',
            '2xl': 'var(--font-size-xxl)',
            '3xl': 'var(--font-size-xxxl)',
            '4xl': 'var(--font-size-xxxl)',
            '5xl': 'var(--font-size-xxxl)',
            'superLarge': '75px',
        },
        padding: {
            'none': '0',
            'xs': '5px',
            'sm': '10px',
            'base': '15px',
            'm': '15px',
            'l': '20px',
            'xl': '30px',
        },
        margin: {
            'none': '0',
            'sm': '5px',
            'base': '15px',
            'm': '15px',
            'l': '20px',
            'xl': '30px',
        }
    },
    plugins: [],
}
