/**
 * Simple Text Switcher - Clean Fade Implementation
 * Replaces the complex Gooey Text to fix cropping and UI clutter
 */
class TextSwitcher {
    constructor(options) {
        this.container = options.container;
        this.texts = options.texts;
        this.morphTime = options.morphTime || 0.5;
        this.cooldownTime = options.cooldownTime || 2.0;
        this.textClassName = options.textClassName || '';
        this.textStyle = options.textStyle || '';

        this.textIndex = this.texts.length - 1;
        this.time = new Date();
        this.morph = 0;
        this.cooldown = this.cooldownTime;

        this.init();
    }

    init() {
        if (!this.container) return;

        // Container should not have any cropping filters
        this.container.style.filter = "none";
        this.container.classList.add('flex', 'items-center', 'justify-center', 'relative', 'overflow-visible');

        const wrapper = document.createElement('div');
        wrapper.className = "relative flex items-center justify-center w-full h-full overflow-visible";

        this.text1 = document.createElement('span');
        this.text2 = document.createElement('span');

        [this.text1, this.text2].forEach(span => {
            // Use absolute positioning to overlay text during fade
            span.className = `absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-max h-max flex items-center justify-center select-none text-center whitespace-nowrap transition-opacity duration-500 ${this.textClassName}`;
            if (this.textStyle) span.style.cssText += this.textStyle;
            wrapper.appendChild(span);
        });

        this.container.innerHTML = '';
        this.container.appendChild(wrapper);

        this.animate();
    }

    setMorph(fraction) {
        // Simple opacity fade
        this.text2.style.opacity = fraction;
        this.text1.style.opacity = 1 - fraction;

        // Ensure they are visible or hidden completely
        this.text1.style.visibility = (1 - fraction) < 0.01 ? "hidden" : "visible";
        this.text2.style.visibility = fraction < 0.01 ? "hidden" : "visible";
    }

    doCooldown() {
        this.morph = 0;
        this.text2.style.opacity = "1";
        this.text1.style.opacity = "0";
        this.text1.style.visibility = "hidden";
        this.text2.style.visibility = "visible";
    }

    doMorph() {
        this.morph -= this.cooldown;
        this.cooldown = 0;
        let fraction = (this.morph + this.cooldownTime) / this.morphTime;

        if (fraction > 1) {
            this.cooldown = this.cooldownTime;
            fraction = 1;
        }

        this.setMorph(fraction);
    }

    animate() {
        requestAnimationFrame(() => this.animate());

        const newTime = new Date();
        const shouldIncrementIndex = this.cooldown > 0;
        const dt = (newTime.getTime() - this.time.getTime()) / 1000;
        this.time = newTime;

        this.cooldown -= dt;

        if (this.cooldown <= 0) {
            if (shouldIncrementIndex) {
                this.textIndex = (this.textIndex + 1) % this.texts.length;
                // CRITICAL FIX: Use innerHTML to render span tags
                this.text1.innerHTML = this.texts[this.textIndex % this.texts.length];
                this.text2.innerHTML = this.texts[(this.textIndex + 1) % this.texts.length];
            }
            this.doMorph();
        } else {
            this.doCooldown();
        }
    }
}

// Global initialization helper (compatible with old name for easy transition)
window.initGooeyText = (selector, texts, options = {}) => {
    const el = document.querySelector(selector);
    if (el) {
        new TextSwitcher({
            container: el,
            texts: texts,
            ...options
        });
    }
};
