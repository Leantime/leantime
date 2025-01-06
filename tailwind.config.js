// default config:
import defaultConfig from 'tailwindcss/defaultConfig'

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './app/{Views,Domain,Plugins}/**/*.{tpl,sub,inc,blade}.php',
        './app/Core/UI/Template.php',
        './storage/framwork/views/*.php',
        './app/{Views,Domain,Plugins}/**/{Composers,Controllers}/**/*.php',
        './public/assets/less/{base,components,utilities}/*.css',
    ],
    safelist: [
        'bg-success',
        'bg-info',
        'bg-warning',
        'bg-error',
        'bg-critical',
        'bg-trivial',
        'border-success',
        'border-info',
        'border-warning',
        'border-error',
        'border-critical',
        'border-trivial',
        'text-success-content',
        'text-info-content',
        'text-warning-content',
        'text-error-content',
        'text-critical-content',
        'text-trivial-content',
        'text-success',
        'text-info',
        'text-warning',
        'text-error',
        'text-critical',
        'text-trivial',
        'badge-sm',
        'badge-md',
        'badge-lg',
    ],
    theme:  {
        fontFamily: {
            sans: ['"Hanken Grotesk"', 'sans'],
            serif: ['Merriweather', 'serif'],
            hanken: ['"Hanken Grotesk"', 'sans-serif'],
            atkinson: ['"Atkinson Hyperlegible"', 'sans-serif'],
            shantell: ['"Shantell Sans"', 'sans-serif'],
            materialIcons: ['"Material Symbols Rounded"', 'sans-serif'],
        },
        fontWeight: {
            thin: '100',
            hairline: '100',
            extralight: '200',
            light: '300',
            normal: '400',
            medium: '500',
            semibold: '600',
            bold: '700',
            extrabold: '800',
            'extra-bold': '800',
            black: '900',
        },
        fontSize: {
            xs: ['0.875rem', { lineHeight: '1rem' }],
            sm: ['0.938rem', { lineHeight: '1.5rem' }],
            base: ['1rem', { lineHeight: '1.75rem' }],
            lg: ['1.125rem', { lineHeight: '1.75rem' }],
            xl: ['1.25rem', { lineHeight: '2rem' }],
            '2xl': ['1.5rem', { lineHeight: '2rem' }],
            '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
            '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
            '5xl': ['3rem', { lineHeight: '1' }],
            '6xl': ['3.75rem', { lineHeight: '1' }],
            '7xl': ['4.5rem', { lineHeight: '1' }],
            '8xl': ['6rem', { lineHeight: '1' }],
            '9xl': ['8rem', { lineHeight: '1' }],
        },
        borderRadius: {
            none: '0px',
            sm: '0.25rem',
            'default': '0.5rem',
            md: '0.5rem',
            lg: '1rem',
            xl: '1.5rem',
            '2xl': '2rem',
            '3xl': '3rem',
            full: '9999px',
            round: '9999px',
            pill: '3.5rem',
        },
        colors: {
            transparent: 'transparent',
            current: 'currentColor',
            "purple": {
                "10": "#e6ccec",
                "20": "#cd99d8",
                "30": "#b366c5",
                "40": "#9a33b1",
                "50": "#81009e",
                "60": "#6a0381",
                "70": "#540665",
                "80": "#3d0948",
                "90": "#260c2c",
                "95": "#1b0e1e",
                "05": "#f2e5f5"
            },
            "pink": {
                "10": "#efd0e5",
                "20": "#dfa1cb",
                "30": "#ce72b1",
                "40": "#be4397",
                "50": "#ae147d",
                "60": "#8e1367",
                "70": "#6f1251",
                "80": "#4f113b",
                "90": "#2f1025",
                "95": "#1f101a",
                "05": "#f7e7f2"
            },
            "cobalt": {
                "10": "#cedbed",
                "20": "#9db7da",
                "30": "#6c92c8",
                "40": "#3b6eb5",
                "50": "#0a4aa3",
                "60": "#0b3e85",
                "70": "#0c3368",
                "80": "#0d274a",
                "90": "#0e1b2d",
                "95": "#0f151e",
                "05": "#e6edf6"
            },
            "blue": {
                "10": "#cce3ee",
                "20": "#99c7dc",
                "30": "#66abcb",
                "40": "#338fb9",
                "50": "#0073a8",
                "60": "#035f89",
                "70": "#064b6b",
                "80": "#09374c",
                "90": "#0c232e",
                "95": "#0e191f",
                "05": "#e5f1f6"
            },
            "teal": {
                "10": "#cceee7",
                "20": "#99dccf",
                "30": "#66cbb7",
                "40": "#33b99f",
                "50": "#00a887",
                "60": "#03896f",
                "70": "#066b57",
                "80": "#094c3f",
                "90": "#0c2e27",
                "95": "#0e1f1b",
                "05": "#e5f6f3"
            },
            "orange": {
                "10": "#f0d9cd",
                "20": "#e1b39b",
                "30": "#d18e69",
                "40": "#c26837",
                "50": "#b34205",
                "60": "#923807",
                "70": "#722e09",
                "80": "#51240b",
                "90": "#30190d",
                "95": "#18120f",
                "05": "#f7ece6"
            },
            "red": {
                "10": "#eecece",
                "20": "#dd9d9d",
                "30": "#cc6b6b",
                "40": "#bb3a3a",
                "50": "#aa0909",
                "60": "#8b0a0a",
                "70": "#6c0c0c",
                "80": "#4d0d0d",
                "90": "#2e0e0e",
                "95": "#1f0f0f",
                "05": "#f6e6e6"
            },
            "yellow": {
                "10": "#fceccf",
                "20": "#fada9e",
                "30": "#f7c76e",
                "40": "#f5b53d",
                "50": "#f2a20d",
                "60": "#c5850d",
                "70": "#97670e",
                "80": "#6a4a0e",
                "90": "#3d2d0f",
                "95": "#261e0f",
                "05": "#fef6e7"
            },
            "green": {
                "10": "#cfe8d7",
                "20": "#9fd2af",
                "30": "#70bb88",
                "40": "#3fa45f",
                "50": "#108e38",
                "60": "#107530",
                "70": "#105b28",
                "80": "#104220",
                "90": "#0f2917",
                "95": "#0f1c13",
                "05": "#e7f4eb"
            },
            "white": "#ffffff",
            "black": "#080808",
            "transparent": "#ffffff00",
            "shade": {
                "10": "#0f0f0f1a",
                "20": "#0f0f0f33",
                "30": "#0f0f0f4d",
                "40": "#0f0f0f66",
                "50": "#0f0f0f80",
                "60": "#0f0f0f99",
                "70": "#0f0f0fb2",
                "80": "#0f0f0fcc",
                "90": "#0f0f0fe5",
                "95": "#0f0f0ff2",
                "05": "#0f0f0f0d"
            },
            "tint": {
                "10": "#ffffff1a",
                "20": "#ffffff33",
                "30": "#ffffff4d",
                "40": "#ffffff66",
                "50": "#ffffff80",
                "60": "#ffffff99",
                "70": "#ffffffb2",
                "80": "#ffffffcc",
                "90": "#ffffffe5",
                "95": "#fffffff2",
                "05": "#ffffff0d"
            },
            "neutral": {
                "10": "#e7e7e7",
                "20": "#cfcfcf",
                "30": "#b7b7b7",
                "40": "#9f9f9f",
                "50": "#878787",
                "60": "#6f6f6f",
                "70": "#575757",
                "80": "#3f3f3f",
                "90": "#272727",
                "95": "#1b1b1b",
                "05": "#f3f3f3"
            },
            "pagetitle-content" : "#fff",
            'trivial' :  '#777777',
            'trivial-content' :  '#ffffff',
        },
        extend: {
            spacing: {
                'xs': '0.25rem',
                'sm': '0.5rem',
                'md': '1rem',
                'lg': '1.5rem',
                'xl': '2rem',
                'xxl': '3rem',
            }
        },

    },

    daisyui: {
        base: true,
        themes: [{
            leantime: {
                'fontFamily': 'Hanken Grotesk',

                'primary' : '#006c9e',           /* Primary color */
                //'primary-focus' : '#035f89',     /* Primary color - focused */
                'primary-content' : '#ffffff',   /* Foreground content color to use on primary color */

                'secondary' : '#ffffff',           /* Secondary color */
                //'secondary-content' : 'var(--palette-teal-05)',   /* Foreground content color to use on secondary color */

                'accent' : '#00a887',            /* Accent color */
                //'accent-content' : '#f7e7f2',    /* Foreground content color to use on accent color */

                'neutral' :  '#f3f3f3',           /* Neutral color */
                //'neutral-content' :  '#3f3f3f',   /* Foreground content color to use on neutral color */

                'base-100' : '#ffffff',          /* Base color of page, used for blank backgrounds */
                 //'base-200' : '#e7e7e7',          /* Base color, a little darker */
                 //'base-300' : '#dedede',          /* Base color, even more darker */
                'base-content' : '#272727',      /* Foreground content color to use on base color */

                'info' : '#0a4aa3',              /* Info */
                'info-content' : '#ffffff',              /* Info */

                'success' :  '#108e38',           /* Success */
                'success-content' :  '#ffffff',           /* Success */

                'warning' :  '#f2a20d',           /* Warning */
                'warning-content' :  '#ffffff',           /* Warning */

                'critical' :  '#aa0909',             /* Error */
                'critical-content' :  '#ffffff',             /* Error */

                'error' :  '#aa0909',             /* Error */
                'error-content' :  '#ffffff',             /* Error */



                'text-sm': '0.938rem',

                "--rounded-box": '1rem', // biggest elements, large content cards
                "--rounded-btn": '3.0rem', // button
                "--rounded-badge": '3.0rem', // border radius rounded-badge utility class, used in badges and similar
                "--animation-btn": "0.25s", // duration of animation when you click on button
                "--animation-input": "0.2s", // duration of animation for inputs like checkbox, toggle, radio, etc
                "--btn-focus-scale": "0.95", // scale transform of button when you focus on it
                "--border-btn": "1px", // border width of buttons
            },
        }],
    },
    plugins: [
        require("@tailwindcss/typography"),
        require('daisyui'),
    ],
}
