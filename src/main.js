import { createApp } from 'vue'
import App from './App.vue'
import { t, n } from '@nextcloud/l10n'

const app = createApp(App)
app.mixin({ methods: { t, n }})
app.mount('#content')
