import Vue from 'vue'
import App from './App.vue'
import { generateFilePath } from '@nextcloud/router'

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath('helplinks', '', 'js/')

Vue.mixin({ methods: { t, n } })

export default new Vue({
    el: '#app-helplinks',
    render: h => h(App),
})