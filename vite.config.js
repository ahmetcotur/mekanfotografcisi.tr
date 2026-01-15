import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [react({
        jsxRuntime: 'automatic',
    })],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './'),
        },
    },
    build: {
        lib: {
            entry: path.resolve(__dirname, 'components/hero-effect-mount.tsx'),
            name: 'HeroEffect',
            fileName: 'hero-effect',
            formats: ['iife'],
        },
        rollupOptions: {
            output: {
                globals: {
                    react: 'React',
                    'react-dom': 'ReactDOM'
                }
            }
        },
        outDir: 'assets/js/react',
        emptyOutDir: true,
        minify: true,
    },
    define: {
        'process.env.NODE_ENV': '"production"',
    }
});

