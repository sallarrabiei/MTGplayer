import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';

// Import components
import CardSearch from './components/CardSearch.vue';
import CardDetail from './components/CardDetail.vue';

// Create router
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'search',
            component: CardSearch
        },
        {
            path: '/card/:id',
            name: 'card-detail',
            component: CardDetail,
            props: true
        }
    ]
});

// Create Pinia store
const pinia = createPinia();

// Create and mount Vue app
const app = createApp(App);
app.use(router);
app.use(pinia);
app.mount('#app');
