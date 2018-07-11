module.exports = {
    srcDir: 'src/',
    css: [
        '@/assets/sass/app.scss',
        'swiper/dist/css/swiper.css',
    ],
    plugins: [
        '~plugins/filters.js',
        '~plugins/moment.js',
        { src: '~/plugins/swiper.js', ssr: false },
        '~plugins/shave.js',
    ],
    env: {
        actionUrl: process.env.NODE_ENV === 'production' ? 'https://plugins.craftcms.com/index.php/actions' : 'https://plugins.craftcms.test/index.php/actions',
        showSeoMeta: false,
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