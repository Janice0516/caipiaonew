<template>
  <div class="flex flex-col h-screen bg-dark-bg text-gray-200">
    <!-- Header -->
    <header class="relative h-12 flex items-center justify-between px-4 z-30 bg-dark-surface border-b border-dark-border">
      <button @click="$router.back()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white">
        <i class="fas fa-arrow-left"></i>
      </button>
      <h1 class="text-white font-bold text-lg">注单记录</h1>
      <div class="w-8"></div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 space-y-4">
      <!-- 筛选 Tab -->
      <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar">
        <button v-for="tab in tabs" :key="tab.id"
                @click="currentTab = tab.id"
                class="px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap transition-all border"
                :class="currentTab === tab.id ? 'bg-brand-primary border-brand-primary text-white shadow-glow-blue' : 'bg-dark-card border-dark-border text-gray-400'">
          {{ tab.name }}
        </button>
      </div>

      <div v-for="order in filteredOrders" :key="order.id" class="bg-dark-card rounded-xl border border-dark-border p-4">
        <div class="flex justify-between items-start mb-3">
          <div>
            <h3 class="font-bold text-white">{{ order.game }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">第 {{ order.issue }} 期</p>
          </div>
          <span :class="['text-xs px-2 py-0.5 rounded border', getStatusClass(order.status)]">
            {{ order.statusText }}
          </span>
        </div>
        
        <div class="flex justify-between items-center text-sm mb-3">
          <span class="text-gray-400">投注内容</span>
          <span class="text-white font-medium">{{ order.content }}</span>
        </div>
        
        <div class="flex justify-between items-center text-sm border-t border-dark-border pt-3">
          <span class="text-gray-400">投注金额</span>
          <span class="text-white font-bold">¥ {{ order.amount.toFixed(2) }}</span>
        </div>
        
        <div v-if="order.win > 0" class="flex justify-between items-center text-sm mt-2">
          <span class="text-gray-400">中奖金额</span>
          <span class="text-brand-success font-bold">+ {{ order.win.toFixed(2) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { getBetHistory } from '@/api/bet'

const currentTab = ref('all')
const tabs = [
  { id: 'all', name: '全部' },
  { id: '0', name: '待开奖' },
  { id: '1', name: '已中奖' },
  { id: '2', name: '未中奖' },
]

const orders = ref<any[]>([])

// Calculate status based on account and tui fields
const getOrderStatus = (o: any) => {
  let key = 'pending'
  let label = '待开奖'
  const acc = parseFloat(o.account || '0')
  
  if (o.tui == 1 || o.ban == 1 || o.ban == 3) {
    key = 'cancelled'
    label = '已撤单'
  } else if (acc < 0) {
    key = 'lost'
    label = '未中奖'
  } else if (acc > 0) {
    key = 'won'
    label = '已中奖'
  }
  
  return { key, label, winAmt: acc > 0 ? acc : 0 }
}

const fetchOrders = async (reset = false) => {
  try {
    const res: any = await getBetHistory({ page: 1, limit: 30 })
    if (res && res.list) {
      const mapped = res.list.map((o: any) => {
        const s = getOrderStatus(o)
        return {
          id: o.id,
          game: o.gamename || ('游戏 ' + o.gameid),
          issue: o.qishu,
          content: o.show_wanfa || o.wanfa,
          amount: parseFloat(o.money),
          win: s.winAmt,
          status: s.key,
          statusText: s.label,
          addtime: o.addtime
        }
      })
      if (reset) orders.value = mapped
      else orders.value = [...orders.value, ...mapped]
    }
  } catch(err) {
    console.error('注单获取失败', err)
  }
}

onMounted(() => fetchOrders(true))

const filteredOrders = computed(() => {
  if (currentTab.value === 'all') return orders.value
  const statusKey = ['pending', 'won', 'lost', 'cancelled'][Number(currentTab.value)]
  return orders.value.filter(o => o.status === statusKey)
})

const getStatusClass = (status: string) => {
  switch (status) {
    case 'pending': return 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20'
    case 'won': return 'bg-green-500/10 text-green-500 border-green-500/20'
    case 'lost': return 'bg-gray-500/10 text-gray-500 border-gray-500/20'
    default: return 'bg-red-500/10 text-red-400 border-red-500/20'
  }
}
</script>