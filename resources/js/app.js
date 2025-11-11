import './bootstrap';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import 'bootstrap';

import App from './App.vue';
import AuthProvider from './AuthProvider.vue';
import { useAuthStore } from './stores/authGlobal';

const app = createApp({
  render: () => h(AuthProvider, null, { default: () => h(App) })
})
app.use(createPinia())
app.use(router)
const auth = useAuthStore()

// 🔄 Kiểm tra phiên đăng nhập ngay khi mở app
await auth.fetchUser()

app.mount('#app');
