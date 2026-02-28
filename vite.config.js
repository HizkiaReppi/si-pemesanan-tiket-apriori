import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: ["resources/views/**", "routes/**", "app/Livewire/**"],
        }),
        tailwindcss(),
    ],

    server: {
        cors: true,
        hmr: { overlay: true },
        watch: {
            ignored: [
                "**/storage/framework/views/**",
                "**/vendor/**",
                "**/node_modules/**",
            ],
        },
    },

    build: {
        target: "es2022",
        cssMinify: "lightningcss",
        reportCompressedSize: false,
        chunkSizeWarningLimit: 600,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes("node_modules")) {
                        return "vendor";
                    }
                },
                assetFileNames: "assets/[name]-[hash][extname]",
                chunkFileNames: "assets/js/[name]-[hash].js",
                entryFileNames: "assets/js/[name]-[hash].js",
            },
        },
    },
});
