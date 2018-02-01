let mix = require('laravel-mix');
let tailwindcss = require('tailwindcss');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

const sourcePath = 'web/craftidresources/src';
const distPath = 'web/craftidresources/dist';

if(mix.config.hmr) {
    mix.setResourceRoot("//localhost:8080/");
}

mix.setPublicPath(distPath);

mix.js(sourcePath + '/js/app.js', distPath + '/js/')
    .js(sourcePath + '/js/site.js', distPath + '/js/')
    .sass(sourcePath + '/sass/app.scss', distPath + '/css/')
    .sass(sourcePath + '/sass/site.scss', distPath + '/css/')
    .sass(sourcePath + '/sass/plugins.scss', distPath + '/css/')
    .options({
        processCssUrls: false,
        postCss: [ tailwindcss('./tailwind-config.js') ],
    })
    .copy(sourcePath + '/images', distPath + '/images/')
    .sourceMaps();

/*
mix.browserSync({
    host: 'id.craftcms.test',
    proxy: 'https://id.craftcms.test/',
    files: [
        distPath + '/css/!*.css',
        distPath + '/js/!*.js',
    ]
});*/
