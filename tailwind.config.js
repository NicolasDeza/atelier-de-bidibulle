import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Roboto", ...defaultTheme.fontFamily.sans],
                serif: ["Playfair Display", ...defaultTheme.fontFamily.serif],
            },
            colors: {
                bidibordeaux: {
                    DEFAULT: "#800020",
                    hover: "#a0002a",
                    focus: "#660018",
                },
                beige: "#F8F4F0",
            },
        },
    },
    plugins: [forms, typography],
};
