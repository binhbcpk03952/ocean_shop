import './bootstrap';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import 'bootstrap';
// import 'tinymce/skins/ui/oxide/skin.css'


import App from './App.vue';
import AuthProvider from './AuthProvider.vue';

const app = createApp({
  render: () => h(AuthProvider, null, { default: () => h(App) })
})
app.use(createPinia())
app.use(router)

app.mount('#app');
