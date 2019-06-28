var Encore = require('@symfony/webpack-encore');

Encore
.setOutputPath('src/Resources/public/js/')
.addEntry('contao-utils-bundle', '@hundh/contao-utils-bundle')
.setPublicPath('/public/js/')
.disableSingleRuntimeChunk()
.configureBabel(function (babelConfig) {
}, {
    // include to babel processing
    includeNodeModules: ['@hundh/contao-utils-bundle']
})
.enableSourceMaps(!Encore.isProduction());

module.exports = Encore.getWebpackConfig();