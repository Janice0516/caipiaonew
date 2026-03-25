<template>
  <div class="flex flex-col h-full w-full max-w-md mx-auto relative bg-dark-bg shadow-2xl overflow-hidden">
    <!-- 路由视图 -->
    <div class="flex-1 overflow-y-auto no-scrollbar relative z-0 pb-16">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </div>

    <!-- 底部导航栏 -->
    <nav class="absolute bottom-0 left-0 right-0 bg-dark-nav border-t border-dark-border z-50 w-full pb-safe backdrop-blur-md">
      <div class="flex justify-around items-center h-16 px-2">
        <router-link to="/" class="nav-item flex flex-col items-center gap-1 w-full text-gray-500 transition-all duration-300" active-class="active" exact-active-class="active">
          <i class="fas fa-home text-xl mb-0.5"></i>
          <span class="text-[10px]">首页</span>
        </router-link>
        <router-link to="/activity" class="nav-item flex flex-col items-center gap-1 w-full text-gray-500 transition-all duration-300" active-class="active">
          <i class="fas fa-gift text-xl mb-0.5"></i>
          <span class="text-[10px]">活动</span>
        </router-link>
        <router-link to="/lobby" class="nav-item flex flex-col items-center gap-1 w-full text-gray-500 transition-all duration-300 relative group" active-class="active">
          <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-brand-primary to-brand-secondary flex items-center justify-center -mt-8 shadow-glow-blue border-4 border-dark-bg group-active:scale-95 transition-transform">
            <i class="fas fa-gamepad text-white text-2xl"></i>
          </div>
          <span class="text-[10px] mt-1 font-medium">大厅</span>
        </router-link>
        <router-link to="/results" class="nav-item flex flex-col items-center gap-1 w-full text-gray-500 transition-all duration-300" active-class="active">
          <i class="fas fa-chart-line text-xl mb-0.5"></i>
          <span class="text-[10px]">开奖</span>
        </router-link>
        <router-link to="/profile" class="nav-item flex flex-col items-center gap-1 w-full text-gray-500 transition-all duration-300" active-class="active">
          <i class="fas fa-user text-xl mb-0.5"></i>
          <span class="text-[10px]">我的</span>
        </router-link>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/store/auth'

const authStore = useAuthStore()

onMounted(() => {
  // 每次进入大厅基础布局时，自动请求一次最新余额
  authStore.fetchBalance()
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* 底部导航激活态 */
.nav-item.active {
  color: #3B82F6;
}
.nav-item.active i {
  transform: translateY(-2px);
  text-shadow: 0 4px 10px rgba(59, 130, 246, 0.5);
}
</style>
