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
    .sass('assets/src/scss/rcp-file-protector.scss', 'assets/dist/css')
   .js('assets/src/js/app.js', 'assets/dist/js');