const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
    'main': path.join(__dirname, 'src', 'main.js'),
    'admin': path.join(__dirname, 'src', 'admin.js'),
}

module.exports = webpackConfig