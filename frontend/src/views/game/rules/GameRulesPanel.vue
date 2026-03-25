<template>
  <!-- 遮罩层 -->
  <Transition name="overlay">
    <div v-if="modelValue" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm" @click="$emit('update:modelValue', false)" />
  </Transition>

  <!-- 抽屉主体：从右滑入 -->
  <Transition name="drawer">
    <div v-if="modelValue" class="fixed inset-y-0 right-0 z-50 w-full max-w-md flex flex-col rules-panel">

      <!-- 头部渐变 -->
      <div class="rules-header relative overflow-hidden px-4 py-4 flex-shrink-0">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-primary/30 via-purple-900/20 to-transparent pointer-events-none" />
        <div class="absolute inset-0 rules-header-grid opacity-10 pointer-events-none" />

        <div class="relative flex items-center justify-between">
          <button
            @click="$emit('update:modelValue', false)"
            class="w-9 h-9 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-white hover:bg-white/20 transition-all active:scale-90"
          >
            <i class="fas fa-arrow-left text-sm" />
          </button>
          <div class="text-center">
            <div class="flex items-center gap-2 justify-center">
              <i class="fas fa-book-open text-brand-accent text-sm" />
              <span class="text-white font-bold tracking-wide">{{ gameName }}</span>
            </div>
            <span class="text-[11px] text-gray-400 mt-0.5 block">玩法说明</span>
          </div>
          <div class="w-9 h-9" /><!-- spacer -->
        </div>

        <!-- Tab 栏 -->
        <div class="flex gap-2 mt-4 overflow-x-auto no-scrollbar pb-1">
          <button
            v-for="group in rulesGroups"
            :key="group.id"
            @click="activeTab = group.id"
            class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200 border"
            :class="activeTab === group.id
              ? 'bg-gradient-to-r from-brand-primary to-purple-600 border-brand-primary/60 text-white shadow-glow-blue'
              : 'bg-white/5 border-white/10 text-gray-400 hover:border-brand-primary/40 hover:text-gray-200'"
          >
            <i :class="group.icon + ' text-[10px]'" />
            {{ group.name }}
          </button>
        </div>
      </div>

      <!-- 重要公告（折叠） -->
      <div class="mx-4 mt-3 flex-shrink-0">
        <button
          @click="showNotice = !showNotice"
          class="w-full flex items-center justify-between px-3 py-2 rounded-lg bg-yellow-500/10 border border-yellow-500/20 text-xs text-yellow-400"
        >
          <span class="flex items-center gap-2"><i class="fas fa-exclamation-triangle" /> 重要投注声明</span>
          <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': showNotice }" />
        </button>
        <Transition name="notice">
          <div v-if="showNotice" class="px-3 py-2 bg-yellow-500/5 border border-yellow-500/10 border-t-0 rounded-b-lg text-[11px] text-gray-400 leading-relaxed">
            一旦投注被接受，则不得取消或修改。所有号码赔率将不时浮动，派彩时的赔率将以确认投注时之赔率为准。所有投注派彩彩金皆含本金。
          </div>
        </Transition>
      </div>

      <!-- 规则内容区 -->
      <div class="flex-1 overflow-y-auto no-scrollbar px-4 py-3 space-y-3 pb-8">
        <TransitionGroup name="cards" tag="div" class="space-y-3">
          <div
            v-for="(item, idx) in currentItems"
            :key="item.name"
            class="rule-card relative overflow-hidden rounded-2xl border p-4"
            :class="cardBorderClass(item.accent)"
            :style="{ transitionDelay: `${idx * 40}ms` }"
          >
            <!-- 背景装饰 -->
            <div class="absolute top-0 right-0 w-20 h-20 rounded-bl-full opacity-10 pointer-events-none" :class="cardGlowClass(item.accent)" />

            <!-- 玩法名 -->
            <div class="flex items-center gap-2 mb-2">
              <span class="w-1 h-5 rounded-full flex-shrink-0" :class="accentBarClass(item.accent)" />
              <h3 class="font-bold text-sm" :class="accentTextClass(item.accent)">{{ item.name }}</h3>
            </div>

            <!-- 规则描述 -->
            <p class="text-gray-300 text-xs leading-relaxed mb-2 whitespace-pre-line">{{ item.desc }}</p>

            <!-- 中奖条件 -->
            <div class="flex items-start gap-2 bg-white/5 rounded-lg px-3 py-2 border border-white/10">
              <i class="fas fa-trophy text-brand-accent text-xs mt-0.5 flex-shrink-0" />
              <p class="text-xs text-brand-accent leading-relaxed">{{ item.win }}</p>
            </div>

            <!-- 示例 -->
            <div v-if="item.example" class="mt-2 flex items-start gap-2">
              <i class="fas fa-lightbulb text-yellow-500 text-xs mt-0.5 flex-shrink-0" />
              <p class="text-[11px] text-gray-500 leading-relaxed">{{ item.example }}</p>
            </div>
          </div>
        </TransitionGroup>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { getRulesData, RulesGroup } from './data/index'

