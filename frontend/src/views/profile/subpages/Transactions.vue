<template>
  <div class="flex flex-col h-screen bg-dark-bg text-gray-200">
    <!-- Header -->
    <header class="relative h-12 flex items-center justify-between px-4 z-30 bg-dark-surface border-b border-dark-border">
      <button @click="$router.back()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white">
        <i class="fas fa-arrow-left"></i>
      </button>
      <h1 class="text-white font-bold text-lg">资金明细</h1>
      <div class="w-8"></div>
    </header>

    <div class="flex-1 overflow-y-auto p-4">
      <!-- 筛选 Tab -->
      <div class="flex gap-2 overflow-x-auto pb-4 no-scrollbar">
        <button v-for="tab in tabs" :key="tab.id"
                @click="onTabChange(tab.id)"
                class="px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all border"
                :class="currentTab === tab.id ? 'bg-brand-primary border-brand-primary text-white shadow-glow-blue' : 'bg-dark-card border-dark-border text-gray-400'">
          {{ tab.name }}
        </button>
      </div>

      <div class="space-y-3">
        <div v-for="item in filteredList" :key="item.id" class="bg-dark-card rounded-xl p-4 border border-dark-border flex justify-between items-center">
          <div class="flex items-center gap-3">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-lg', getIconClass(item.type)]">
              <i :class="getIcon(item.type)"></i>
            </div>
            <div>
              <div class="text-sm font-bold text-white">{{ item.title }}</div>
              <div class="text-xs text-gray-500 mt-0.5">{{ item.time }}</div>
            </div>
          </div>
          <div class="text-right">
            <div :class="['font-mono font-bold', item.amount > 0 ? 'text-brand-success' : 'text-white']">
              {{ item.amount > 0 ? '+' : '' }}{{ item.amount.toFixed(2) }}
            </div>
            <div class="text-xs text-gray-500 mt-0.5">余额: {{ item.balance.toFixed(2) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { getTransactionList } from '@/api/finance'

const currentTab = ref('all')
const tabs = [
  { id: 'all', name: '全部' },
  { id: 'recharge', name: '充值' },
  { id: 'withdraw', name: '提现' },
  { id: 'bet', name: '投注' },
]

// PHP account_model type: 1=提现 2=投注 3=中奖 4=撤单 5=充值
const typeMap: Record<number, string> = { 1: 'withdraw', 2: 'bet', 3: 'win', 4: 'refund', 5: 'recharge' }

const list = ref<any[]>([])
const page = ref(1)
const hasMore = ref(true)

const fetchList = async (reset = false) => {
  if (reset) { page.value = 1; list.value = [] }
  try {
    const params: any = { page: page.value, limit: 20 }
    if (currentTab.value !== 'all') params.type = currentTab.value
    const res: any = await getTransactionList(params)
    if (res && res.list) {
      list.value = [...list.value, ...res.list.map((r: any) => ({
        id: r.id,
        type: typeMap[r.type] || 'other',
        title: r.comment || '资金变动',
        time: String(r.addtime).replace(/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, '$1-$2-$3 $4:$5:$6'),
        amount: parseFloat(r.money),
        balance: parseFloat(r.countmoney)
      }))]
      hasMore.value = res.list.length >= 20
    }
  } catch(err) {
    console.error('资金明细获取失败', err)
  }
}

onMounted(() => fetchList())

const filteredList = computed(() => {
  // Filtering is done serverside by re-fetching
  return list.value
})

const onTabChange = (id: string) => {
  currentTab.value = id
  fetchList(true)
}

const getIconClass = (type: string) => {
  switch (type) {
    case 'recharge': return 'bg-blue-500/20 text-blue-500'
    case 'withdraw': return 'bg-orange-500/20 text-orange-500'
    case 'bet': return 'bg-purple-500/20 text-purple-500'
    case 'win': return 'bg-green-500/20 text-green-500'
    default: return 'bg-gray-500/20 text-gray-500'
  }
}

const getIcon = (type: string) => {
  switch (type) {
    case 'recharge': return 'fas fa-wallet'
    case 'withdraw': return 'fas fa-hand-holding-usd'
    case 'bet': return 'fas fa-gamepad'
    case 'win': return 'fas fa-trophy'
    default: return 'fas fa-gift'
  }
}
</script>