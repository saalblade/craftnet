const fs = require('fs');
const ManifestPlugin = require('webpack-manifest-plugin')

module.exports = {
    filenameHashing: false,
    devServer: {
        headers: { "Access-Control-Allow-Origin": "*" },
        https: {
            key: process.env.DEV_SSL_KEY ? fs.readFileSync(process.env.DEV_SSL_KEY) : null,
            cert: process.env.DEV_SSL_CERT ? fs.readFileSync(process.env.DEV_SSL_CERT) : null,
        },
        port: process.env.DEV_SERVER_PORT,
    },
    baseUrl: process.env.NODE_ENV === 'development' ? process.env.DEV_BASE_URL : process.env.PROD_BASE_URL,
    configureWebpack: {
        plugins: [
            new ManifestPlugin({
                publicPath: '/'
            }),
        ],
        resolve: {
            alias: {
                'vue$': 'vue/dist/vue.esm.js'
            }
        }
    },
    chainWebpack: config => {
        // Remove the standard entry point
        config.entryPoints.delete('app')

        // Add entry points
        config.entry('app')
            .add('./src/js/app.js')
            .end()
            .entry('site')
            .add('./src/js/site.js')
            .end()
    },
}
