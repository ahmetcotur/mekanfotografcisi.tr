/**
 * Gooey Text Morphing - Vanilla JS Implementation
 * Ported from React component for Mekan Fotoğrafçısı
 */
class GooeyText {
    constructor(options) {
        this.container = options.container;
        this.texts = options.texts;
        this.morphTime = options.morphTime || 1;
        this.cooldownTime = options.cooldownTime || 0.25;
        this.textClassName = options.textClassName || '';

        this.textIndex = this.texts.length - 1;
        this.time = new Date();
        this.morph = 0;
        this.cooldown = this.cooldownTime;

        this.init();
    }

    init() {
        if (!this.container) return;

        // Create SVG Filter
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("class", "absolute h-0 w-0");
        svg.setAttribute("aria-hidden", "true");
        svg.setAttribute("focusable", "false");
        svg.innerHTML = `
            <defs>
                <filter id="gooey-threshold">
                    <feColorMatrix
                        in="SourceGraphic"
                        type="matrix"
                        values="1 0 0 0 0
                                0 1 0 0 0
                                0 0 1 0 0
                                0 0 0 255 -140"
                    />
                </filter>
            </defs>
        `;
        document.body.appendChild(svg);

        // Create Text Elements
        const wrapper = document.createElement('div');
        wrapper.className = "flex items-center justify-center relative w-full h-full";
        wrapper.style.filter = "url(#gooey-threshold)";

        this.text1 = document.createElement('span');
        this.text1.className = `absolute inset-0 flex items-center justify-center select-none text-center whitespace-nowrap ${this.textClassName}`;

        this.text2 = document.createElement('span');
        this.text2.className = `absolute inset-0 flex items-center justify-center select-none text-center whitespace-nowrap ${this.textClassName}`;

        wrapper.appendChild(this.text1);
        wrapper.appendChild(this.text2);
        this.container.appendChild(wrapper);

        this.animate();
    }

    setMorph(fraction) {
        this.text2.style.filter = `blur(${Math.min(8 / fraction - 8, 100)}px)`;
        this.text2.style.opacity = `${Math.pow(fraction, 0.4) * 100}%`;

        fraction = 1 - fraction;
        this.text1.style.filter = `blur(${Math.min(8 / fraction - 8, 100)}px)`;
        this.text1.style.opacity = `${Math.pow(fraction, 0.4) * 100}%`;
    }

    doCooldown() {
        this.morph = 0;
        this.text2.style.filter = "";
        this.text2.style.opacity = "100%";
        this.text1.style.filter = "";
        this.text1.style.opacity = "0%";
    }

    doMorph() {
        this.morph -= this.cooldown;
        this.cooldown = 0;
        let fraction = this.morph / this.morphTime;

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
                this.text1.textContent = this.texts[this.textIndex % this.texts.length];
                this.text2.textContent = this.texts[(this.textIndex + 1) % this.texts.length];
            }
            this.doMorph();
        } else {
            this.doCooldown();
        }
    }
}

// Global initialization helper
window.initGooeyText = (selector, texts, options = {}) => {
    const el = document.querySelector(selector);
    if (el) {
        new GooeyText({
            container: el,
            texts: texts,
            ...options
        });
    }
};
