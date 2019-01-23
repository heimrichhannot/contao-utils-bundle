var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('src/Resources/public/js/')
    .addEntry('contao-utils-bundle', '@hundh/contao-utils-bundle')
    .setPublicPath('/public/js/')
    .disableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();