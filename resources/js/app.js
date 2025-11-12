import './bootstrap';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import 'bootstrap';

import App from './App.vue';

const app = createApp(App)
app.use(createPinia())
app.use(router)

app.mount('#app');
