require('dotenv').config()

module.exports = {
    modules: [
        '@nuxtjs/dotenv',
    ],
    server: {
        port: process.env.NUXT_DEV_PORT,
        host: process.env.NUXT_DEV_HOST,
    },
    srcDir: 'src/',
    css: [
        '@/assets/sass/app.scss',
        'swiper/dist/css/swiper.css',
    ],
    plugins: [
        '~plugins/fontAwesome.js',
        '~plugins/filters.js',
        '~plugins/moment.js',
        '~plugins/shave.js',
        '~plugins/eventBus.js',
        { src: '~/plugins/swiper.js', ssr: false },
    ],
    env: {
        actionUrl: process.env.NODE_ENV === 'production' ? 'https://plugins.craftcms.com/index.php/actions' : 'https://plugins.craftcms.test/index.php/actions',
        showSeoMeta: false,
        craftIdUrl: process.env.NODE_ENV === 'production' ? 'https://id.craftcms.com' : 'https://id.craftcms.test',
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
    },
}