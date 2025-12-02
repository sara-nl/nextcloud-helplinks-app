import Vue from 'vue'
import AdminApp from './AdminApp.vue'
import { generateFilePath } from '@nextcloud/router'

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath('helplinks', '', 'js/')

Vue.mixin({ methods: { t, n } })

export default new Vue({
    el: '#helplinks-admin-app',
    render: h => h(AdminApp),
})