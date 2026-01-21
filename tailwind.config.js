module.exports = {
    content: [
        './resources/js2/**/*.{vue,js}',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: '#1d5d5f',
                secondary: '#0f1d24',
                accent: '#e0b45a',
                ext: '#e3f1ef',
                ink: '#1e1f25',
                muted: '#666b78',
                surface: '#f7f3ef',
                panel: '#ffffff',
                border: '#e5e2dc',
                sidebar: '#0f1d24',
                'sidebar-accent': '#16313c',
                'sidebar-text': '#e8eef0',
                'surface-muted': '#f2efe9',
            },
            fontFamily: {
                sans: ['"IBM Plex Sans"', '"Segoe UI"', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
