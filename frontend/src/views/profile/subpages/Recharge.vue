<template>
  <div class="flex flex-col h-screen bg-dark-bg text-gray-200">
    <!-- Header -->
    <header class="relative h-12 flex items-center justify-between px-4 z-30 bg-dark-surface border-b border-dark-border">
      <button @click="$router.back()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white">
        <i class="fas fa-arrow-left"></i>
      </button>
      <h1 class="text-white font-bold text-lg">充值</h1>
      <div class="w-8"></div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 space-y-6" v-if="!loading">
      <!-- 充值方式 -->
      <div v-if="channels.length > 0 && !isOrderSubmitted">
        <h2 class="text-sm font-bold text-gray-400 mb-3">选择充值方式</h2>
        <div class="grid grid-cols-2 gap-3">
          <div v-for="channel in channels" :key="channel.type" 
               @click="selectedMethod = channel.type"
               class="bg-dark-card rounded-xl p-3 border cursor-pointer transition-all flex items-center gap-3"
               :class="selectedMethod === channel.type ? 'border-brand-primary shadow-glow-blue' : 'border-dark-border'">
            <i :class="[getChannelIcon(channel.type).icon, 'text-xl', getChannelIcon(channel.type).color]"></i>
            <span class="text-sm text-white font-medium">{{ channel.name }}</span>
          </div>
        </div>
      </div>
      <div v-else class="text-center text-gray-500 py-8">
        暂无可用充值通道
      </div>

      <!-- 收款信息展示 -->
      <div v-if="selectedChannelInfo && (selectedMethod !== 'ali' || isOrderSubmitted)" class="bg-dark-card rounded-xl border border-dark-border p-4">
        <h2 class="text-sm font-bold text-gray-400 mb-3">收款信息</h2>
        <div v-if="selectedMethod === 'bank'" class="text-sm text-gray-300 leading-relaxed whitespace-pre-wrap" v-html="selectedChannelInfo.info"></div>
        <div v-else class="flex flex-col items-center justify-center w-full">
          <!-- USDT 专属金额提示卡片 -->
          <div v-if="selectedMethod === 'ali'" class="text-center w-full mb-4 bg-brand-primary/10 border border-brand-primary/30 rounded-xl p-4 shadow-glow-blue">
            <h3 class="text-sm text-gray-400 mb-1">请向以下地址转账精确金额（包含小数）</h3>
            <div class="text-3xl font-bold text-brand-primary tracking-wider">{{ usdtEquivalent }} <span class="text-lg">USDT</span></div>
            <div class="text-xs text-red-500 mt-2 font-bold"><i class="fas fa-exclamation-triangle mr-1"></i>金额不匹配将导致无法自动到账，损失自负！</div>
          </div>

          <img :src="getAbsoluteUrl(selectedChannelInfo.info)" alt="收款二维码" class="w-48 h-48 object-contain bg-white p-2 rounded-lg mix-blend-screen" />
          <p class="text-xs text-gray-400 mt-2 mb-3">请保存二维码或截图扫码支付</p>
          
          <div v-if="usdtAddress && selectedMethod !== 'bank'" class="w-full bg-dark-bg border border-dark-border rounded-xl p-3 flex flex-col gap-2">
            <div class="text-[10px] text-gray-500 uppercase tracking-wider">USDT 收款地址 (TRC20)</div>
            <div class="flex items-center gap-2 mb-1">
              <span class="text-xs text-brand-primary font-mono break-all flex-1">{{ usdtAddress }}</span>
              <button @click="copyAddress" class="shrink-0 bg-brand-primary/20 text-brand-primary border border-brand-primary/30 px-3 py-1.5 rounded-lg text-xs font-bold active:scale-95 transition-all">复制地址</button>
            </div>
            
            <div v-if="selectedMethod === 'ali' && isOrderSubmitted" class="flex flex-col gap-2 pt-2 border-t border-dark-border">
              <div class="text-[10px] text-gray-500 uppercase tracking-wider">要求转账金额 (USDT)</div>
              <div class="flex items-center gap-2">
                <span class="text-xl text-white font-mono flex-1">{{ usdtEquivalent }}</span>
                <button @click="fallbackCopy(usdtEquivalent)" class="shrink-0 bg-[#09B83E]/20 text-[#09B83E] border border-[#09B83E]/30 px-3 py-1.5 rounded-lg text-xs font-bold active:scale-95 transition-all">复制金额</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 充值金额 -->
      <div v-if="channels.length > 0 && !isOrderSubmitted">
        <h2 class="text-sm font-bold text-gray-400 mb-3 flex justify-between">
          <span>充值金额 (元)</span>
          <span class="text-xs text-brand-primary">最低: ¥{{ minPay }}</span>
        </h2>
        <div class="bg-dark-card rounded-xl border border-dark-border p-4 flex items-center gap-3">
          <span class="text-2xl font-bold text-white">¥</span>
          <input type="number" v-model="amount" :placeholder="`最低充值 ${minPay} 元`" 
                 class="bg-transparent border-none outline-none text-2xl font-bold text-white w-full">
        </div>
        <div class="grid grid-cols-4 gap-2 mt-3">
          <button v-for="val in [100, 500, 1000, 5000]" :key="val"
                  @click="amount = val"
                  class="py-2 rounded-lg text-sm font-medium border transition-colors"
                  :class="amount === val ? 'bg-brand-primary/20 border-brand-primary text-brand-primary' : 'bg-dark-surface border-dark-border text-gray-400 hover:text-white'">
            {{ val }}
          </button>
        </div>
      </div>

      <!-- USDT 换算提示（仅数字货币通道时显示） -->
      <div v-if="selectedMethod === 'ali' && amount && usdtRate > 0 && !isOrderSubmitted"
           class="bg-brand-primary/10 border border-brand-primary/30 rounded-xl px-4 py-3 flex items-center justify-between">
        <div class="text-xs text-gray-400">
          <i class="fab fa-bitcoin text-brand-primary mr-1" />
          汇率: <b class="text-white">1 USDT ≈ ¥{{ usdtRate.toFixed(2) }}</b>
        </div>
        <div class="text-right">
          <div class="text-sm font-bold text-brand-primary">≈ {{ usdtEquivalent }} USDT</div>
          <div class="text-[10px] text-gray-500">实际到账金额</div>
        </div>
      </div>

      <button v-if="channels.length > 0 && !isOrderSubmitted"
              @click="submit" :disabled="isSubmitting"
              class="w-full py-3.5 rounded-xl bg-gradient-to-r from-brand-primary to-blue-600 text-white font-bold text-lg shadow-glow-blue active:scale-95 transition-transform mt-8 disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center">
        <span v-if="!isSubmitting">{{ selectedMethod === 'ali' ? '生成充值订单' : '提交充值已付款' }}</span>
        <span v-else><i class="fas fa-spinner fa-spin mr-2"></i>提交中...</span>
      </button>

      <button v-if="isOrderSubmitted"
              @click="$router.push('/profile/transactions')" 
              class="w-full py-3.5 rounded-xl bg-gradient-to-r from-dark-surface to-dark-card border border-dark-border text-white font-bold text-lg active:scale-95 transition-transform mt-4 flex justify-center items-center">
        我已完成支付
      </button>
    </div>
    
    <div v-else class="flex-1 flex items-center justify-center">
      <i class="fas fa-spinner fa-spin text-3xl text-brand-primary"></i>
    </div>

    <!-- 弹窗提示 -->
    <transition name="toast">
      <div v-if="errorMsg" class="fixed top-20 left-0 right-0 mx-auto w-max max-w-xs z-[100] px-5 py-3 rounded-2xl bg-red-500/90 backdrop-blur text-white text-sm font-bold shadow-lg text-center">
        {{ errorMsg }}
      </div>
    </transition>
    <transition name="toast">
      <div v-if="successMsg" class="fixed top-20 left-0 right-0 mx-auto w-max max-w-xs z-[100] px-5 py-3 rounded-2xl bg-green-500/90 backdrop-blur text-white text-sm font-bold shadow-lg text-center">
        {{ successMsg }}
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { getRechargeChannels, submitRecharge } from '@/api/finance'

