import { createApp } from 'vue'
import AdminApp from './AdminApp.vue'
import { t, n } from '@nextcloud/l10n'

const adminApp = createApp(AdminApp)
adminApp.mixin({ methods: { t, n }})
adminApp.mount('#helplinks-admin-app')
