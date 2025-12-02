const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
    'helplinks-main': path.join(__dirname, 'src', 'main.js'),
    'helplinks-admin': path.join(__dirname, 'src', 'admin.js'),
}

module.exports = webpackConfig