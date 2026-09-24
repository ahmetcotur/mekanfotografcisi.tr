/**
 * Tailwind build for the public site (templates/, panel/, musteri/).
 *
 * The site used to compile Tailwind in the browser via the Play CDN, which
 * left every page unstyled whenever that CDN was slow or blocked. The CSS is
 * now prebuilt into assets/css/app.css (committed, so deploys need no Node):
 *
 *   npm run build:css      # one-off build
 *   npm run watch:css      # rebuild on change while editing templates
 *
 * Page bodies stored in the database (service pages, legal pages, etc.) also
 * carry Tailwind classes, so the SQL seeds/migrations are scanned too.
 */
const brandShades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];

module.exports = {
    content: [
        './templates/**/*.php',
        './panel/**/*.php',
        './musteri/**/*.php',
        './includes/**/*.php',
        './login.php',
        './assets/js/*.js',
        './full_production_sync.sql',
        './database_schema.sql',
        './scripts/migrations/*.sql',
        './migrations/*.sql',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                display: ['Fraunces', 'ui-serif', 'Georgia', 'serif'],
                // Legacy alias used inside DB-stored page content.
                heading: ['Fraunces', 'ui-serif', 'Georgia', 'serif'],
            },
            colors: {
                // Accent colour comes from the admin "secondary_color" setting at
                // runtime (see brand_palette_css() in includes/helpers.php).
                brand: Object.fromEntries(
                    brandShades.map((s) => [s, `rgb(var(--brand-${s}) / <alpha-value>)`])
                ),
                paper: '#faf8f5',
                ink: {
                    DEFAULT: '#1c1917',
                    soft: '#44403c',
                    muted: '#78716c',
                },
                line: '#e7e5e4',
            },
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },
            maxWidth: {
                page: '80rem',
            },
            boxShadow: {
                soft: '0 1px 2px rgb(28 25 23 / 0.04), 0 8px 24px -12px rgb(28 25 23 / 0.12)',
                lift: '0 2px 4px rgb(28 25 23 / 0.04), 0 20px 40px -20px rgb(28 25 23 / 0.25)',
            },
            keyframes: {
                'fade-up': {
                    '0%': { opacity: '0', transform: 'translateY(8px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'fade-up': 'fade-up 0.4s ease-out both',
                // Legacy names still referenced by DB content.
                'slide-up': 'fade-up 0.4s ease-out both',
                'fade-in': 'fade-up 0.4s ease-out both',
            },
            typography: ({ theme }) => ({
                DEFAULT: {
                    css: {
                        '--tw-prose-body': theme('colors.ink.soft'),
                        '--tw-prose-headings': theme('colors.ink.DEFAULT'),
                        '--tw-prose-links': 'rgb(var(--brand-700))',
                        '--tw-prose-bold': theme('colors.ink.DEFAULT'),
                        '--tw-prose-bullets': 'rgb(var(--brand-500))',
                        '--tw-prose-quote-borders': 'rgb(var(--brand-300))',
                        'h1, h2, h3': { fontFamily: theme('fontFamily.display').join(','), fontWeight: '600' },
                        a: { textUnderlineOffset: '3px' },
                    },
                },
            }),
        },
    },
    plugins: [require('@tailwindcss/typography')],
};
