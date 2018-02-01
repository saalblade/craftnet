let mix = require('laravel-mix');
let tailwindcss = require('tailwindcss');
const sourcePath = 'web/craftidresources/src';
const distPath = 'web/craftidresources/dist';

mix.setResourceRoot("/craftidresources/dist/");

if(mix.config.hmr) {
    mix.setResourceRoot("//localhost:8080/");
}

mix.setPublicPath(distPath);

mix.js(sourcePath + '/js/app.js', 'js')
    .js(sourcePath + '/js/site.js', 'js')
    .sass(sourcePath + '/sass/app.scss', 'css')
    .sass(sourcePath + '/sass/site.scss', 'css')
    .sass(sourcePath + '/sass/plugins.scss', 'css')
    .options({
        processCssUrls: false,
        postCss: [ tailwindcss('./tailwind-config.js') ],
    })
    .copy(sourcePath + '/images', distPath + '/images/')
    .sourceMaps();
