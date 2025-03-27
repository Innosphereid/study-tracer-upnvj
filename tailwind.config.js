const defaultTheme = require("tailwindcss/defaultTheme");

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: "#eef2ff",
                    100: "#e0e7ff",
                    200: "#c7d2fe",
                    300: "#a5b4fc",
                    400: "#818cf8",
                    500: "#6366f1",
                    600: "#4f46e5",
                    700: "#4338ca",
                    800: "#3730a3",
                    900: "#312e81",
                    950: "#1e1b4b",
                },
            },
            animation: {
                "fade-in": "fadeIn 0.3s ease-in-out",
                "slide-in": "slideIn 0.3s ease-in-out",
            },
            keyframes: {
                fadeIn: {
                    "0%": { opacity: "0" },
                    "100%": { opacity: "1" },
                },
                slideIn: {
                    "0%": { transform: "translateY(20px)", opacity: "0" },
                    "100%": { transform: "translateY(0)", opacity: "1" },
                },
            },
        },
    },
    plugins: [require("@tailwindcss/forms")],
    safelist: [
        // Pastikan class backdrop-blur tersedia
        "backdrop-blur-sm",
        // Pastikan classes transform tersedia
        "transform",
        "translate-y-0",
        "translate-y-4",
        "-translate-x-1/2",
        "-top-14",
        // Pastikan class transisi tersedia
        "transition-all",
        "duration-300",
        "ease-in-out",
        // Pastikan class opacity tersedia
        "opacity-0",
        "opacity-100",
    ],
};
