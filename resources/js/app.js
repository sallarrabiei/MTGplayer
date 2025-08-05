import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import { createPinia } from 'pinia';
import App from './App.vue';

// Import pages
import HomePage from './pages/HomePage.vue';
import CardDetailPage from './pages/CardDetailPage.vue';
import FavoritesPage from './pages/FavoritesPage.vue';

// Create router
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            name: 'home',
            component: HomePage,
        },
        {
            path: '/cards/:id',
            name: 'card-detail',
            component: CardDetailPage,
        },
        {
            path: '/favorites',
            name: 'favorites',
            component: FavoritesPage,
            meta: { requiresAuth: true }
        },
    ],
});

// Create Vue app
const app = createApp(App);

// Use plugins
app.use(router);
app.use(createPinia());

// Mount app
app.mount('#app');
