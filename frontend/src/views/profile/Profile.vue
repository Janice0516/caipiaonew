<template>
  <div class="flex-1 overflow-y-auto no-scrollbar pb-20">
    <!-- 个人信息卡片 -->
    <div class="p-4 pt-8 bg-gradient-to-b from-gray-900 to-dark-bg border-b border-dark-border">
      <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 rounded-full border-2 border-brand-accent p-0.5 relative">
          <img :src="authStore.userInfo.avatar" class="w-full h-full rounded-full bg-gray-800 object-cover">
          <div class="absolute bottom-0 right-0 w-4 h-4 bg-brand-success border-2 border-dark-bg rounded-full"></div>
        </div>
        <div>
          <h2 class="text-xl font-bold text-white flex items-center gap-2">
            {{ authStore.userInfo.username || 'VIP_Player' }}
            <span class="px-1.5 py-0.5 rounded bg-brand-accent/20 text-brand-accent text-[10px] border border-brand-accent/30">SVIP 3</span>
          </h2>
          <p class="text-xs text-gray-500 mt-1">加入时间: 2023-09-12</p>
        </div>
        <button @click="$router.push('/security')" class="ml-auto text-gray-400 hover:text-white"><i class="fas fa-cog text-lg"></i></button>
      </div>

      <!-- 资产卡片 -->
      <div class="w-full rounded-2xl bg-gradient-to-br from-brand-primary/20 to-purple-900/20 border border-white/10 p-5 relative overflow-hidden backdrop-blur-md">
        <div class="relative z-10">
          <p class="text-xs text-gray-400 mb-1 flex items-center gap-2">
            总资产 (CNY)
            <i class="fas fa-sync-alt cursor-pointer hover:text-white transition-colors" @click="authStore.fetchBalance" title="刷新余额"></i>
          </p>
          <h3 class="text-3xl font-mono font-bold text-white mb-4">¥ {{ authStore.userInfo.balance.toFixed(2) }}</h3>
          <div class="flex gap-3">
            <button @click="$router.push('/recharge')" class="flex-1 py-2 rounded-lg bg-brand-primary text-white text-sm font-medium shadow-lg shadow-blue-500/20 active:scale-95 transition-transform">
              充值
            </button>
            <button @click="$router.push('/withdraw')" class="flex-1 py-2 rounded-lg bg-white/10 text-white text-sm font-medium hover:bg-white/20 border border-white/10 active:scale-95 transition-transform">
              提现
            </button>
          </div>
        </div>
      </div>
      <!-- 代理中心入口 -->
      <div @click="$router.push('/agent')" class="mt-4 w-full rounded-xl bg-gradient-to-r from-blue-900 to-indigo-900 border border-brand-primary/30 p-4 flex items-center justify-between cursor-pointer active:scale-95 transition-transform relative overflow-hidden group">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20"></div>
        <div class="flex items-center gap-3 relative z-10">
          <div class="w-10 h-10 rounded-full bg-brand-primary/20 flex items-center justify-center text-brand-primary border border-brand-primary/30">
            <i class="fas fa-users-cog"></i>
          </div>
          <div>
            <div class="text-sm font-bold text-white">代理中心</div>
            <div class="text-[10px] text-blue-200">查看团队数据与收益</div>
          </div>
        </div>
        <i class="fas fa-chevron-right text-gray-400 relative z-10"></i>
      </div>
    </div>

    <!-- 功能菜单 -->
    <div class="p-4 space-y-4">
      <div class="bg-dark-card rounded-xl border border-dark-border overflow-hidden">
        <div v-for="(item, i) in menuItems" :key="i" @click="handleItemClick(item)" class="flex items-center justify-between p-4 border-b border-dark-border last:border-0 hover:bg-white/5 cursor-pointer transition-colors">
          <div class="flex items-center gap-3">
            <div :class="['w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm', item.bg]">
              <i :class="item.icon"></i>
            </div>
            <span class="text-sm text-gray-200">{{ item.label }}</span>
          </div>
          <i class="fas fa-chevron-right text-gray-600 text-xs"></i>
        </div>
      </div>

      <button @click="logout" class="w-full py-3 rounded-xl border border-dark-border text-gray-400 text-sm hover:text-white hover:border-gray-600 transition-colors">
        退出登录
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../store/auth'

const router = useRouter()
const authStore = useAuthStore()

const menuItems = ref([
  { label: '注单记录', icon: 'fas fa-list-alt', bg: 'bg-blue-500', path: '/orders' },
  { label: '资金明细', icon: 'fas fa-wallet', bg: 'bg-purple-500', path: '/transactions' },
  { label: '安全中心', icon: 'fas fa-shield-alt', bg: 'bg-green-500', path: '/security' },
  { label: '在线客服', icon: 'fas fa-headset', bg: 'bg-orange-500', path: '/kefu/chatlink.html' },
])

const handleItemClick = (item: any) => {
  if (item.path.includes('.html')) {
    window.location.href = item.path;
  } else {
    router.push(item.path);
  }
}

const logout = () => {
  if (confirm('确定要退出登录吗？')) {
    authStore.logoutAction()
    router.replace('/login')
  }
}
</script>
