<template>
  <div class="flex flex-col h-screen bg-dark-bg text-gray-200 overflow-hidden">
    <!-- 顶部 -->
    <header class="px-4 py-3 flex items-center justify-between flex-shrink-0 bg-dark-surface border-b border-dark-border">
      <span class="font-bold text-white text-lg">📊 开奖大厅</span>
      <span class="text-xs text-gray-500">实时数据</span>
    </header>

    <!-- 彩种切换 Tab -->
    <div class="flex gap-2 px-4 py-3 overflow-x-auto no-scrollbar flex-shrink-0 border-b border-dark-border/50">
      <button
        v-for="cat in categories"
        :key="cat.id"
        @click="activeCategory = cat.id"
        class="flex-shrink-0 px-4 py-1.5 rounded-full text-xs font-medium transition-all duration-200 border"
        :class="activeCategory === cat.id
          ? 'bg-gradient-to-r from-brand-primary to-purple-600 border-brand-primary/60 text-white shadow-glow-blue'
          : 'bg-dark-card border-dark-border text-gray-400 hover:text-white'"
      >{{ cat.name }}</button>
    </div>

    <!-- 游戏列表 -->
    <div class="flex-1 overflow-y-auto no-scrollbar px-4 py-3 space-y-3 pb-24">
      <!-- 加载中 -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-16 gap-4">
        <div class="w-10 h-10 rounded-full border-2 border-brand-primary border-t-transparent animate-spin" />
        <p class="text-gray-400 text-sm">加载开奖数据...</p>
      </div>

      <!-- 无数据 -->
      <div v-else-if="filteredGames.length === 0" class="flex flex-col items-center justify-center py-20 gap-3">
        <i class="fas fa-inbox text-4xl text-gray-600" />
        <p class="text-gray-500 text-sm">暂无开奖数据</p>
      </div>

      <!-- 游戏卡片 -->
      <div
        v-for="game in filteredGames"
        :key="game.gameid"
        class="bg-dark-card rounded-2xl border border-dark-border overflow-hidden transition-all duration-200"
        :class="expandedId === game.gameid ? 'border-brand-primary/40' : 'hover:border-white/20'"
      >
        <!-- 游戏头部（可点击展开历史） -->
        <div class="p-4 cursor-pointer" @click="toggleExpand(game.gameid)">
          <div class="flex justify-between items-center mb-3">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center text-sm" :class="getGameIconBg(game.template)">
                <i :class="getGameIcon(game.template)" />
              </div>
              <div>
                <span class="font-bold text-white text-sm">{{ game.name }}</span>
                <div class="text-[10px] text-gray-500">第 {{ game.lastIssue }} 期</div>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-500">{{ game.lastTime }}</span>
              <i class="fas fa-chevron-down text-gray-600 text-xs transition-transform duration-300"
                 :class="{'rotate-180': expandedId === game.gameid}" />
            </div>
          </div>

          <!-- 最新号码球 -->
          <div v-if="game.lastNumbers.length" class="flex flex-wrap gap-1.5 mt-1">
            <span
              v-for="(num, idx) in game.lastNumbers"
              :key="idx"
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shadow-sm transition-transform hover:scale-110"
              :class="getBallClass(game.template, num)"
            >{{ num }}</span>
          </div>
          <div v-else class="text-xs text-gray-600 italic">暂无开奖数据</div>

          <!-- 统计数据（仅时时彩/PC28等）-->
          <div v-if="game.lastNumbers.length && showSum(game.template)" class="flex gap-3 mt-2 text-xs text-gray-500">
            <span>总和: <b class="text-white">{{ calcSum(game.lastNumbers) }}</b></span>
            <span>{{ calcDX(game.template, game.lastNumbers) }}</span>
            <span>{{ calcDS(game.lastNumbers) }}</span>
          </div>
        </div>

        <!-- 历史记录展开区 -->
        <div v-show="expandedId === game.gameid" class="border-t border-dark-border/50">
          <!-- 历史期号加载中 -->
          <div v-if="loadingHistory[game.gameid]" class="py-4 flex justify-center">
            <div class="w-4 h-4 rounded-full border-2 border-brand-primary border-t-transparent animate-spin" />
          </div>

          <template v-else-if="historyMap[game.gameid]?.length">
            <div class="px-4 py-2 text-xs text-gray-500 flex justify-between bg-dark-surface/50">
              <span>期号</span>
              <span>开奖号码</span>
              <span>时间</span>
            </div>
            <div
              v-for="h in historyMap[game.gameid]"
              :key="h.qishu"
              class="flex justify-between items-center px-4 py-2.5 border-b border-dark-border/20 last:border-0 hover:bg-white/5 transition-colors"
            >
              <span class="font-mono text-gray-500 text-xs w-24">{{ h.qishu }}</span>
              <div class="flex gap-0.5 flex-1 justify-center flex-wrap">
                <span
                  v-for="(n, ni) in h.numbers"
                  :key="ni"
                  class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                  :class="getBallClass(game.template, n)"
                >{{ n }}</span>
              </div>
              <span class="text-[10px] text-gray-600 w-14 text-right">{{ formatTime(h.sendtime) }}</span>
            </div>
          </template>
          <div v-else class="py-4 text-center text-xs text-gray-600">暂无历史数据</div>

          <!-- 前往投注 -->
          <div class="p-3 flex justify-center border-t border-dark-border/30 bg-dark-surface/30">
            <button
              @click="$router.push(`/game/${game.gameid}`)"
              class="px-5 py-1.5 rounded-full bg-gradient-to-r from-brand-primary to-purple-600 text-white text-xs font-medium shadow-glow-blue active:scale-95 transition-all"
            >
              <i class="fas fa-dice mr-1.5" />立即投注
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { getLobbyList, getHistoryResults } from '@/api/game'
import { getBallColorClass } from '@/views/game/templates'

