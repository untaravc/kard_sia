module.exports = {
    content: [
        './resources/js2/**/*.{vue,js}',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#0e153f',
                secondary: '#0b1233',
                accent: '#f2b544',
                ext: '#e8ecf7',
                ink: '#0f172a',
                muted: '#6b7280',
                surface: '#f7f8fc',
                panel: '#ffffff',
                border: '#e5e7eb',
                sidebar: '#0e153f',
                'sidebar-accent': '#18245a',
                'sidebar-text': '#e7ebff',
                'surface-muted': '#f1f3f9',
            },
            fontFamily: {
                sans: ['"IBM Plex Sans"', '"Segoe UI"', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
