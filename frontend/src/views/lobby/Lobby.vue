<template>
  <div class="flex-1 overflow-y-auto no-scrollbar pb-24 bg-dark-bg">
    <!-- 头部搜索 -->
    <div class="sticky top-0 z-20 bg-dark-bg/95 backdrop-blur px-4 py-3 border-b border-dark-border">
      <div class="flex items-center gap-3 bg-dark-surface rounded-xl px-3 py-2 border border-dark-border focus-within:border-brand-primary transition-colors">
        <i class="fas fa-search text-gray-500"></i>
        <input type="text" placeholder="搜索游戏..." class="bg-transparent border-none outline-none text-sm text-white w-full">
      </div>
    </div>

    <!-- 分类 Tab -->
    <div class="px-4 py-3 flex gap-3 overflow-x-auto no-scrollbar">
      <button v-for="cat in categories" :key="cat.id" 
          @click="activeCategory = cat.id"
          class="px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap shrink-0 transition-all border"
          :class="activeCategory === cat.id ? 'bg-brand-primary border-brand-primary text-white shadow-glow-blue' : 'bg-dark-card border-dark-border text-gray-400'">
        {{ cat.name }}
      </button>
    </div>

    <!-- 游戏列表 -->
    <div class="px-4 grid grid-cols-2 gap-3">
      <div v-for="game in filteredGames" :key="game.id" @click="goToGame(game.id)"
           class="bg-dark-card rounded-xl p-3 border border-dark-border relative overflow-hidden group active:scale-95 transition-transform cursor-pointer">
        <!-- 背景图标水印 -->
        <i :class="[game.icon, 'absolute -right-2 -bottom-2 text-6xl opacity-5 transform rotate-12 transition-transform group-hover:scale-110', game.color]"></i>
        
        <div class="flex flex-col h-full relative z-10">
          <div class="flex justify-between items-start mb-2">
            <div class="w-10 h-10 rounded-lg bg-dark-surface flex items-center justify-center border border-dark-border group-hover:border-brand-primary/30 transition-colors">
              <i :class="[game.icon, 'text-xl', game.color]"></i>
            </div>
            <span v-if="game.hot" class="px-1.5 py-0.5 rounded bg-brand-danger/20 text-brand-danger text-[10px] border border-brand-danger/20">HOT</span>
          </div>
          <h3 class="text-sm font-bold text-gray-200">{{ game.name }}</h3>
          <p class="text-[10px] text-gray-500 mt-1">{{ game.desc }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getLobbyList } from '@/api/game'

const router = useRouter()

const goToGame = (id: number) => {
  router.push(`/game/${id}`)
}

const activeCategory = ref('all')

// We will build categories dynamically based on returned templates, or hardcode common ones
const categories = ref([
  { id: 'all', name: '全部' },
  { id: 'pk10', name: '赛车系' },
  { id: 'cqssc', name: '时时彩' },
  { id: 'lhc', name: '六合彩' },
  { id: 'pc28', name: 'PC蛋蛋' },
  { id: 'k3', name: '快三' },
])

// Mapping backend templates to frontend UI
const templateMap: Record<string, any> = {
  'pk10': { icon: 'fas fa-car', color: 'text-brand-primary' },
  'cqssc': { icon: 'fas fa-ticket-alt', color: 'text-brand-danger' },
  'lhc': { icon: 'fas fa-dragon', color: 'text-brand-success' },
  'pc28': { icon: 'fas fa-egg', color: 'text-brand-accent' },
  'k3': { icon: 'fas fa-dice-three', color: 'text-red-400' },
  'default': { icon: 'fas fa-gamepad', color: 'text-purple-400' }
}

const allGames = ref<any[]>([])

// 暂不支持的彩种模板（无Vue3模板文件 或 DB已关闭）
const HIDDEN_TEMPLATES = ['a11x5', 'klsf', 'fantan', 'liubo']

const fetchGames = async () => {
  try {
    const res: any = await getLobbyList()
    if (res && res.games) {
      allGames.value = res.games
        .filter((g: any) => !HIDDEN_TEMPLATES.includes(g.template))
        .map((g: any) => {
          let tmpl = g.template || 'default'
          if (tmpl === 'xync') tmpl = 'pk10'  // xync 归类到赛车系

          const ui = templateMap[tmpl] || templateMap['default']
          return {
            id: g.gameid,
            name: g.name,
            desc: g.content || '热门推荐',
            icon: ui.icon,
            color: ui.color,
            hot: g.sort > 90,
            type: tmpl
          }
        })
    }
  } catch (err) {
    console.error('获取大厅列表失败', err)
  }
}

onMounted(() => {
  fetchGames()
})

const filteredGames = computed(() => {
  if (activeCategory.value === 'all') return allGames.value
  return allGames.value.filter(g => g.type === activeCategory.value)
})
</script>
