import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../store/auth'
import MainLayout from '../layout/MainLayout.vue'
import Login from '../views/auth/Login.vue'
import Register from '../views/auth/Register.vue'
import Home from '../views/home/Home.vue'
import Lobby from '../views/lobby/Lobby.vue'
import Activity from '../views/activity/Activity.vue'
import Results from '../views/results/Results.vue'
import Profile from '../views/profile/Profile.vue'
import GamePlay from '../views/game/GamePlay.vue'
import Recharge from '../views/profile/subpages/Recharge.vue'
import Withdraw from '../views/profile/subpages/Withdraw.vue'
import Orders from '../views/profile/subpages/Orders.vue'
import Transactions from '../views/profile/subpages/Transactions.vue'
import Security from '../views/profile/subpages/Security.vue'
import Agent from '../views/profile/subpages/Agent.vue'
import Notice from '../views/notice/Notice.vue'

const routes = [
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Home',
        component: Home
      },
      {
        path: 'lobby',
        name: 'Lobby',
        component: Lobby
      },
      {
        path: 'activity',
        name: 'Activity',
        component: Activity
      },
      {
        path: 'results',
        name: 'Results',
        component: Results
      },
      {
        path: 'profile',
        name: 'Profile',
        component: Profile
      }
    ]
  },
  {
    path: '/game/:id',
    name: 'GamePlay',
    component: GamePlay,
    meta: { requiresAuth: true }
  },
  {
    path: '/recharge',
    name: 'Recharge',
    component: Recharge,
    meta: { requiresAuth: true }
  },
  {
    path: '/withdraw',
    name: 'Withdraw',
    component: Withdraw,
    meta: { requiresAuth: true }
  },
  {
    path: '/orders',
    name: 'Orders',
    component: Orders,
    meta: { requiresAuth: true }
  },
  {
    path: '/transactions',
    name: 'Transactions',
    component: Transactions,
    meta: { requiresAuth: true }
  },
  {
    path: '/security',
    name: 'Security',
    component: Security,
    meta: { requiresAuth: true }
  },
  {
    path: '/agent',
    name: 'Agent',
    component: Agent,
    meta: { requiresAuth: true }
  },
  {
    path: '/notice',
    name: 'Notice',
    component: Notice,
    meta: { requiresAuth: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    next({ path: '/login', query: to.query })
  } else {
    next()
  }
})

export default router