const props = defineProps<{
  modelValue: boolean
  template: string
  gameName: string
}>()
defineEmits(['update:modelValue'])

const showNotice = ref(false)

const rulesGroups = computed<RulesGroup[]>(() => getRulesData(props.template))

const activeTab = ref('')
watch(() => rulesGroups.value, (groups) => {
  if (groups.length && !groups.find(g => g.id === activeTab.value)) {
    activeTab.value = groups[0]?.id ?? ''
  }
}, { immediate: true })

const currentItems = computed(() => {
  const group = rulesGroups.value.find(g => g.id === activeTab.value)
  return group?.items ?? []
})

// Accent color helpers — always return a string for TS safety
const borderMap: Record<string, string> = {
  blue:   'border-blue-500/20 bg-blue-950/30',
  gold:   'border-yellow-500/20 bg-yellow-950/30',
  green:  'border-green-500/20 bg-green-950/30',
  red:    'border-red-500/20 bg-red-950/30',
  purple: 'border-purple-500/20 bg-purple-950/30',
  cyan:   'border-cyan-500/20 bg-cyan-950/30',
  orange: 'border-orange-500/20 bg-orange-950/30',
}
const glowMap: Record<string, string> = {
  blue: 'bg-blue-500', gold: 'bg-yellow-500', green: 'bg-green-500',
  red: 'bg-red-500', purple: 'bg-purple-500', cyan: 'bg-cyan-500', orange: 'bg-orange-500',
}
const barMap: Record<string, string> = {
  blue: 'bg-blue-400', gold: 'bg-yellow-400', green: 'bg-green-400',
  red: 'bg-red-400', purple: 'bg-purple-400', cyan: 'bg-cyan-400', orange: 'bg-orange-400',
}
const textMap: Record<string, string> = {
  blue: 'text-blue-300', gold: 'text-yellow-300', green: 'text-green-300',
  red: 'text-red-300', purple: 'text-purple-300', cyan: 'text-cyan-300', orange: 'text-orange-300',
}

const cardBorderClass = (a: string): string => borderMap[a] ?? 'border-dark-border bg-dark-card'
const cardGlowClass   = (a: string): string => glowMap[a] ?? 'bg-gray-500'
const accentBarClass  = (a: string): string => barMap[a] ?? 'bg-gray-400'
const accentTextClass = (a: string): string => textMap[a] ?? 'text-gray-200'
</script>


<style scoped>
.rules-panel {
  background: linear-gradient(180deg, #0d1117 0%, #111827 100%);
  box-shadow: -8px 0 32px rgba(0, 0, 0, 0.6);
}

.rules-header-grid {
  background-image:
    linear-gradient(rgba(99, 102, 241, 0.15) 1px, transparent 1px),
    linear-gradient(90deg, rgba(99, 102, 241, 0.15) 1px, transparent 1px);
  background-size: 24px 24px;
}

/* 抽屉动画 */
.drawer-enter-active { transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1); }
.drawer-leave-active { transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1); }
.drawer-enter-from { transform: translateX(100%); }
.drawer-leave-to  { transform: translateX(100%); }

/* 遮罩动画 */
.overlay-enter-active, .overlay-leave-active { transition: opacity 0.3s; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

/* 卡片 stagger 动画 */
.cards-enter-active {
  transition: all 0.35s ease;
}
.cards-enter-from {
  opacity: 0;
  transform: translateY(16px);
}

/* 公告折叠 */
.notice-enter-active, .notice-leave-active { transition: all 0.2s ease; }
.notice-enter-from, .notice-leave-to { opacity: 0; max-height: 0; }
.notice-enter-to, .notice-leave-from { max-height: 200px; }

/* 自定义规则卡片 hover */
.rule-card {
  transition: border-color 0.2s, box-shadow 0.2s;
}
.rule-card:hover {
  box-shadow: 0 0 16px rgba(99, 102, 241, 0.12);
}
</style>
