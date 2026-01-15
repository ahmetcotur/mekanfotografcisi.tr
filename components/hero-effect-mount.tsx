import React from 'react';
import { createRoot } from 'react-dom/client';
import { HeroEffect } from './hero-effect';

const mount = () => {
    const container = document.getElementById('hero-effect-root');
    if (container) {
        // Prevent double mounting
        if (container.hasAttribute('data-mounted')) return;

        try {
            const root = createRoot(container);
            root.render(
                <React.StrictMode>
                    <HeroEffect />
                </React.StrictMode>
            );
            container.setAttribute('data-mounted', 'true');
            console.log('HeroEffect mounted successfully');
        } catch (e) {
            console.error('HeroEffect mount error:', e);
        }
    } else {
        // console.warn('HeroEffect root not found, retrying in 100ms');
        setTimeout(mount, 100);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mount);
} else {
    mount();
}
