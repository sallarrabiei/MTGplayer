import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import axios from 'axios';

// Configure axios
axios.defaults.baseURL = '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Add CSRF token to requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.getAttribute('content');
}

// Create Pinia store
const pinia = createPinia();

// Create Vue app
const app = createApp(App);

// Use plugins
app.use(pinia);
app.use(router);

// Global properties
app.config.globalProperties.$http = axios;

// Mount the app
app.mount('#app');