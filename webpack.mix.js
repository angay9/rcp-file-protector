const mix = require('laravel-mix');

mix.webpackConfig(webpack => {
    return {
        plugins: [
            new webpack.ProvidePlugin({
                $: 'jquery',
                jquery: 'jquery',
                'window.jQuery': 'jquery'
            })
        ]
    };
});

mix
    .sass('resources/src/scss/rcp-file-protector.scss', 'resources/dist/css')
   .js('resources/src/js/app.js', 'resources/dist/js');