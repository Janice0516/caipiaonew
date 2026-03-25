<template>
  <div class="flex flex-col h-screen bg-dark-bg text-gray-200">
    <header class="relative h-12 flex items-center justify-between px-4 z-30 bg-dark-surface border-b border-dark-border">
      <button @click="$router.push('/profile')" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white">
        <i class="fas fa-arrow-left"></i>
      </button>
      <h1 class="text-white font-bold text-lg">提现</h1>
      <div class="w-8"></div>
    </header>

    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <i class="fas fa-circle-notch fa-spin text-3xl text-brand-primary"></i>
    </div>

    <div v-else class="flex-1 overflow-y-auto p-4 space-y-6">
      <div class="bg-dark-card rounded-xl border border-dark-border p-5 text-center">
        <p class="text-sm text-gray-400 mb-1">可提现余额 (元)</p>
        <h2 class="text-3xl font-mono font-bold text-white">¥ {{ authStore.userInfo.balance.toFixed(2) }}</h2>
      </div>

      <div v-if="!userProfile?.has_fund_pwd" class="bg-orange-500/10 border border-orange-500/20 rounded-xl p-4 text-center">
        <p class="text-sm text-orange-400 mb-3">您尚未设置资金密码，无法提现</p>
        <button @click="$router.push('/security')" class="px-6 py-2 bg-orange-500 text-white rounded-lg text-sm font-bold active:scale-95">去设置</button>
      </div>

      <div v-else>
        <!-- 提现方式选择 -->
        <h2 class="text-sm font-bold text-gray-400 mb-3">选择提现方式</h2>
        <div class="grid grid-cols-2 gap-3 mb-6">
          <div @click="accountType = 2" 
               :class="['p-4 rounded-xl border cursor-pointer transition-all text-center', accountType === 2 ? 'bg-brand-primary/10 border-brand-primary shadow-glow-blue' : 'bg-dark-card border-dark-border hover:bg-white/5']">
            <i class="fab fa-bitcoin text-2xl mb-2" :class="accountType === 2 ? 'text-brand-primary' : 'text-gray-500'"></i>
            <div class="text-sm font-bold" :class="accountType === 2 ? 'text-brand-primary' : 'text-gray-300'">USDT数字货币</div>
          </div>
          <div @click="accountType = 1" 
               :class="['p-4 rounded-xl border cursor-pointer transition-all text-center', accountType === 1 ? 'bg-brand-primary/10 border-brand-primary shadow-glow-blue' : 'bg-dark-card border-dark-border hover:bg-white/5']">
            <i class="fas fa-university text-2xl mb-2" :class="accountType === 1 ? 'text-brand-primary' : 'text-gray-500'"></i>
            <div class="text-sm font-bold" :class="accountType === 1 ? 'text-brand-primary' : 'text-gray-300'">银联银行卡</div>
          </div>
        </div>

        <!-- 对应账户信息显示 -->
        <template v-if="accountType === 2">
          <div v-if="!userProfile?.alipay" class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-center mb-6">
            <p class="text-sm text-red-400 mb-3">您尚未绑定USDT提现地址</p>
            <button @click="$router.push('/security')" class="px-6 py-2 bg-red-500 text-white rounded-lg text-sm font-bold active:scale-95">去绑定</button>
          </div>
          <div v-else class="mb-6 space-y-3">
            <div class="text-xs text-gray-400 mb-1">请选择提款地址 (真实姓名: {{ userProfile.name }})</div>
            <div v-for="(addr, idx) in userProfile.alipay.split(',')" :key="idx"
                 @click="selectedUsdt = addr"
                 :class="['p-4 rounded-xl border cursor-pointer transition-all break-all text-sm flex items-center', selectedUsdt === addr ? 'bg-brand-primary/10 border-brand-primary shadow-glow-blue' : 'bg-dark-surface border-dark-border hover:bg-white/5']">
              <i class="fab fa-bitcoin text-xl mr-3" :class="selectedUsdt === addr ? 'text-brand-primary' : 'text-gray-500'"></i>
              <span class="flex-1" :class="selectedUsdt === addr ? 'text-white font-bold' : 'text-gray-300'">{{ addr }}</span>
              <i v-if="selectedUsdt === addr" class="fas fa-check-circle text-brand-primary ml-2"></i>
            </div>
          </div>
        </template>

        <template v-if="accountType === 1">
          <div v-if="!userProfile?.bank || !userProfile?.card" class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-center mb-6">
            <p class="text-sm text-red-400 mb-3">您尚未绑定储蓄卡信息</p>
            <button @click="$router.push('/security')" class="px-6 py-2 bg-red-500 text-white rounded-lg text-sm font-bold active:scale-95">去绑定</button>
          </div>
          <div v-else class="bg-dark-surface rounded-xl border border-dark-border p-4 flex flex-col gap-1 mb-6">
            <div class="text-xs text-gray-400">真实姓名: {{ userProfile.name }}</div>
            <div class="text-sm font-bold text-white">{{ userProfile.bank }} (尾号 {{ userProfile.card.slice(-4) }})</div>
          </div>
        </template>

        <!-- 提现表单 -->
        <template v-if="(accountType === 2 && userProfile?.alipay) || (accountType === 1 && userProfile?.bank)">
          <div>
            <h2 class="text-sm font-bold text-gray-400 mb-3">提现金额</h2>
            <div class="bg-dark-card rounded-xl border border-dark-border p-4 mb-6">
              <div class="flex items-center gap-3 border-b border-dark-border pb-3 mb-3">
                <span class="text-2xl font-bold text-white">¥</span>
                <input type="number" v-model="amount" placeholder="请输入提现金额" 
                       class="bg-transparent border-none outline-none text-2xl font-bold text-white w-full">
              </div>
              <div class="flex justify-between items-center text-sm">
                <span class="text-gray-500">单笔限额视系统而定</span>
                <button @click="amount = authStore.userInfo.balance" class="text-brand-primary font-medium">全部提现</button>
              </div>
            </div>
          </div>

          <!-- USDT 换算提示（数字货币提现时显示） -->
          <div v-if="accountType === 2 && amount && usdtRate > 0"
               class="bg-brand-primary/10 border border-brand-primary/30 rounded-xl px-4 py-3 flex items-center justify-between mb-1">
            <div class="text-xs text-gray-400">
              <i class="fab fa-bitcoin text-brand-primary mr-1" />
              汇率: <b class="text-white">1 USDT ≈ ¥{{ usdtRate.toFixed(2) }}</b>
            </div>
            <div class="text-right">
              <div class="text-sm font-bold text-brand-primary">≈ {{ usdtEquivalent }} USDT</div>
              <div class="text-[10px] text-gray-500">实际收到加密货币</div>
            </div>
          </div>

          <div>
            <h2 class="text-sm font-bold text-gray-400 mb-3">资金密码</h2>
            <div class="bg-dark-card rounded-xl border border-dark-border p-4 mb-6 flex items-center">
              <i class="fas fa-lock text-gray-500 mr-3"></i>
              <input type="password" v-model="moneyPassword" placeholder="请输入6位及以上资金密码" 
                     class="bg-transparent border-none outline-none text-white w-full">
            </div>
          </div>

          <button @click="submit" 
                  class="w-full py-3.5 rounded-xl bg-gradient-to-r from-brand-primary to-blue-600 text-white font-bold text-lg shadow-glow-blue active:scale-95 transition-transform disabled:opacity-50"
                  :disabled="isSubmitting || !amount || amount <= 0 || !moneyPassword">
            {{ isSubmitting ? '提交中...' : '确认提现' }}
          </button>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../../store/auth'
