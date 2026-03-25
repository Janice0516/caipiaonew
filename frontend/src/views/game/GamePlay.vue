<template>
  <div class="flex flex-col h-screen w-full max-w-md mx-auto relative bg-dark-bg shadow-2xl overflow-hidden">
    
    <!-- 顶部区域：含赛道背景 -->
    <header class="relative z-20 overflow-hidden bg-dark-surface shadow-lg pb-2">
      <!-- 动态背景层 -->
      <div class="absolute inset-0 race-track-bg opacity-20 pointer-events-none"></div>

      <!-- 标题栏 -->
      <div class="relative h-12 flex items-center justify-between px-4 z-30 bg-gradient-to-b from-dark-bg/90 to-transparent">
        <button @click="goBack" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 backdrop-blur text-white hover:bg-white/20">
          <i class="fas fa-arrow-left"></i>
        </button>
        <div class="flex flex-col items-center">
          <span class="text-white font-bold text-lg tracking-wider flex items-center gap-2">
            <i class="fas fa-flag-checkered text-brand-primary"></i> {{ gameInfo.name }}
          </span>
          <span class="text-[10px] text-brand-accent bg-brand-accent/10 px-2 rounded-full border border-brand-accent/20">第 {{ gameInfo.currentIssue }} 期</span>
        </div>
        <button @click="showRules = true" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 backdrop-blur text-white hover:bg-white/20">
          <i class="fas fa-bars" />
        </button>
      </div>

      <!-- 开奖展示区 (更紧凑) -->
      <div class="relative z-30 px-3 mt-2">
        <div class="flex justify-between items-center bg-dark-card/80 backdrop-blur rounded-xl p-3 border border-dark-border shadow-inner">
          <!-- 左侧：上期开奖 -->
          <div class="flex-1">
            <div class="flex justify-between items-center mb-2">
              <span class="text-[10px] text-gray-400">上期开奖</span>
              <div class="flex items-center gap-1 cursor-pointer" @click="toggleHistory">
                <span class="text-[10px] text-brand-primary">历史走势</span>
                <i class="fas fa-chart-line text-[10px] text-brand-primary"></i>
              </div>
            </div>
            <div class="flex gap-1 justify-start items-center flex-wrap">
              <div v-for="(n, idx) in lastDrawNumbers" :key="idx" :class="['w-6 h-6 rounded flex items-center justify-center text-xs font-bold text-shadow shadow-md', getBallColorClass(n)]">
                {{ n }}
              </div>
              <div v-if="['pc28', 'k3', 'cqssc'].includes(gameInfo.template) && gameInfo.history[0]?.sum" class="ml-2 px-2 bg-dark-bg/50 border border-dark-border rounded text-xs text-white flex items-center shadow-inner h-6">
                = {{ gameInfo.history[0].sum }}
              </div>
            </div>
          </div>
        </div>

        <!-- 倒计时条 (悬浮感) -->
        <div class="mt-3 flex items-center justify-between bg-gradient-to-r from-blue-900/50 to-purple-900/50 rounded-lg p-2 border border-white/10 relative overflow-hidden">
          <div class="absolute inset-0 bg-white/5 animate-pulse-fast" v-if="timeLeft <= 10"></div>
          <div class="relative flex items-center gap-2">
            <i class="fas fa-stopwatch text-brand-accent text-lg"></i>
            <span class="text-xs text-gray-300">封盘倒计时</span>
          </div>
          <div class="relative font-mono font-bold text-2xl tracking-widest text-white" :class="{'countdown-urgent': timeLeft <= 10}">
            {{ formattedTime }}
          </div>
        </div>
      </div>
    </header>

    <!-- 历史记录下拉 (CSS Transition) — 按游戏模板差异化展示 -->
    <div class="absolute top-[160px] left-0 right-0 bg-dark-surface/95 backdrop-blur z-40 border-b border-dark-border shadow-2xl transition-all duration-300 overflow-hidden"
         :style="{ height: showHistory ? '240px' : '0' }">
      <div class="p-3 h-full overflow-y-auto">

        <!-- PK10 飞艇：名次排列 + 龙虎 -->
        <table v-if="gameInfo.template === 'pk10'" class="w-full text-xs text-gray-400">
          <thead class="text-gray-500 border-b border-dark-border">
            <tr>
              <th class="py-2 text-left w-12">期号</th>
              <th class="py-2 text-center">开奖排名（冠→第十）</th>
              <th class="py-2 text-right w-10">龙虎</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in gameInfo.history" :key="item.issue" class="border-b border-dark-border/30 hover:bg-white/5">
              <td class="py-2 font-mono text-gray-500 text-[10px]">{{ String(item.issue).slice(-4) }}</td>
              <td class="py-2 text-center">
                <span v-for="(n, i) in item.numbers" :key="i"
                      class="inline-block w-5 h-5 leading-5 rounded text-[10px] font-bold mx-0.5 text-center"
                      :class="n <= 5 ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400'">{{ n }}</span>
              </td>
              <td class="py-2 text-right font-bold" :class="item.dragon === '龙' ? 'text-red-400' : 'text-blue-400'">{{ item.dragon }}</td>
            </tr>
          </tbody>
        </table>

        <!-- 时时彩（cqssc）：五星号码 + 总和 + 大小单双 -->
        <table v-else-if="gameInfo.template === 'cqssc'" class="w-full text-xs text-gray-400">
          <thead class="text-gray-500 border-b border-dark-border">
            <tr>
              <th class="py-2 text-left w-12">期号</th>
              <th class="py-2 text-center">号码</th>
              <th class="py-2 text-center w-8">和</th>
              <th class="py-2 text-right w-16">大小/单双</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in gameInfo.history" :key="item.issue" class="border-b border-dark-border/30 hover:bg-white/5">
              <td class="py-2 font-mono text-gray-500 text-[10px]">{{ String(item.issue).slice(-4) }}</td>
              <td class="py-2 text-center">
                <span v-for="(n, i) in item.numbers" :key="i"
                      class="inline-block w-5 h-5 leading-5 rounded-full text-[11px] font-bold mx-0.5 bg-brand-primary/20 text-brand-primary text-center">{{ n }}</span>
              </td>
              <td class="py-2 text-center font-bold text-white">{{ item.sum }}</td>
              <td class="py-2 text-right text-[10px]">
                <span :class="item.sum > 22 ? 'text-red-400' : 'text-blue-400'">{{ item.sum > 22 ? '大' : '小' }}</span>
                <span class="ml-1" :class="item.sum % 2 !== 0 ? 'text-yellow-400' : 'text-purple-400'">{{ item.sum % 2 !== 0 ? '单' : '双' }}</span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- 快三（k3）：骰子 + 和值 + 大小单双 -->
        <table v-else-if="gameInfo.template === 'k3'" class="w-full text-xs text-gray-400">
          <thead class="text-gray-500 border-b border-dark-border">
            <tr>
              <th class="py-2 text-left w-12">期号</th>
              <th class="py-2 text-center">骰子</th>
              <th class="py-2 text-center w-8">和</th>
              <th class="py-2 text-right w-16">大小/单双</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in gameInfo.history" :key="item.issue" class="border-b border-dark-border/30 hover:bg-white/5">
              <td class="py-2 font-mono text-gray-500 text-[10px]">{{ String(item.issue).slice(-4) }}</td>
              <td class="py-2 text-center">
                <span v-for="(n, i) in item.numbers" :key="i"
                      class="inline-block w-6 h-6 leading-6 rounded border border-brand-accent/40 text-sm font-bold mx-0.5 text-brand-accent text-center">
                  {{ n }}
                </span>
              </td>
              <td class="py-2 text-center font-bold text-white">{{ item.sum }}</td>
              <td class="py-2 text-right text-[10px]">
                <span :class="item.sum >= 11 ? 'text-red-400' : 'text-blue-400'">{{ item.sum >= 11 ? '大' : '小' }}</span>
                <span class="ml-1" :class="item.sum % 2 !== 0 ? 'text-yellow-400' : 'text-purple-400'">{{ item.sum % 2 !== 0 ? '单' : '双' }}</span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- PC28 蛋蛋：3球号码 + 和值 + 大小单双 -->
        <table v-else-if="gameInfo.template === 'pc28'" class="w-full text-xs text-gray-400">
          <thead class="text-gray-500 border-b border-dark-border">
            <tr>
              <th class="py-2 text-left w-12">期号</th>
              <th class="py-2 text-center">号码</th>
              <th class="py-2 text-center w-10">和值</th>
              <th class="py-2 text-right w-16">大小/单双</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in gameInfo.history" :key="item.issue" class="border-b border-dark-border/30 hover:bg-white/5">
              <td class="py-2 font-mono text-gray-500 text-[10px]">{{ String(item.issue).slice(-4) }}</td>
              <td class="py-2 text-center">
                <span v-for="(n, i) in item.numbers.slice(0,3)" :key="i"
                      class="inline-block w-6 h-6 leading-6 rounded-full text-[11px] font-bold mx-0.5 text-center"
                      :class="n <= 13 ? 'bg-green-500/20 text-green-400' : 'bg-orange-500/20 text-orange-400'">{{ n }}</span>
              </td>
              <td class="py-2 text-center font-bold text-white">{{ item.sum }}</td>
              <td class="py-2 text-right text-[10px]">
                <span :class="item.sum >= 14 ? 'text-red-400' : 'text-blue-400'">{{ item.sum >= 14 ? '大' : '小' }}</span>
                <span class="mx-0.5 text-gray-600">|</span>
                <span :class="item.sum % 2 !== 0 ? 'text-yellow-400' : 'text-purple-400'">{{ item.sum % 2 !== 0 ? '单' : '双' }}</span>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- 通用（lhc / fantan / klsf / a11x5 等）：开奖号码 + 特码/总和 -->
        <table v-else class="w-full text-xs text-gray-400">
          <thead class="text-gray-500 border-b border-dark-border">
            <tr>
              <th class="py-2 text-left w-12">期号</th>
              <th class="py-2 text-center">开奖号码</th>
              <th class="py-2 text-right w-12">{{ gameInfo.template === 'lhc' ? '特码' : '总和' }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in gameInfo.history" :key="item.issue" class="border-b border-dark-border/30 hover:bg-white/5">
              <td class="py-2 font-mono text-gray-500 text-[10px]">{{ String(item.issue).slice(-4) }}</td>
              <td class="py-2 text-center">
                <span v-for="(n, i) in item.numbers" :key="i"
                      class="inline-block min-w-5 px-1 h-5 leading-5 rounded bg-dark-card text-[10px] font-bold mx-0.5 text-gray-300 text-center">{{ n }}</span>
              </td>
              <td class="py-2 text-right font-bold text-white">
                {{ gameInfo.template === 'lhc' ? item.numbers[item.numbers.length - 1] : item.sum }}
              </td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>

    <!-- 投注主体区域 -->
    <div class="flex-1 overflow-y-auto no-scrollbar pb-32 bg-dark-bg relative z-0">
      <!-- 玩法导航 (胶囊式) -->
      <div class="sticky top-0 bg-dark-bg/95 backdrop-blur z-20 px-3 py-3 border-b border-dark-border/50 flex gap-3 overflow-x-auto no-scrollbar shadow-lg">
        <button v-for="tab in playTabs" :key="tab.id"
          @click="currentTab = tab.id"
          class="px-5 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all transform active:scale-95 flex-shrink-0"
          :class="currentTab === tab.id ? 'bg-gradient-to-r from-brand-primary to-blue-600 text-white shadow-glow-blue' : 'bg-dark-card text-gray-400 border border-dark-border'">
          {{ tab.name }}
        </button>
      </div>

      <!-- 选号区 -->
      <div class="p-3 space-y-5 pt-4 pb-48">
        <div v-for="(row, idx) in currentPlayRows" :key="idx" class="bg-dark-card rounded-2xl p-4 border border-dark-border shadow-glow-card relative overflow-hidden group">
          <!-- 装饰光效 -->
          <div class="absolute top-0 right-0 w-32 h-32 bg-brand-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 group-hover:bg-brand-primary/10 transition-colors"></div>

          <!-- 行标题与快捷选号 -->
          <div class="flex justify-between items-center mb-4 relative z-10">
            <div class="flex items-center gap-2">
              <span class="w-1 h-4 bg-brand-accent rounded-full"></span>
              <span class="text-base font-bold text-white">{{ row.title }}</span>
            </div>
            <div v-if="row.range" class="flex bg-dark-bg rounded-lg p-0.5 border border-dark-border">
              <button v-for="type in ['大', '小', '单', '双', '清']" :key="type"
                @click="quickSelect(row, type)" 
                class="px-2.5 py-1 text-[10px] rounded hover:bg-dark-surface text-gray-400 transition-colors">
                {{ type }}
              </button>
            </div>
          </div>
          
          <!-- 号码球/选项 Grid -->
          <div class="grid grid-cols-5 gap-y-6 gap-x-2 justify-items-center relative z-10">
            <!-- 范围循环 (数字) -->
            <template v-if="row.range">
              <div v-for="n in (row.range[1] - row.range[0] + 1)" :key="n" 
                   @click="toggleBall(row.id, n + row.range[0] - 1)"
                   class="flex flex-col items-center cursor-pointer group w-full relative">
                
                <div :class="['pk10-ball', 
                              getNumericBallClass(n + row.range[0] - 1),
                              isSelected(row.id, n + row.range[0] - 1) ? 'selected-ball' : 'opacity-80 scale-95 grayscale-[0.4] hover:grayscale-0 hover:scale-100']"
                     :style="{'--glow-color': getGlowColor(n + row.range[0] - 1)}">
                  {{ n + row.range[0] - 1 }}
                  <div v-if="isSelected(row.id, n + row.range[0] - 1)" class="absolute inset-0 bg-white/20 rounded-lg animate-pulse"></div>
                </div>
                
                <!-- 赔率 -->
                <span class="text-[10px] font-mono font-bold mt-2 transition-colors" 
                      :class="isSelected(row.id, n + row.range[0] - 1) ? 'text-brand-accent' : 'text-gray-500'">
                  {{ getBallOdds(row.id, n + row.range[0] - 1) }}
                </span>
              </div>
            </template>

            <!-- 代码循环 (如 大/小) -->
            <template v-else-if="row.codes">
              <div v-for="code in row.codes" :key="code" 
                   @click="toggleBall(row.id, code)"
                   class="flex flex-col items-center cursor-pointer group w-full relative">
                
                <div :class="['pk10-ball rounded-xl border border-dark-border/30 transition-all', 
                              gameInfo.template === 'lhc' && row.id === 'sx_tm' ? '!flex-col py-1.5 min-h-[58px] justify-center px-1' : '',
                              getCodeColorClass(code),
                              isSelected(row.id, code) ? 'selected-ball shadow-glow-blue' : 'opacity-80 scale-95 hover:opacity-100 hover:scale-100']"
                     :style="gameInfo.template === 'lhc' && row.id === 'sx_tm' ? 'min-width: 100%;' : ''">
                  <span class="text-sm font-bold flex-shrink-0 drop-shadow-md">{{ formatCodeName(code) }}</span>
                  <div v-if="gameInfo.template === 'lhc' && row.id === 'sx_tm'" class="flex gap-[2px] mt-1 justify-center w-full flex-wrap">
                    <span v-for="n in lhcZodiacMap[formatCodeName(code)]" :key="n" 
                          :class="['text-[9px] w-[14px] h-[14px] flex items-center justify-center rounded-sm font-mono leading-none shadow-sm', getNumericBallClass(n)]">
                      {{ n < 10 ? '0' + n : n }}
                    </span>
                  </div>
                </div>
                
                <span class="text-[10px] font-mono font-bold mt-2 text-gray-500">
                  {{ getBallOdds(row.id, code) }}
                </span>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- 底部投注篮 (悬浮抽屉) -->
    <div class="absolute bottom-0 left-0 right-0 z-50 w-full max-w-md mx-auto pointer-events-none pb-safe">
      
      <!-- 筹码选择器 (点击展开) -->
      <div class="pointer-events-auto bg-dark-nav/95 backdrop-blur border-t border-dark-border rounded-t-2xl shadow-[0_-5px_30px_rgba(0,0,0,0.8)] transition-transform duration-300"
           :class="{'translate-y-full': !showBetBasket && totalBets === 0}">
        
        <!-- 模式与倍数控制 -->
        <div class="px-4 py-2 border-b border-dark-border/50 flex items-center justify-between bg-dark-surface/50">
          <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400">倍数</span>
            <div class="flex items-center bg-dark-bg rounded border border-dark-border h-7">
              <button @click="multiplier > 1 && multiplier--" class="w-8 h-full flex items-center justify-center text-gray-400 hover:text-white active:bg-white/10 transition-colors rounded-l">-</button>
              <input type="number" v-model.number="multiplier" class="w-12 h-full text-center bg-transparent text-sm font-bold text-brand-primary focus:outline-none appearance-none border-x border-dark-border/30">
              <button @click="multiplier++" class="w-8 h-full flex items-center justify-center text-gray-400 hover:text-white active:bg-white/10 transition-colors rounded-r">+</button>
            </div>
          </div>
          
          <div class="flex bg-dark-bg rounded-lg p-0.5 border border-dark-border h-7">
             <button v-for="mode in moneyModes" :key="mode.val"
                     @click="moneyUnit = mode.val"
                     class="px-3 h-full text-xs rounded-md transition-all flex items-center justify-center font-medium"
                     :class="moneyUnit === mode.val ? 'bg-brand-primary text-white shadow-sm' : 'text-gray-400 hover:text-gray-200'">
               {{ mode.label }}
             </button>
          </div>
        </div>

        <!-- 筹码条 -->
        <div class="flex items-center gap-3 px-4 py-3 overflow-x-auto no-scrollbar">
          <div v-for="chip in chips" :key="chip" 
               @click="selectedChip = chip"
               class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all active:scale-90 shadow-lg relative overflow-hidden"
               :class="selectedChip === chip ? 'border-brand-accent bg-brand-accent text-black scale-110 shadow-glow-gold' : 'border-gray-600 bg-dark-card text-gray-400'">
            {{ chip }}
            <!-- 选中光效 -->
            <div v-if="selectedChip === chip" class="absolute inset-0 bg-white/30 animate-pulse"></div>
          </div>
          <!-- 自定义金额 -->
          <div class="flex-shrink-0 flex items-center bg-dark-bg rounded-full border border-dark-border px-3 h-10 ml-2">
            <span class="text-xs text-gray-500 mr-1">¥</span>
            <input type="number" v-model.number="customAmount" class="w-12 bg-transparent text-sm text-white focus:outline-none font-mono" placeholder="自定义">
          </div>
        </div>

        <!-- 汇总操作栏 -->
        <div class="px-4 py-3 flex items-center justify-between bg-dark-surface">
          <div class="flex flex-col cursor-pointer" @click="showDetails = !showDetails">
            <div class="text-sm text-gray-300 flex items-center gap-1">
              已选 <span class="text-brand-accent font-bold text-lg">{{ totalBets }}</span> 注
              <i class="fas fa-chevron-up text-xs text-gray-500 transition-transform" :class="{'rotate-180': showDetails}"></i>
            </div>
            <div class="text-xs text-gray-500">
              余额: <span class="text-white">¥ {{ authStore.userInfo.balance.toFixed(2) }}</span>
            </div>
          </div>
          
          <div class="flex gap-3">
            <button @click="clearAll" class="px-4 py-2.5 rounded-xl bg-dark-card text-gray-400 text-sm font-bold border border-dark-border active:bg-dark-border">
              清空
            </button>
            <button class="px-8 py-2.5 rounded-xl bg-gradient-to-r from-brand-primary to-blue-600 text-white text-sm font-bold shadow-glow-blue active:scale-95 transition-transform disabled:opacity-50 disabled:cursor-not-allowed flex flex-col items-center leading-none justify-center min-w-[120px]"
                    :disabled="totalBets === 0 || isBetting"
                    @click="openConfirm">
              <span v-if="!isBetting">立即投注</span>
              <span v-else>提交中...</span>
              <span class="text-[10px] opacity-80 mt-0.5 font-mono">¥ {{ totalAmount }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 注单详情弹窗 (遮罩) -->
    <div v-if="showDetails && totalBets > 0" class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm flex items-end justify-center pb-[180px]" @click.self="showDetails = false">
      <div class="w-full max-w-md bg-dark-card rounded-t-2xl border-t border-dark-border max-h-[50vh] overflow-y-auto animate-slide-up p-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-white font-bold">投注明细</h3>
          <button @click="showDetails = false" class="text-gray-500"><i class="fas fa-times"></i></button>
        </div>
        <div class="space-y-2">
          <div v-for="(nums, rowId) in selections" :key="rowId">
            <div v-if="nums.length > 0" class="bg-dark-bg p-3 rounded-lg border border-dark-border">
              <div class="text-xs text-gray-500 mb-2">{{ getRowTitle(rowId as string) }}</div>
              <div class="flex flex-wrap gap-2">
                <span v-for="n in nums" :key="n" class="w-6 h-6 rounded bg-brand-primary/20 text-brand-primary text-xs flex items-center justify-center border border-brand-primary/30">
                  {{ n }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 投注确认弹窗 -->
    <div v-if="showBetConfirm" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="w-full max-w-sm bg-dark-card rounded-2xl border border-dark-border shadow-2xl p-5 animate-slide-up">
        <h3 class="text-white font-bold text-center text-lg mb-4">确认投注</h3>
        
        <div class="space-y-3 mb-6">
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-400">期号：</span>
            <span class="text-white font-mono">{{ gameInfo.currentIssue }}期</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-400">总注数：</span>
            <span class="text-white"><span class="text-brand-accent font-bold">{{ totalBets }}</span> 注</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-400">总金额：</span>
            <span class="text-brand-accent font-mono font-bold text-lg">¥ {{ totalAmount.toFixed(2) }}</span>
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="showBetConfirm = false" class="flex-1 py-3 rounded-xl bg-dark-bg text-gray-400 font-bold border border-dark-border active:scale-95 transition-transform">
            取消
          </button>
          <button @click="submitBet" :disabled="isBetting" class="flex-1 py-3 rounded-xl bg-brand-primary text-black font-bold flex justify-center items-center active:scale-95 transition-transform disabled:opacity-50">
            <span v-if="!isBetting">确认下注</span>
            <span v-else class="text-sm">处理中...</span>
          </button>
        </div>
      </div>
    </div>
    <!-- 下注 Toast 提示 -->
    <transition name="toast">
      <div v-if="betSuccessMsg" class="fixed top-20 left-0 right-0 mx-auto w-max max-w-xs z-[100] px-5 py-3 rounded-2xl bg-green-500/90 backdrop-blur text-white text-sm font-bold shadow-lg text-center">
        {{ betSuccessMsg }}
      </div>
    </transition>
    <transition name="toast">
      <div v-if="betErrorMsg" class="fixed top-20 left-0 right-0 mx-auto w-max max-w-xs z-[100] px-5 py-3 rounded-2xl bg-red-500/90 backdrop-blur text-white text-sm font-bold shadow-lg text-center">
        {{ betErrorMsg }}
      </div>
    </transition>

    <!-- 玩法说明抽屉 -->
    <GameRulesPanel
      v-model="showRules"
      :template="gameInfo.template"
      :game-name="gameInfo.name"
    />
  </div>

</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../../store/auth'
import { submitBet as submitBetAPI } from '@/api/bet'
import { getLotteryTemplate, formatCodeName, getCodeColorClass, getDefaultNumericBallClass, getBallColorClass as getBallColorClass_imported, getGlowColor as getGlowColor_imported, calcLhcZodiacMap } from './templates'
import { useGameState } from '@/composables/useGameState'
import { useBetBasket } from '@/composables/useBetBasket'
import GameRulesPanel from './rules/GameRulesPanel.vue'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const gameId = route.params.id || '1'

// ── Game state (fetch, timer, history) ──────────────────────────────────────
const { gameInfo, timeLeft, startTimer, stopTimer, fetchHistory } = useGameState(gameId)

// ── Bet basket (chips, selections, totals) ──────────────────────────────────
const { selections, chips, selectedChip, customAmount, multiplier, moneyUnit, moneyModes, moneyUnitName, totalBets, totalAmount, toggleBall, isSelected, quickSelect, clearAll: clearSelections } = useBetBasket()

// ── UI State ────────────────────────────────────────────────────────────────
const showHistory = ref(false)
const showBetBasket = ref(true)
const showDetails = ref(false)
const showRules = ref(false)
const showBetConfirm = ref(false)

// ── Tabs ────────────────────────────────────────────────────────────────────
const currentTab = ref('ranking')
const playTabs = computed(() => getLotteryTemplate(gameInfo.value.template)?.getTabs() ?? [])
watch(() => gameInfo.value.template, (newTemp) => {
  const tabs = getLotteryTemplate(newTemp)?.getTabs() ?? []
  currentTab.value = tabs.length > 0 ? tabs[0].id : 'ranking'
})

// ── Rows ─────────────────────────────────────────────────────────────────────
const currentPlayRows = computed(() =>
  getLotteryTemplate(gameInfo.value.template)?.getPlayRows(currentTab.value) ?? []
)

// ── Helpers ──────────────────────────────────────────────────────────────────
const lastDrawNumbers = computed(() => gameInfo.value.lastIssue.numbers)

const formattedTime = computed(() => {
  const m = Math.floor(timeLeft.value / 60)
  const s = timeLeft.value % 60
  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
})

const lhcZodiacMap = computed(() => calcLhcZodiacMap())
const currentOdds = computed(() => 9.95)

const getRowTitle = (id: string) => {
  const map: Record<string, string> = { 'champion': '冠军', 'runner_up': '亚军', 'rank3': '第三名', 'rank4': '第四名', 'rank5': '第五名', 'dragon_tiger': '龙虎' }
  return map[id] || id
}

const getNumericBallClass = (n: number) => {
  const engine = getLotteryTemplate(gameInfo.value.template)
  if (!engine) return getDefaultNumericBallClass(n)
  return engine.getNumericBallClass ? engine.getNumericBallClass(n) : getDefaultNumericBallClass(n)
}

const getBallColorClass = (n: number) => getBallColorClass_imported(n, gameInfo.value.template)
const getGlowColor = (n: number) => getGlowColor_imported(n, gameInfo.value.template)

const getBallOdds = (rowId: string, n: number | string) => {
  const engine = getLotteryTemplate(gameInfo.value.template)
  if (!engine) return 9.95
  let code = engine.buildSubmitCode(rowId, n).code
  if (gameInfo.value.template !== 'pk10') {
    // For non-pk10, code is already correct
  } else if (!gameInfo.value.odds[code]) {
    // PK10 fallback: try '冠军大' style keys
    const name = typeof n === 'string' ? formatCodeName(String(n)) : String(n)
    const pkPos: Record<string, number> = { 'champion': 1, 'runner_up': 2, 'rank3': 3, 'rank4': 4, 'rank5': 5, 'rank6': 6, 'rank7': 7, 'rank8': 8, 'rank9': 9, 'rank10': 10 }
    const titles = ['冠军', '亚军', '第三名', '第四名', '第五名', '第六名', '第七名', '第八名', '第九名', '第十名']
    const alterCode = `${titles[(pkPos[rowId] || 1) - 1]}${name}`
    if (gameInfo.value.odds[alterCode]) code = alterCode
  }
  const baseOdds = Number(gameInfo.value.odds[code] || 9.95)
  const ratio = ((authStore.userInfo as any)?.rebate ? Number((authStore.userInfo as any).rebate) : 1996) / 1996
  return parseFloat((baseOdds * ratio).toFixed(3))
}

// ── Navigation ────────────────────────────────────────────────────────────────
const goBack = () => router.back()
const toggleHistory = () => {
  showHistory.value = !showHistory.value
  if (showHistory.value && gameInfo.value.history.length === 0) fetchHistory()
}
const clearAll = () => clearSelections(() => { showDetails.value = false })

// ── Bet Confirm & Submit ──────────────────────────────────────────────────────
const isBetting = ref(false)
const betErrorMsg = ref('')
const betSuccessMsg = ref('')

const openConfirm = () => {
  if (totalBets.value === 0) return
  if (totalAmount.value > authStore.userInfo.balance) {
    betErrorMsg.value = '余额不足，请先充值'
    setTimeout(() => betErrorMsg.value = '', 3000)
    return
  }
  showDetails.value = false
  showBetConfirm.value = true
}

const submitBet = async () => {
  if (totalBets.value === 0) return
  showBetConfirm.value = false
  const wanfaParts: string[] = []
  const odds = currentOdds.value

  for (const rowId in selections.value) {
    for (const n of selections.value[rowId] || []) {
      const engine = getLotteryTemplate(gameInfo.value.template)
      if (!engine) continue
      let { code, displayName, wanfaCode } = engine.buildSubmitCode(rowId, n)
      // For pk10, wanfaCode is the actual bet-type (大/小/单/双/龙/虎/数字/冠亚大...)
      // wanfa format backend expects: betType@odds@positionName
      const isPk10 = gameInfo.value.template === 'pk10' || gameInfo.value.template === 'klsf' || gameInfo.value.template === 'kl10f'
      // Fallback: pk10 number/大小单双 are 1.98; generic fallback is 9.95
      const pk10Fallback = 1.98
      const peilv = gameInfo.value.odds[code] || (isPk10 ? pk10Fallback : odds)
      if (isPk10 && wanfaCode) {
        // pk10 strict format: DB_Code@PlayName@Odds@PositionName
        wanfaParts.push(`${code}@${wanfaCode}@${peilv}@${displayName}`)
      } else {
        wanfaParts.push(`${code}@${displayName}@${peilv}`)
      }
    }
  }

  if (!wanfaParts.length) return
  const chip = Number(customAmount.value || selectedChip.value)
  isBetting.value = true
  betErrorMsg.value = ''
  try {
    await submitBetAPI({ gameid: gameId as string, gamename: gameInfo.value.name, qishu: gameInfo.value.currentIssue, wanfa: wanfaParts.join(' | '), money: chip * multiplier.value, unit: moneyUnitName.value })
    betSuccessMsg.value = `🎉 投注成功！共 ${totalBets.value} 注，合计 ¥${totalAmount.value}`
    setTimeout(() => betSuccessMsg.value = '', 3000)
    clearAll()
    authStore.fetchBalance()
  } catch (err: any) {
    betErrorMsg.value = err.message || '投注失败，请重试'
    setTimeout(() => betErrorMsg.value = '', 4000)
  } finally {
    isBetting.value = false
  }
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(startTimer)
onUnmounted(stopTimer)
</script>



<style scoped>
/* 隐藏滚动条但保留功能 */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* 动态赛道背景 */
.race-track-bg {
  background-image: 
    linear-gradient(90deg, transparent 49%, rgba(255,255,255,0.1) 50%, transparent 51%),
    linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.05) 10%, transparent 20%);
  background-size: 100% 100px;
  animation: roadMove 1s linear infinite;
}

/* 赛车号码球样式 (3D 拟物) */
.pk10-ball {
  min-width: 40px;
  padding: 0 6px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-weight: 900;
  font-size: 15px;
  white-space: nowrap;
  font-family: 'Arial', sans-serif;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0,0,0,0.3);
  transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
/* 顶部高光 */
.pk10-ball::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 40%;
  background: linear-gradient(to bottom, rgba(255,255,255,0.3), transparent);
  border-radius: 8px 8px 0 0;
}

