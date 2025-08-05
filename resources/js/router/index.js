import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import CardDetail from '../views/CardDetail.vue';
import Favorites from '../views/Favorites.vue';
import Statistics from '../views/Statistics.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
    meta: {
      title: 'Search Cards'
    }
  },
  {
    path: '/card/:id',
    name: 'card-detail',
    component: CardDetail,
    props: true,
    meta: {
      title: 'Card Details'
    }
  },
  {
    path: '/favorites',
    name: 'favorites',
    component: Favorites,
    meta: {
      title: 'Favorite Cards'
    }
  },
  {
    path: '/stats',
    name: 'stats',
    component: Statistics,
    meta: {
      title: 'Statistics'
    }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  }
});

// Update page title
router.beforeEach((to, from, next) => {
  document.title = to.meta.title ? `${to.meta.title} - MTG Player` : 'MTG Player';
  next();
});

export default router;