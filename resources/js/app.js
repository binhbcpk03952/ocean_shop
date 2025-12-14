import './bootstrap'
import { createApp, h } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import 'bootstrap'

import { createHead } from '@vueuse/head'

import App from './App.vue'
import AuthProvider from './AuthProvider.vue'

const app = createApp({
  render: () => h(AuthProvider, null, {
    default: () => h(App)
  })
})

// ✅ ĐĂNG KÝ HEAD (BẮT BUỘC)
const head = createHead()
app.use(head)

app.use(createPinia())
app.use(router)

app.mount('#app')
