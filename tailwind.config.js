// tailwind.config.js
const plugin = require('tailwindcss/plugin');

module.exports = {
    darkMode: 'media',
    important: '.force-tailwind',
    content: [
        './src/**/*.{js,ts,jsx,tsx}',
        './**/*.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [
        plugin(function ({addUtilities}) {
            const newUtilities = {
                '.text-shadow': {
                    'text-shadow': '1px 1px 2px rgba(0, 0, 0, 0.25)',
                },
                '.text-shadow-md': {
                    'text-shadow': '2px 2px 4px rgba(0, 0, 0, 0.3)',
                },
                '.text-shadow-lg': {
                    'text-shadow': '3px 3px 6px rgba(0, 0, 0, 0.4)',
                },
                '.text-shadow-none': {
                    'text-shadow': 'none',
                },
            };

            addUtilities(newUtilities, ['responsive', 'hover']);
        }),
        require('tailwind-scrollbar'),
    ],
};