const router = useRouter()

const loading = ref(true)
const isSubmitting = ref(false)
const isOrderSubmitted = ref(false)
const channels = ref<any[]>([])
const minPay = ref(10)

const selectedMethod = ref('')
const amount = ref<number | ''>('')
const errorMsg = ref('')
const successMsg = ref('')
const usdtRate = ref(0)     // 1 USDT = N 元
const usdtAddress = ref('') // 平台 USDT 收款地址

const showError = (msg: string) => {
  errorMsg.value = msg
  setTimeout(() => errorMsg.value = '', 3000)
}

const showSuccess = (msg: string) => {
  successMsg.value = msg
  setTimeout(() => successMsg.value = '', 3000)
}

const fetchChannels = async () => {
  loading.value = true
  try {
    const res: any = await getRechargeChannels()
    if (res && res.channels) {
      channels.value = res.channels.filter((c: any) => c.type !== 'wx' && c.type !== 'bank')
      minPay.value = res.min_pay || 10
      usdtRate.value = Number(res.usdt_rate) || 0
      usdtAddress.value = res.usdt_address || ''
      if (channels.value.length > 0) {
        selectedMethod.value = channels.value[0].type
      }
    }
  } catch (error) {
    console.error('Failed to fetch recharge channels:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchChannels()
})

const getChannelIcon = (type: string) => {
  const map: Record<string, any> = {
    'wx': { icon: 'fab fa-weixin', color: 'text-[#09B83E]' },
    'ali': { icon: 'fab fa-bitcoin', color: 'text-[#1677FF]' }, // 后端ali可能代表数字货币
    'bank': { icon: 'fas fa-university', color: 'text-brand-accent' }
  }
  return map[type] || { icon: 'fas fa-wallet', color: 'text-gray-400' }
}

const selectedChannelInfo = computed(() => {
  return channels.value.find(c => c.type === selectedMethod.value) || null
})

// USDT 换算：用户输入人民币 ÷ 汇率 = USDT
const usdtEquivalent = computed(() => {
  if (!amount.value || !usdtRate.value) return '0.000000'
  return (Number(amount.value) / usdtRate.value).toFixed(6)
})

const getAbsoluteUrl = (path: string) => {
  if (path.startsWith('http')) return path;
  return '/' + path; // e.g. /uppic/ewm/xxx.jpg
}

const copyAddress = () => {
  if (!usdtAddress.value) return
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(usdtAddress.value).then(() => {
      showSuccess('地址已复制到剪贴板')
    }).catch(() => fallbackCopy(usdtAddress.value))
  } else {
    fallbackCopy(usdtAddress.value)
  }
}

