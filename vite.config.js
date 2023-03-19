import {defineConfig, loadEnv} from 'vite';
import laravel from 'laravel-vite-plugin';

export default ({mode}) => {
    process.env = Object.assign(process.env, loadEnv(mode, process.cwd(), ''));

    return defineConfig({
        plugins: [
            laravel({
                input: [
                    'resources/sass/app.scss',
                    'resources/js/app.js',
                    'resources/css/app.css',
                ],
                refresh: true,
            }),
        ],
        server: {
            host: process.env.HOST,
        },
    });
}

