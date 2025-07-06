import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import vuePlugin from "@vitejs/plugin-vue";
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173
        },
        watch: {
            usePolling: true,
            include: ['**/*.php', '**/*.twig']
        }
    },
    plugins: [
        vuePlugin(),
        symfonyPlugin({
            stimulus: true,
            refresh: ['templates/**/*.twig']
        }),
        tailwindcss(),
        {
            name: 'twig-php-reload',
            handleHotUpdate ({ file, server }) {
                if (file.endsWith('.twig')) {
                    console.log('Reloading due to change in:', file);
                    server.ws.send({
                        type: 'full-reload',
                        path: '*'
                    });
                    return [];
                }
            }
        }
    ],
    build: {
        outDir: 'public/build',
        manifest: true,
        rollupOptions: {
            input: {
                app: "./assets/app.js",
                theme: "./assets/app.css",
                leaflet: "./assets/js/leaflet.js"
            },
        }
    },
});