const fallbackCopy = (text: string) => {
  const textArea = document.createElement("textarea")
  textArea.value = text
  document.body.appendChild(textArea)
  textArea.select()
  try {
    document.execCommand('copy')
    showSuccess('地址已复制到剪贴板')
  } catch (err) {
    showError('该浏览器不支持一键复制，请长按地址手动复制')
  }
  document.body.removeChild(textArea)
}

const submit = async () => {
  if (!amount.value || amount.value < minPay.value) {
    showError(`金额不能低于最低限额 ¥${minPay.value}`)
    return
  }
  
  isSubmitting.value = true
  try {
    let finalMoney = Number(amount.value)
    
    // 如果是USDT通道获取唯一订单尾数
    if (selectedMethod.value === 'ali' && !isOrderSubmitted.value) {
      const randomAdd = Math.floor(Math.random() * 11 + 1) / 100 // 产生 0.01 - 0.11
      finalMoney = Number((finalMoney + randomAdd).toFixed(2))
      amount.value = finalMoney
    }

    await submitRecharge({
      pay_type: selectedMethod.value,
      money: finalMoney,
      comment: selectedMethod.value === 'ali' ? 'USDT充值自动生成尾数' : '会员充值'
    })
    
    if (selectedMethod.value === 'ali') {
      isOrderSubmitted.value = true
      showSuccess('订单已创建，请按照显示的精确金额转账！')
    } else {
      showSuccess('提交成功，请等待财务审核！')
      setTimeout(() => {
        router.push('/profile/transactions') // 跳往账单记录
      }, 1500)
    }
  } catch (error: any) {
    showError(error.message || '充值提交失败')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.toast-enter-active, .toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from, .toast-leave-to {
  opacity: 0;
  transform: translateY(-12px) scale(0.9);
}
</style>