const loading = ref(true)
const allGames = ref<any[]>([])
const activeCategory = ref('all')
const expandedId = ref<number | null>(null)
const historyMap = ref<Record<number, any[]>>({})
const loadingHistory = ref<Record<number, boolean>>({})

// ─── 彩种分类 ───────────────────────────────────────────────────────────
const HIDDEN = ['a11x5', 'klsf', 'fantan', 'liubo']

const categories = [
  { id: 'all',   name: '全部' },
  { id: 'pk10',  name: '赛车系' },
  { id: 'cqssc', name: '时时彩' },
  { id: 'lhc',   name: '六合彩' },
  { id: 'pc28',  name: 'PC蛋蛋' },
  { id: 'k3',    name: '快三' },
]

const filteredGames = computed(() => {
  if (activeCategory.value === 'all') return allGames.value
  return allGames.value.filter(g => g.template === activeCategory.value)
})

// ─── 数据加载 ───────────────────────────────────────────────────────────
const fetchGames = async () => {
  loading.value = true
  try {
    const res: any = await getLobbyList()
    if (res?.games) {
      allGames.value = res.games
        .filter((g: any) => !HIDDEN.includes(g.template))
        .map((g: any) => {
          const haoma = g.last_haoma || ''
          let nums: (number | string)[] = []
          if (haoma) nums = haoma.split(',').map((n: string) => {
            const v = Number(n.trim())
            return isNaN(v) ? n.trim() : v
          })
          return {
            gameid: g.gameid,
            name: g.name,
            template: g.template,
            lastIssue: g.last_issue || '',
            lastNumbers: nums,
            lastTime: g.last_time ? formatTime(g.last_time) : '',
          }
        })
    }
  } catch (e) {
    console.error('fetch games failed', e)
  } finally {
    loading.value = false
  }
}

const toggleExpand = async (gameid: number) => {
  if (expandedId.value === gameid) {
    expandedId.value = null
    return
  }
  expandedId.value = gameid
  if (historyMap.value[gameid]) return  // already loaded

  loadingHistory.value[gameid] = true
  try {
    const res: any = await getHistoryResults(gameid, 10)
    const list = res?.list || []
    historyMap.value[gameid] = list.map((h: any) => ({
      qishu: h.qishu,
      sendtime: h.sendtime,
      numbers: h.haoma ? h.haoma.split(',').map((n: string) => {
        const v = Number(n.trim()); return isNaN(v) ? n.trim() : v
      }) : []
    }))
  } catch (e) {
    historyMap.value[gameid] = []
  } finally {
    loadingHistory.value[gameid] = false
  }
}

// ─── 工具函数 ─────────────────────────────────────────────────────────────
const getGameIcon = (tmpl: string) => ({
  pk10: 'fas fa-car', cqssc: 'fas fa-ticket-alt', lhc: 'fas fa-dragon',
  pc28: 'fas fa-egg', k3: 'fas fa-dice-three',
}[tmpl] || 'fas fa-gamepad')

const getGameIconBg = (tmpl: string) => ({
  pk10: 'bg-brand-primary/20 text-brand-primary',
  cqssc: 'bg-red-500/20 text-red-400',
  lhc: 'bg-green-500/20 text-green-400',
  pc28: 'bg-purple-500/20 text-purple-400',
  k3: 'bg-orange-500/20 text-orange-400',
}[tmpl] || 'bg-gray-500/20 text-gray-400')

const getBallClass = (template: string, num: number | string) => {
  const n = Number(num)
  if (!isNaN(n)) return getBallColorClass(n, template)
  return 'bg-gray-600 text-white'
}

const showSum = (tmpl: string) => ['cqssc', 'k3', 'pc28'].includes(tmpl)

const calcSum = (nums: (number | string)[]): number =>
  nums.map(n => Number(n)).reduce((a, b) => a + b, 0)

const calcDX = (tmpl: string, nums: (number | string)[]): string => {
  const s: number = calcSum(nums)
  if (tmpl === 'cqssc') return s >= 23 ? '大' : '小'
  if (tmpl === 'pc28')  return s >= 14 ? '大' : '小'
  if (tmpl === 'k3')    return s >= 11 ? '大' : '小'
  return ''
}

const calcDS = (nums: (number | string)[]): string => {
  const s: number = calcSum(nums)
  return s % 2 === 0 ? '双' : '单'
}

const formatTime = (ts: number) => {
  if (!ts) return ''
  const d = new Date(ts * 1000)
  return `${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`
}

onMounted(fetchGames)
</script>
