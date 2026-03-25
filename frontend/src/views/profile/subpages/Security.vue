<template>
  <div class="flex flex-col h-screen bg-dark-bg text-gray-200">
    <!-- Header -->
    <header class="relative h-12 flex items-center justify-between px-4 z-30 bg-dark-surface border-b border-dark-border">
      <button @click="activeForm ? (activeForm = null) : $router.push('/profile')" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white">
        <i class="fas fa-arrow-left"></i>
      </button>
      <h1 class="text-white font-bold text-lg">安全中心</h1>
      <div class="w-8"></div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 space-y-6">
      <!-- 列表模式 -->
      <div v-if="!activeForm" class="bg-dark-card rounded-xl border border-dark-border overflow-hidden">
        <div v-for="item in securityItems" :key="item.id" 
             class="flex items-center justify-between p-4 border-b border-dark-border last:border-0 hover:bg-white/5 cursor-pointer transition-colors"
             @click="openForm(item)">
          <div class="flex items-center gap-3">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-white', item.color]">
              <i :class="item.icon"></i>
            </div>
            <div>
              <div class="text-sm font-bold text-white">{{ item.title }}</div>
              <div class="text-xs text-gray-500 mt-0.5">{{ item.desc }}</div>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <span v-if="item.status" :class="['text-xs', item.status === '已绑定' || item.status === '已设置' ? 'text-brand-success' : 'text-orange-500']">{{ item.status }}</span>
            <i class="fas fa-chevron-right text-gray-600 text-xs"></i>
          </div>
        </div>
      </div>

      <!-- 修改表单模式 -->
      <div v-else class="space-y-4">
        <div class="bg-dark-card rounded-2xl p-6 border border-dark-border shadow-xl">
          <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
            <i :class="[activeItem?.icon, 'text-brand-primary']"></i>
            {{ activeItem?.title }}
          </h2>
          
          <form @submit.prevent="handleSubmit" class="space-y-5">
            
            <!-- 修改登录/资金密码 -->
            <template v-if="activeForm === 'login_pwd' || activeForm === 'fund_pwd'">
              <div v-if="!isNewFundPwd">
                <label class="block text-xs font-medium text-gray-400 mb-2">旧密码</label>
                <input 
                  v-model="passwordForm.oldpassword"
                  type="password" 
                  class="w-full bg-dark-bg border border-dark-border rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-primary transition-all placeholder-gray-600"
                  placeholder="请输入原密码"
                  required
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">新密码</label>
                <input 
                  v-model="passwordForm.newpassword"
                  type="password" 
                  class="w-full bg-dark-bg border border-dark-border rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-primary transition-all placeholder-gray-600"
                  placeholder="含大写英文字母、数字和特殊符号"
                  required
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">确认新密码</label>
                <input 
                  v-model="passwordForm.confirmPassword"
                  type="password" 
                  class="w-full bg-dark-bg border border-dark-border rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-primary transition-all placeholder-gray-600"
                  placeholder="请再次确认新密码"
                  required
                />
              </div>
            </template>

            <!-- 绑定真实姓名 -->
            <template v-if="activeForm === 'real_name'">
              <div class="text-xs text-orange-400 mb-4 bg-orange-500/10 p-3 rounded-lg border border-orange-500/20">
                <i class="fas fa-info-circle mr-1"></i> 为了资金安全，真实姓名一旦绑定将无法自行修改，请确保与提现账户姓名一致。
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-400 mb-2">真实姓名</label>
                <input 
                  v-model="infoForm.name"
                  type="text" 
                  class="w-full bg-dark-bg border border-dark-border rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-primary transition-all placeholder-gray-600"
                  placeholder="请输入真实姓名"
                  required
                />
              </div>
            </template>

            <!-- 绑定USDT -->
            <template v-if="activeForm === 'bind_usdt'">
              <div class="text-xs text-orange-400 mb-4 bg-orange-500/10 p-3 rounded-lg border border-orange-500/20">
                <i class="fas fa-info-circle mr-1"></i> USDT 提现地址一旦绑定无法自行修改，只能新增（最多5个）。
              </div>
              <div v-if="!userProfile?.name">
                <label class="block text-xs font-medium text-gray-400 mb-2 border border-red-500/50 p-3 rounded-lg text-red-400">请先返回绑定真实姓名</label>
              </div>
              <template v-else>
                <!-- 已有的 USDT 列表 -->
                <div v-if="userProfile?.alipay" class="mb-6 space-y-2">
                  <label class="block text-xs font-medium text-gray-400 mb-2">已绑定的 USDT 地址</label>
                  <div v-for="(addr, idx) in userProfile.alipay.split(',')" :key="idx" class="bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm text-gray-300 break-all flex items-center">
                    <i class="fab fa-bitcoin text-brand-primary mr-3"></i> {{ addr }}
                  </div>
                </div>

                <!-- 新增输入框 -->
                <div v-if="!userProfile?.alipay || userProfile.alipay.split(',').length < 5">
                  <label class="block text-xs font-medium text-gray-400 mb-2">新增 USDT 地址 (TRC20)</label>
                  <input 
                    v-model="infoForm.alipay"
                    type="text" 
                    class="w-full bg-dark-bg border border-dark-border rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-primary transition-all placeholder-gray-600"
                    placeholder="请输入新的 USDT 提现地址"
                  />
                </div>
              </template>
            </template>

            <!-- 绑定银行卡 -->
            <template v-if="activeForm === 'bind_bank'">
              <div class="text-xs text-orange-400 mb-4 bg-orange-500/10 p-3 rounded-lg border border-orange-500/20">
                <i class="fas fa-info-circle mr-1"></i> 银行卡信息一旦绑定无法自行修改，请仔细核对。
              </div>
              <div v-if="!userProfile?.name">
                <label class="block text-xs font-medium text-gray-400 mb-2 border border-red-500/50 p-3 rounded-lg text-red-400">请先返回绑定真实姓名</label>
              </div>
              <template v-else>
                <div>
                  <label class="block text-xs font-medium text-gray-400 mb-2">开户银行</label>
                  <input 
                    v-model="infoForm.bank"
                    type="text" 
                    class="w-full bg-dark-bg border border-dark-border rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-primary transition-all placeholder-gray-600"
                    placeholder="如：工商银行、建设银行"
                    required
                  />
                </div>
                <div>
                  <label class="block text-xs font-medium text-gray-400 mb-2">银行卡号</label>
                  <input 
                    v-model="infoForm.card"
                    type="text" 
                    class="w-full bg-dark-bg border border-dark-border rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-primary transition-all placeholder-gray-600"
                    placeholder="请输入银行卡号"
                    required
                  />
                </div>
              </template>
            </template>

            <div v-if="errorMsg" class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs text-center">
              {{ errorMsg }}
            </div>

            <div class="flex gap-3 pt-4">
              <button type="button" @click="activeForm = null" class="flex-1 py-3 rounded-xl bg-white/5 border border-dark-border text-gray-400 font-medium active:scale-95 transition-all">
                取消
              </button>
              <button type="submit" :disabled="loading || (['bind_usdt','bind_bank'].includes(activeForm as string) && !userProfile?.name) || (activeForm === 'bind_usdt' && !infoForm.alipay)" class="flex-2 py-3 px-8 rounded-xl bg-gradient-to-r from-brand-primary to-brand-secondary text-white font-bold shadow-lg active:scale-95 transition-all disabled:opacity-50">
                {{ loading ? '保存中...' : '提交绑定' }}
              </button>
            </div>
          </form>
        </div>
      </div>
      
      <div v-if="!activeForm" class="text-center text-xs text-gray-600 mt-8">
        为了您的资金安全，请勿泄露任何密码和验证码给他人
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { updatePassword, updateFundPassword, getUserProfile, updateUserInfo } from '@/api/user'

