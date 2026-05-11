module.exports = {
    content: [
        './assets/**/*.js',
        './templates/**/*.twig',
        './node_modules/flowbite/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                blue: {
                    50: 'var(--color-vsg-blue-50)',
                    100: 'var(--color-vsg-blue-100)',
                    200: 'var(--color-vsg-blue-200)',
                    300: 'var(--color-vsg-blue-300)',
                    400: 'var(--color-vsg-blue-400)',
                    500: 'var(--color-vsg-blue-500)',
                    600: 'var(--color-vsg-blue-600)',
                    700: 'var(--color-vsg-blue-700)',
                    800: 'var(--color-vsg-blue-800)',
                    900: 'var(--color-vsg-blue-900)',
                    950: 'var(--color-vsg-blue-950)',
                },
                'vsg-blue': {
                    50: 'var(--color-vsg-blue-50)',
                    100: 'var(--color-vsg-blue-100)',
                    200: 'var(--color-vsg-blue-200)',
                    300: 'var(--color-vsg-blue-300)',
                    400: 'var(--color-vsg-blue-400)',
                    500: 'var(--color-vsg-blue-500)',
                    600: 'var(--color-vsg-blue-600)',
                    700: 'var(--color-vsg-blue-700)',
                    800: 'var(--color-vsg-blue-800)',
                    900: 'var(--color-vsg-blue-900)',
                    950: 'var(--color-vsg-blue-950)',
                },
                'vsg-gold': {
                    50: 'var(--color-vsg-gold-50)',
                    100: 'var(--color-vsg-gold-100)',
                    200: 'var(--color-vsg-gold-200)',
                    300: 'var(--color-vsg-gold-300)',
                    400: 'var(--color-vsg-gold-400)',
                    500: 'var(--color-vsg-gold-500)',
                    600: 'var(--color-vsg-gold-600)',
                    700: 'var(--color-vsg-gold-700)',
                    800: 'var(--color-vsg-gold-800)',
                    900: 'var(--color-vsg-gold-900)',
                },
                vsg: {
                    badminton: {
                        primary: 'var(--color-vsg-badminton-primary)',
                        secondary: 'var(--color-vsg-badminton-secondary)',
                    },
                    gymnastik: {
                        primary: 'var(--color-vsg-gymnastik-primary)',
                        secondary: 'var(--color-vsg-gymnastik-secondary)',
                    },
                    tischtennis: {
                        primary: 'var(--color-vsg-tischtennis-primary)',
                        secondary: 'var(--color-vsg-tischtennis-secondary)',
                    },
                    volleyball: {
                        primary: 'var(--color-vsg-volleyball-primary)',
                        secondary: 'var(--color-vsg-volleyball-secondary)',
                    },
                },
            },
            fontFamily: {
                display: ['var(--font-display)'],
                body: ['var(--font-body)'],
            },
        },
    },
    plugins: [
        require('flowbite/plugin'),
    ],
};
