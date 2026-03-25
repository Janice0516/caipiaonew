<template>
  <div class="flex-1 overflow-y-auto no-scrollbar pb-24 bg-dark-bg">
    <!-- 顶部 header -->
    <header class="px-4 py-4 flex items-center justify-between sticky top-0 z-20 bg-dark-bg/95 backdrop-blur border-b border-dark-border">
      <span class="font-bold text-white text-lg">精彩活动</span>
      <button v-if="loading" class="text-gray-400 animate-spin text-sm">
        <i class="fas fa-spinner"></i>
      </button>
      <button v-else class="text-gray-400" @click="loadActivities">
        <i class="fas fa-sync-alt"></i>
      </button>
    </header>

    <!-- 加载骨架屏 -->
    <div v-if="loading" class="p-4 space-y-4">
      <div class="bg-dark-card rounded-xl border border-dark-border p-4 animate-pulse">
        <div class="h-4 bg-dark-border rounded w-1/3 mb-4"></div>
        <div class="space-y-3">
          <div class="h-12 bg-dark-border rounded"></div>
          <div class="h-12 bg-dark-border rounded"></div>
        </div>
      </div>
      <div class="h-32 bg-dark-card rounded-xl border border-dark-border animate-pulse"></div>
    </div>

    <div v-else class="p-4 space-y-6">

      <!-- 每日任务区块 -->
      <div v-if="tasks.length" class="bg-dark-card rounded-xl border border-dark-border p-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-bold text-white flex items-center gap-2">
            <i class="fas fa-calendar-check text-brand-success"></i> 每日任务
          </h3>
          <span class="text-xs text-gray-500">已完成 {{ doneCount }}/{{ totalCount }}</span>
        </div>

        <div class="space-y-4">
          <div
            v-for="task in tasks"
            :key="task.id"
            class="flex flex-col gap-2"
          >
            <!-- 任务行 -->
            <div class="flex items-center gap-3">
              <!-- 图标 -->
              <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-sm flex-shrink-0', getTaskIconClass(task)]">
                <i :class="getTaskIcon(task)"></i>
              </div>
              <!-- 信息 -->
              <div class="flex-1 min-w-0 cursor-pointer" @click="openDetailModal(task)">
                <div class="text-sm text-white font-medium">{{ task.title }}</div>
                <div class="text-xs text-gray-500">
                  奖励 <span class="text-brand-accent font-bold">{{ task.reward.toFixed(2) }}</span> 元金
                </div>
              </div>
              <!-- 按钮 -->
              <button
                v-if="task.claimed"
                class="px-3 py-1 rounded-full bg-dark-surface border border-dark-border text-gray-500 text-xs cursor-not-allowed flex-shrink-0"
                disabled
              >已领取</button>
              <button
                v-else-if="task.completed"
                class="px-3 py-1 rounded-full bg-brand-success text-white text-xs flex-shrink-0 active:scale-95 transition-transform"
                :class="{'opacity-60 cursor-not-allowed': claimingId === task.id}"
                :disabled="claimingId === task.id"
                @click="handleClaim(task)"
              >
                <i v-if="claimingId === task.id" class="fas fa-spinner animate-spin mr-1"></i>
                领取
              </button>
              <button
                v-else
                class="px-3 py-1 rounded-full bg-dark-surface border border-dark-border text-gray-400 text-xs flex-shrink-0"
              >去完成</button>
            </div>

            <!-- 进度条（充值/投注任务才显示） -->
            <div v-if="task.type !== 1 && !task.claimed" class="ml-13 pr-4">
              <div>
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                  <span>{{ getProgressLabel(task) }}</span>
                  <span>{{ task.progress.toFixed(0) }}%</span>
                </div>
                <div class="w-full bg-dark-border rounded-full h-1.5">
                  <div
                    class="h-1.5 rounded-full transition-all duration-700"
                    :class="task.completed ? 'bg-brand-success' : 'bg-brand-primary'"
                    :style="{ width: task.progress + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 无任务提示 -->
      <div v-else-if="!loading && !error" class="bg-dark-card rounded-xl border border-dark-border p-8 text-center text-gray-500">
        <i class="fas fa-calendar-times text-3xl mb-2"></i>
        <div class="text-sm">暂无任务活动</div>
      </div>

      <!-- 热门活动列表 -->
      <div v-if="banners.length" class="space-y-4">
        <h3 class="font-bold text-white flex items-center gap-2 px-1">
          <i class="fas fa-fire text-orange-500"></i> 热门活动
        </h3>
        <div
          v-for="banner in banners"
          :key="banner.id"
          class="rounded-xl overflow-hidden relative group cursor-pointer shadow-lg"
          @click="openDetailModal(banner)"
        >
          <div class="h-32 bg-gray-800 relative">
            <div :class="['absolute inset-0 bg-gradient-to-r opacity-80', banner.gradient]"></div>
            <div class="absolute inset-0 p-4 flex flex-col justify-center">
              <span class="px-2 py-0.5 bg-white/20 text-white text-[10px] rounded w-fit mb-2 backdrop-blur-sm">
                {{ banner.tag }}
              </span>
              <h4 class="text-xl font-bold text-white mb-1">{{ banner.title }}</h4>
              <p class="text-xs text-white/80">{{ banner.desc }}</p>
            </div>
            <div class="absolute right-4 bottom-4 w-12 h-12 rounded-full bg-white/10 backdrop-blur flex items-center justify-center text-white group-hover:scale-110 transition-transform">
              <i class="fas fa-arrow-right"></i>
            </div>
          </div>
          <div class="bg-dark-card p-3 flex justify-between items-center border border-t-0 border-dark-border rounded-b-xl">
            <span class="text-xs text-gray-400">活动时间: {{ banner.time_label }}</span>
            <span class="text-xs text-brand-primary">查看详情</span>
          </div>
        </div>
      </div>

      <!-- 错误提示 -->
      <div v-if="error" class="bg-red-900/20 border border-red-500/30 rounded-xl p-4 text-center">
        <div class="text-red-400 text-sm mb-2">{{ error }}</div>
        <button class="text-xs text-brand-primary" @click="loadActivities">重新加载</button>
      </div>
    </div>

    <!-- 领取成功 Toast -->
    <Transition name="toast">
      <div
        v-if="toastMsg"
        class="fixed bottom-28 left-1/2 -translate-x-1/2 bg-brand-success text-white text-sm px-5 py-2.5 rounded-full shadow-lg z-50 whitespace-nowrap"
      >
        <i class="fas fa-check-circle mr-1"></i> {{ toastMsg }}
      </div>
    </Transition>

    <!-- 活动详情弹窗卡片 -->
    <Transition name="toast">
      <div v-if="detailModal.show" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <!-- 背景遮罩 -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeDetailModal"></div>
        
        <!-- 弹窗内容 -->
        <div class="relative w-full max-w-sm bg-dark-card border border-dark-border rounded-2xl shadow-2xl overflow-hidden">
          <div :class="['h-24 bg-gradient-to-r', detailModal.data?.gradient || 'from-brand-primary to-purple-600']"></div>
          
          <button @click="closeDetailModal" class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-full bg-black/20 text-white hover:bg-black/40 transition-colors">
            <i class="fas fa-times"></i>
          </button>
          
          <div class="px-5 pt-0 pb-6 relative -mt-8">
            <div class="w-16 h-16 rounded-2xl bg-dark-surface border-4 border-dark-card flex items-center justify-center shadow-lg mb-3" :class="getTaskIconClass(detailModal.data!)">
              <i :class="[getTaskIcon(detailModal.data!), 'text-2xl']"></i>
            </div>
            
            <span class="inline-block px-2 py-0.5 rounded text-[10px] bg-white/10 text-brand-primary mb-2 border border-brand-primary/20">
              {{ detailModal.data?.tag || '活动' }}
            </span>
            <h3 class="text-xl font-bold text-white mb-2">{{ detailModal.data?.title }}</h3>
            
            <div class="bg-dark-surface rounded-xl p-3 border border-dark-border mb-4 text-sm text-gray-400 leading-relaxed">
              {{ detailModal.data?.desc }}
            </div>
            
            <div class="space-y-2 mb-6">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">活动类型</span>
                <span class="text-gray-300">{{ getTypeName(detailModal.data?.type || 1) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">活动奖励</span>
                <span class="text-brand-accent font-bold">{{ detailModal.data?.reward?.toFixed(2) }} 元</span>
              </div>
              <div class="flex justify-between text-sm" v-if="detailModal.data?.target">
                <span class="text-gray-500">目标金额</span>
                <span class="text-gray-300">{{ detailModal.data?.target?.toFixed(2) }} </span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">参与周期</span>
                <span class="text-gray-300">{{ getCycleName(detailModal.data?.cycle || 1) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">活动时间</span>
                <span class="text-gray-300">{{ detailModal.data?.time_label || '长期有效' }}</span>
              </div>
            </div>
            
            <button @click="closeDetailModal" class="w-full py-3 rounded-xl bg-dark-surface border border-dark-border text-white font-bold hover:bg-dark-border active:scale-95 transition-all">
              我知道了
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getActivityList, claimActivityReward, type ActivityItem } from '@/api/activity'
import { useAuthStore } from '@/store/auth'

const authStore = useAuthStore()

const loading    = ref(true)
const error      = ref('')
const tasks      = ref<ActivityItem[]>([])
const banners    = ref<ActivityItem[]>([])
const doneCount  = ref(0)
const totalCount = ref(0)
const claimingId = ref<number | null>(null)
const toastMsg   = ref('')

const detailModal = ref<{show: boolean, data: ActivityItem | null}>({show: false, data: null})

function openDetailModal(item: ActivityItem) {
  detailModal.value = { show: true, data: item }
}

function closeDetailModal() {
  detailModal.value.show = false
}

function getTypeName(type: number) {
  if (type === 1) return '每日签到'
  if (type === 2) return '充值任务'
  if (type === 3) return '投注任务'
  return '展示活动'
}

function getCycleName(cycle: number) {
  if (cycle === 1) return '每日循环'
  if (cycle === 2) return '每周循环'
  if (cycle === 3) return '仅限一次'
  return '不限'
}

function getTaskIcon(task: ActivityItem): string {
  if (task.type === 1) return 'fas fa-sign-in-alt'
  if (task.type === 2) return 'fas fa-coins'
  if (task.type === 3) return 'fas fa-dice'
  return 'fas fa-gift'
}

function getTaskIconClass(task: ActivityItem): string {
  if (task.claimed)   return 'bg-gray-700/50 text-gray-500'
  if (task.completed) return 'bg-brand-success/10 text-brand-success'
  if (task.type === 1) return 'bg-brand-primary/10 text-brand-primary'
  if (task.type === 2) return 'bg-brand-accent/10 text-brand-accent'
  return 'bg-orange-500/10 text-orange-500'
}

function getProgressLabel(task: ActivityItem): string {
  if (task.type === 2) return `充值进度`
  if (task.type === 3) return `投注进度`
  return ''
}

async function loadActivities() {
  loading.value = true
  error.value   = ''
  try {
    const res: any = await getActivityList()
    // request.ts interceptor un-wraps { code: 200, data: {...} } and returns data directly
    if (res && typeof res === 'object' && res.tasks !== undefined) {
      tasks.value      = res.tasks   || []
      banners.value    = res.banners || []
      doneCount.value  = res.done_count  || 0
      totalCount.value = res.total_count || 0
    } else {
      error.value = '活动数据格式错误'
    }
  } catch (e: any) {
    // request.ts will reject with error message if API returns code != 200
    error.value = e.message || '网络错误，请稍后重试'
  } finally {
    loading.value = false
  }
}

async function handleClaim(task: ActivityItem) {
  if (claimingId.value !== null) return
  claimingId.value = task.id
  try {
    const res: any = await claimActivityReward(task.id)
    // request.ts interceptor already checks code === 200
    // and returns res.data (which contains reward and new_balance for claim action)
    if (res) {
      // 更新本地状态
      task.claimed   = true
      task.completed = false
      doneCount.value++
      showToast(`+${res.reward?.toFixed(2) || task.reward.toFixed(2)} 元已到账！`)
      
      // 刷新用户余额
      authStore.fetchBalance()
    }
  } catch (e: any) {
    showToast(e.message || '领取失败，请重试', true)
  } finally {
    claimingId.value = null
  }
}

function showToast(msg: string, _isError = false) {
  toastMsg.value = msg
  setTimeout(() => { toastMsg.value = '' }, 2500)
}

onMounted(() => {
  loadActivities()
})
</script>

<style scoped>
.ml-13 { margin-left: 3.25rem; }

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translate(-50%, 10px);
}
</style>
