let mix = require('laravel-mix');
let tailwindcss = require('tailwindcss');
const sourcePath = './src';
const distPath = './dist';

// Set a prefix for all generated asset paths.
mix.setResourceRoot("/pluginsresources/dist/");

// Override the default path to your project's public directory.
mix.setPublicPath(distPath);

mix.js(sourcePath + '/js/main.js', 'js')
    .sass(sourcePath + '/sass/main.scss', 'css')
    .options({
        processCssUrls: false,
        postCss: [ tailwindcss('./tailwind-config.js') ],
    })
    // .copy(sourcePath + '/images', distPath + '/images/')
    .sourceMaps();


// https://sebastiandedeyne.com/posts/2017/typescript-with-laravel-mix/

mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                loader: 'ts-loader',
                options: { appendTsSuffixTo: [/\.vue$/] },
                exclude: /node_modules/,
            },
        ],
    },
    resolve: {
        extensions: ['*', '.js', '.jsx', '.vue', '.ts', '.tsx'],
        alias: {
            'vue$': 'vue/dist/vue.esm.js'
        }
    },
});

// Run versioning on production only.
//if (mix.inProduction()) {
//    mix.version();
//}
