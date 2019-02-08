var Encore = require('@symfony/webpack-encore');

const path = require('path');

Encore
    .setOutputPath('src/Resources/public/js/')
    .addEntry('contao-utils-bundle', '@hundh/contao-utils-bundle')
    .setPublicPath('/public/js/')
    .disableSingleRuntimeChunk()
    .configureBabel(() => {}, {
        exclude: (filePath) => {
            // Don't exclude files outside of node_modules
            if (!/node_modules/.test(filePath)) {
                return false;
            }

            // Don't exclude whitelisted modules
            const whitelistedModules = ['@hundh' + path.sep + 'contao-utils-bundle'].map(
                module => path.join('node_modules', module) + path.sep
            );

            for (const modulePath of whitelistedModules) {
                if (filePath.includes(modulePath)) {
                    return false;
                }
            }

            // Exclude other files
            return true;
        }
    })
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();