/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './app/{Views,Domain}/**/*.{tpl,sub,inc,blade}.php',
        './app/Core/Template.php',
        './app/{Views,Domain}/**/{Composers,Controllers}/**/*.php',
    ],
    prefix: 'tw-', // temporary prefix until bootstrap is removed
    theme: {
        extend: {
            colors: {
                'primary': {
                    default: 'var(--primary-color)',
                },
                'secondary': {
                    default: 'var(--secondary-color)',
                },
            },
            fontSize: {
                'sm': 'var(--font-size-sm)',
                'base': 'var(--base-font-size)',
                'l': 'var(--font-size-l)',
                'xl': 'var(--font-size-xl)',
                '2xl': 'var(--font-size-xxl)',
                '3xl': 'var(--font-size-xxxl)',
                '4xl': 'calc(var(--font-size-xxxl) * 1.2)',
                '5xl': 'calc(var(--font-size-xxxl) * 1.6)',
                '6xl': 'calc(var(--font-size-xxxl) * 2)',
                '7xl': 'calc(var(--font-size-xxxl) * 2.4)',
                '8xl': 'calc(var(--font-size-xxxl) * 3.2)',
                '9xl': 'calc(var(--font-size-xxxl) * 4.266)',
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
                'xs': '5px',
                'sm': '10px',
                'base': '15px',
                'm': '15px',
                'l': '20px',
                'xl': '30px',
            }
        },
    },
    plugins: [],
}
