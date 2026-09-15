/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    // docs/09: Tailwind hanya utilitas layout; reset prototipe (app.css) yang menang.
    corePlugins: { preflight: false },
    theme: { extend: {} },
    plugins: [],
};