.ball-0 { background: linear-gradient(135deg, #444444, #111111); color: #fff; }
.ball-1 { background: linear-gradient(135deg, #FFD700, #B8860B); color: #000; text-shadow: 0 1px 0 rgba(255,255,255,0.4); }
.ball-2 { background: linear-gradient(135deg, #00BFFF, #00008B); color: #fff; }
.ball-3 { background: linear-gradient(135deg, #696969, #000000); color: #fff; }
.ball-4 { background: linear-gradient(135deg, #FF8C00, #8B4500); color: #fff; }
.ball-5 { background: linear-gradient(135deg, #00FFFF, #008B8B); color: #000; text-shadow: 0 1px 0 rgba(255,255,255,0.4); }
.ball-6 { background: linear-gradient(135deg, #4169E1, #000080); color: #fff; }
.ball-7 { background: linear-gradient(135deg, #D3D3D3, #696969); color: #000; text-shadow: 0 1px 0 rgba(255,255,255,0.4); }
.ball-8 { background: linear-gradient(135deg, #FF0000, #8B0000); color: #fff; }
.ball-9 { background: linear-gradient(135deg, #8B0000, #300000); color: #fff; }
.ball-10 { background: linear-gradient(135deg, #32CD32, #006400); color: #fff; }

/* 选中态 */
.selected-ball {
  transform: scale(1.15) translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.5), 0 0 15px var(--glow-color);
  z-index: 10;
  border: 2px solid #fff;
}

/* 倒计时紧张感 */
.countdown-urgent {
  color: #EF4444;
  animation: pulse-urgent 0.5s infinite;
  text-shadow: 0 0 10px rgba(239, 68, 68, 0.6);
}
@keyframes pulse-urgent {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.8; }
  100% { transform: scale(1); opacity: 1; }
}

/* 底部安全区 */
.pb-safe {
  padding-bottom: env(safe-area-inset-bottom);
}

/* 长龙提示 */
.dragon-tip {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #EF4444;
  color: white;
  font-size: 9px;
  padding: 2px 4px;
  border-radius: 4px;
  transform: rotate(15deg);
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  z-index: 5;
}

/* Toast 弹出动效 */
.toast-enter-active, .toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateY(-12px) scale(0.9);
}
</style>