const router = useRouter()
const activeForm = ref<string | null>(null)
const loading = ref(false)
const errorMsg = ref('')

const userProfile = ref<any>(null)

const securityItems = ref([
  { id: 'login_pwd', title: '登录密码', desc: '定期修改密码保护账号安全', icon: 'fas fa-lock', color: 'bg-blue-500', status: '已设置' },
  { id: 'fund_pwd', title: '资金密码', desc: '提现时需验证资金密码', icon: 'fas fa-key', color: 'bg-orange-500', status: '检查中...' },
  { id: 'real_name', title: '真实姓名', desc: '提现账户必须与此姓名一致', icon: 'fas fa-id-card', color: 'bg-purple-500', status: '检查中...' },
  { id: 'bind_usdt', title: 'U地址绑定管理', desc: '绑定USDT(TRC20)数字货币提款地址', icon: 'fab fa-bitcoin', color: 'bg-blue-500', status: '检查中...' },
  { id: 'bind_bank', title: '银行卡管理', desc: '绑定银行卡用于提现', icon: 'fas fa-credit-card', color: 'bg-green-500', status: '检查中...' },
])

const activeItem = computed(() => securityItems.value.find(i => i.id === activeForm.value))

const passwordForm = ref({
  oldpassword: '',
  newpassword: '',
  confirmPassword: ''
})