import { getUserProfile } from '@/api/user'
import { submitWithdraw, getRechargeChannels } from '@/api/finance'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(true)
const isSubmitting = ref(false)
const userProfile = ref<any>(null)

// 默认 2代表数字货币, 1代表银行卡
const accountType = ref(2) 
const amount = ref<number | ''>('')
const moneyPassword = ref('')
const selectedUsdt = ref('')
const usdtRate = ref(0)  // 1 USDT = N 元

// USDT 换算: 用户提现人民币 ÷ 汇率 = USDT
const usdtEquivalent = computed(() => {
  if (!amount.value || !usdtRate.value) return '0.000000'
  return (Number(amount.value) / usdtRate.value).toFixed(6)
})

const fetchData = async () => {
  loading.value = true
  try {
    // 同时获取用户资料和 USDT 汇率
    const [profileRes, channelRes]: any[] = await Promise.all([
      getUserProfile(),
      getRechargeChannels()
    ])
    // 拦截器已剥一层，res 本身可能就是 user 对象
    const userData = profileRes?.uid ? profileRes : (profileRes?.data || profileRes)
    if (userData && userData.uid) {
      userProfile.value = userData
      if (userData.alipay) {
        selectedUsdt.value = userData.alipay.split(',')[0]
      }
    }
    if (channelRes?.usdt_withdraw_rate) {
      usdtRate.value = Number(channelRes.usdt_withdraw_rate) || 0
    } else if (channelRes?.usdt_rate) {
      usdtRate.value = Number(channelRes.usdt_rate) || 0
    }
  } catch (error) {
    console.error('获取用户信息失败:', error)
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  if (!amount.value || amount.value <= 0) {
    alert('请输入正确的提现金额')
    return
  }
  if (!moneyPassword.value) {
    alert('请输入资金密码')
    return
  }
  
  isSubmitting.value = true
  try {
    const payload: any = {
      money: Number(amount.value),
      account_type: accountType.value,
      money_password: moneyPassword.value,
      comment: '用户前台发起提现' // Comment is optional but explicitly sent 
    }
    
    if (accountType.value === 2) {
      payload.target_address = selectedUsdt.value
    }
    
    const res: any = await submitWithdraw(payload)

    if (res.code === 200) {
      alert('提现申请提交成功，请等待系统划拨审批！')
      authStore.fetchBalance() // 刷新余额展示
      router.replace('/transactions')
    } else {
      alert(res.message || res.msg || '提现失败')
    }
  } catch (error: any) {
    alert(error.message || '请求失败，请检查网络或联系客服状态')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>