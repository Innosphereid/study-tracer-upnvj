import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue";

const input = [
    "resources/css/app.css", "resources/js/app.js",
    "resources/js/questionnaire/index.js",
];

export default defineConfig({
    plugins: [
        laravel({ input, refresh: true, }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            "@": "/resources/js",
        },
    },
    build: {
        rollupOptions: { input },
    },
});