const infoForm = ref({
  name: '',
  bank: '',
  card: '',
  alipay: ''
})

// 若 has_fund_pwd 为 false 或 undefined（从未设置），则视为新设密码
const isNewFundPwd = computed(() => {
  return activeForm.value === 'fund_pwd' && !userProfile.value?.has_fund_pwd
})

const fetchData = async () => {
  try {
    // Interceptor returns res.data directly if it exists
    const res: any = await getUserProfile()
    const userData = res.uid ? res : (res.data || res)
    
    if (userData && userData.uid) {
      userProfile.value = userData
      
      // Update statuses
      const updateStatus = (id: string, isBound: boolean) => {
        const item = securityItems.value.find(i => i.id === id)
        if (item) {
          item.status = isBound ? (id.includes('pwd') ? '已设置' : '已绑定') : (id.includes('pwd') ? '未设置' : '待设置')
        }
      }

      updateStatus('fund_pwd', userData.has_fund_pwd)
      updateStatus('real_name', !!userData.name)
      updateStatus('bind_usdt', !!userData.alipay)
      updateStatus('bind_bank', !!(userData.bank && userData.card))
    }
  } catch (err) {
    console.error('获取资料失败', err)
  }
}

const openForm = (item: any) => {
  if (item.status === '已绑定') {
    if (item.id === 'bind_usdt') {
      // 允许进入查看或追加
    } else {
      alert(`${item.title}已完成绑定，保障资金安全无法自行修改。如需修改请联系在线客服！`)
      return
    }
  }
  
  activeForm.value = item.id
  errorMsg.value = ''
  passwordForm.value = { oldpassword: '', newpassword: '', confirmPassword: '' }
  infoForm.value = { name: '', bank: '', card: '', alipay: '' }
}

const handleSubmit = async () => {
  if (['login_pwd', 'fund_pwd'].includes(activeForm.value as string)) {
    if (passwordForm.value.newpassword !== passwordForm.value.confirmPassword) {
      errorMsg.value = '两次输入的新密码不一致'
      return
    }

    // 强验证规则：登录密码和资金密码都需要至少1个大写、1个数字、1个特殊符号
    const pwdRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/
    if (!pwdRegex.test(passwordForm.value.newpassword)) {
      errorMsg.value = '新密码必须包含至少一个大写英文字母、一个数字和一个特殊符号'
      return
    }
  }

  loading.value = true
  errorMsg.value = ''

  try {
    let res: any
    
    if (activeForm.value === 'login_pwd') {
      res = await updatePassword(passwordForm.value)
    } else if (activeForm.value === 'fund_pwd') {
      // 新设资金密码：只发 newpassword；修改时才发 oldpassword
      const fundPayload: any = { newpassword: passwordForm.value.newpassword }
      if (!isNewFundPwd.value) {
        fundPayload.oldpassword = passwordForm.value.oldpassword
      }
      res = await updateFundPassword(fundPayload)
    } else if (['real_name', 'bind_usdt', 'bind_bank'].includes(activeForm.value as string)) {
      const payload: any = {}
      if (activeForm.value === 'real_name') payload.name = infoForm.value.name
      if (activeForm.value === 'bind_usdt') {
        if (!infoForm.value.alipay && userProfile.value?.alipay?.split(',').length >= 5) {
           alert("已达到允许绑定的最大数量"); return;
        }
        payload.alipay = infoForm.value.alipay
      }
      if (activeForm.value === 'bind_bank') {
        payload.bank = infoForm.value.bank
        payload.card = infoForm.value.card
      }
      res = await updateUserInfo(payload)
    }

    // Since the Axios interceptor rejects any non-200 codes, reaching here implies success.
    // However, if the wrapper is preserved, `res.code === 200` is true. If stripped, `res.code` is undefined.
    if (res.code === 200 || res.code === undefined || !res.code) {
      alert(res.message || res.msg || '操作成功')
      if (activeForm.value === 'login_pwd') {
        router.push('/login')
      } else {
        activeForm.value = null
        fetchData()
      }
    } else {
      errorMsg.value = res.message || res.msg || '操作失败'
    }
  } catch (err: any) {
    errorMsg.value = err.message || '请求失败，请稍后重试'
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchData())
</script>