var Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('website', './assets/apps/website/index.js')

    .splitEntryChunks()

    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3,
    })

    .enableSassLoader()
    .enableTypeScriptLoader()

    .enableIntegrityHashes(Encore.isProduction())

    .autoProvidejQuery()
;

Encore.configureWatchOptions(watchOptions => {
    watchOptions.poll = 250;
});

module.exports = Encore.getWebpackConfig();
