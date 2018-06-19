module.exports = {
    srcDir: 'src/',
    css: ['@/assets/sass/tailwind.scss'],
    plugins: [
        '~plugins/filters.js',
        '~plugins/moment.js',
    ],
    env: {
        actionUrl: process.env.NODE_ENV === 'production' ? 'https://plugins.craftcms.com/index.php/actions' : 'https://plugins.craftcms.test/index.php/actions',
    },
    router: {
        middleware: 'route'
    },
    loading: {
        duration: 2000,
    },
    head: {
        meta: [
            {name: 'viewport', content: 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'}
        ]
    }
}