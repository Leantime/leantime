/** @type {import('tailwindcss').Config} */
module.exports = {
  prefix: "tw-",
  content: [
    "./app/**/*.blade.php",
    "./app/**/*.tpl.php",
    "./app/**/*.php",
    "./public/assets/js/**/*.js",
  ],
  corePlugins: {
    // Disable preflight (CSS reset) to avoid conflicts with Bootstrap
    preflight: false,
  },
  theme: {
    extend: {
      spacing: {
        xs: "5px",
        s: "8px",
        sm: "10px",
        m: "15px",
        base: "15px",
        l: "20px",
        xl: "30px",
        xxl: "40px",
      },
      fontSize: {
        xs: ["0.75rem", { lineHeight: "1rem" }],
        sm: "var(--font-size-s)",
        base: "var(--base-font-size)",
        l: "var(--font-size-l)",
      },
    },
  },
  plugins: [],
};
