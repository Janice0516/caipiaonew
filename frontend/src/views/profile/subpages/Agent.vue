<template>
  <div class="flex flex-col h-screen bg-dark-bg text-gray-200">
    <!-- Header -->
    <header class="relative h-12 flex items-center justify-between px-4 z-30 bg-dark-surface border-b border-dark-border">
      <button @click="$router.back()" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white">
        <i class="fas fa-arrow-left"></i>
      </button>
      <h1 class="text-white font-bold text-lg">代理中心</h1>
      <div class="w-8"></div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 space-y-6">

      <!-- 数据概览 -->
      <div class="grid grid-cols-2 gap-3">
        <div class="bg-dark-card rounded-xl p-4 border border-dark-border text-center">
          <p class="text-xs text-gray-500 mb-1">本月佣金</p>
          <h3 class="text-xl font-bold text-brand-success">¥ {{ summary.commission_month.toFixed(2) }}</h3>
        </div>
        <div class="bg-dark-card rounded-xl p-4 border border-dark-border text-center">
          <p class="text-xs text-gray-500 mb-1">团队人数</p>
          <h3 class="text-xl font-bold text-white">{{ summary.team_total }} 人</h3>
        </div>
      </div>

      <!-- 选项卡与列表区 -->
      <div class="flex flex-col flex-1 pb-6">
        <div class="flex p-1 bg-dark-surface rounded-xl mb-4 border border-dark-border/50">
          <button 
            @click="activeTab = 'links'" 
            :class="['flex-1 py-2 text-sm font-bold rounded-lg transition-all', activeTab === 'links' ? 'bg-brand-primary text-white shadow' : 'text-gray-400 hover:text-gray-200']">
            推广链接
          </button>
          <button 
            @click="activeTab = 'members'" 
            :class="['flex-1 py-2 text-sm font-bold rounded-lg transition-all', activeTab === 'members' ? 'bg-brand-primary text-white shadow' : 'text-gray-400 hover:text-gray-200']">
            团队成员
          </button>
          <button 
            @click="activeTab = 'commission'" 
            :class="['flex-1 py-2 text-sm font-bold rounded-lg transition-all', activeTab === 'commission' ? 'bg-brand-primary text-white shadow' : 'text-gray-400 hover:text-gray-200']">
            佣金明细
          </button>
        </div>

        <!-- 团队成员列表 -->
        <div v-if="activeTab === 'members'" class="bg-dark-card rounded-xl border border-dark-border overflow-hidden">
          <div v-if="teamMembers.length === 0" class="p-6 text-center text-gray-500 text-sm">
            暂无团队成员
          </div>
          <div v-else>
            <div v-for="user in teamMembers" :key="user.id" 
                 @click="showMemberDetail(user.id)"
                 class="flex items-center justify-between p-3 border-b border-dark-border last:border-0 hover:bg-white/5 transition-colors cursor-pointer active:bg-white/10">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-primary/60 to-blue-600/60 flex items-center justify-center text-xs text-white font-bold">
                  {{ user.name[0] }}
                </div>
                <div>
                  <div class="text-sm text-gray-300">{{ user.name }}</div>
                  <div class="text-[10px] text-gray-500">{{ user.joinTime }}</div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs text-brand-primary">贡献 ¥ {{ user.contribution.toFixed(2) }}</span>
                <i class="fas fa-chevron-right text-xs text-gray-600"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 佣金明细列表 -->
        <div v-if="activeTab === 'commission'" class="bg-dark-card rounded-xl border border-dark-border overflow-hidden">
          <div v-if="commissionLoading" class="p-6 text-center text-gray-500">
            <i class="fas fa-spinner fa-spin text-xl text-brand-primary"></i>
          </div>
          <div v-else-if="commissionList.length === 0" class="p-6 text-center text-gray-500 text-sm">
            暂无明细记录
          </div>
          <div v-else>
            <div v-for="item in commissionList" :key="item.id" 
                 class="p-4 border-b border-dark-border last:border-0 hover:bg-white/5 transition-colors flex justify-between items-center">
              <div>
                <div class="text-sm text-gray-200 font-medium mb-1">{{ item.comment || '代理佣金' }}</div>
                <div class="text-xs text-gray-500">{{ formatTime(item.addtime) }}</div>
              </div>
              <div class="text-right">
                <div class="text-sm font-bold text-brand-success">+¥{{ Number(item.money).toFixed(2) }}</div>
                <div class="text-xs text-gray-500 mt-0.5">余额: ¥{{ Number(item.countmoney).toFixed(2) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- 推广链接列表 -->
        <div v-if="activeTab === 'links'" class="space-y-4">
          <!-- 创建链接卡片 -->
          <div class="bg-dark-card rounded-xl p-4 border border-dark-border">
            <div class="flex flex-col space-y-3">
              <label class="text-sm font-bold text-gray-200">生成新推广链接</label>
              <div class="flex gap-2">
                <select v-model.number="newLinkRebate" class="flex-1 bg-dark-bg border border-dark-border rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-primary transition-colors text-sm">
                  <option disabled value="">请选择下级奖金组</option>
                  <option v-for="opt in rebateOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
                <button @click="generateInviteLink" :disabled="!newLinkRebate || generatingLink" class="px-4 py-2 rounded-lg bg-brand-primary text-white font-bold text-sm shadow-glow-blue active:scale-95 transition-transform disabled:opacity-50">
                  <span v-if="generatingLink"><i class="fas fa-spinner fa-spin"></i></span>
                  <span v-else>生成链接</span>
                </button>
              </div>
            </div>
          </div>

          <!-- 链接列表 -->
          <div class="bg-dark-card rounded-xl border border-dark-border overflow-hidden">
            <div v-if="linksLoading" class="p-6 text-center text-gray-500">
              <i class="fas fa-spinner fa-spin text-xl text-brand-primary"></i>
            </div>
            <div v-else-if="inviteLinkList.length === 0" class="p-6 text-center text-gray-500 text-sm">
              暂无专属推广链接
            </div>
            <div v-else>
              <div v-for="link in inviteLinkList" :key="link.id" class="p-4 border-b border-dark-border last:border-0 hover:bg-white/5 transition-colors flex flex-col gap-2">
                <div class="flex justify-between items-start">
                  <div>
                    <div class="text-lg font-mono font-bold text-white tracking-widest bg-dark-bg px-2 py-0.5 rounded">{{ link.invite_code }}</div>
                    <div class="text-xs text-brand-warning mt-2">预设奖金组: <span class="font-bold text-white">{{ link.rebate }}</span></div>
                  </div>
                  <div class="text-xs text-gray-500">{{ formatTime(link.addtime) }}</div>
                </div>
                <div class="flex gap-2 mt-2">
                  <button @click="copyCustomLink(link.invite_code)" class="flex-1 py-1.5 rounded bg-white/10 text-brand-primary text-xs font-bold active:bg-white/20 transition-colors">
                    复制专属链接
                  </button>
                  <button @click="removeInviteLink(link.id)" class="px-3 py-1.5 rounded bg-brand-danger/10 text-brand-danger text-xs font-bold active:bg-brand-danger/20 transition-colors">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
    </div>

    <!-- 会员详情底部卡片 -->
    <transition name="slide-up">
      <div v-if="detailVisible" class="fixed inset-0 z-50 flex items-end" @click.self="detailVisible = false">
        <div class="w-full bg-dark-surface rounded-t-3xl border-t border-dark-border p-6 shadow-2xl">
          <!-- 拖动条 -->
          <div class="w-12 h-1 bg-gray-600 rounded-full mx-auto mb-5"></div>

          <!-- Loading -->
          <div v-if="detailLoading" class="py-8 flex justify-center">
            <i class="fas fa-spinner fa-spin text-2xl text-brand-primary"></i>
          </div>

          <!-- 详情内容 -->
          <template v-else-if="memberDetail">
            <!-- 头像 + 用户名 -->
            <div class="flex items-center gap-4 mb-6">
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-brand-primary to-blue-600 flex items-center justify-center text-2xl font-bold text-white shadow-glow-blue">
                {{ memberDetail.username[0] }}
              </div>
              <div>
                <div class="text-lg font-bold text-white">{{ memberDetail.username }}</div>
                <div class="text-xs text-gray-500">注册时间：{{ formatTime(memberDetail.regtime) }}</div>
              </div>
            </div>

            <!-- 数据卡片 -->
            <div class="grid grid-cols-2 gap-4 mb-6">
              <!-- 账户余额 -->
              <div class="bg-dark-card rounded-2xl p-4 border border-dark-border text-center">
                <div class="text-xs text-gray-500 mb-1">账户余额</div>
                <div class="text-2xl font-bold text-brand-success">¥{{ memberDetail.money }}</div>
              </div>
              <!-- 奖金组 -->
              <div class="bg-dark-card rounded-2xl p-4 border border-dark-border text-center relative group">
                <div class="text-xs text-gray-500 mb-1">奖金组</div>
                <div class="text-2xl font-bold text-brand-accent flex items-center justify-center gap-2">
                  {{ memberDetail.rebate }}
                  <button @click="openEditRebate" class="text-gray-400 hover:text-white transition-colors">
                    <i class="fas fa-edit text-sm"></i>
                  </button>
                </div>
                <div class="text-[10px] text-gray-600 mt-0.5">满{{ Math.floor(memberDetail.rebate/10) }}%返点</div>
              </div>
            </div>

            <!-- 最后登录 -->
            <div class="text-center text-[11px] text-gray-600">
              最后登录：{{ formatTime(memberDetail.logintime) }}
            </div>
          </template>

          <div v-else class="py-6 text-center text-gray-500 text-sm">无法获取成员信息</div>

          <button @click="detailVisible = false" class="w-full mt-5 py-3 rounded-xl bg-dark-card border border-dark-border text-gray-400 text-sm font-medium active:scale-95 transition-transform">
            关闭
          </button>
        </div>
      </div>
    </transition>

    <!-- 修改奖金组弹窗 -->
    <transition name="fade">
      <div v-if="editRebateVisible" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-dark-surface rounded-2xl w-full max-w-sm border border-dark-border overflow-hidden shadow-2xl relative">
          <div class="p-5 border-b border-dark-border flex justify-between items-center">
            <h3 class="text-white font-bold text-lg">修改奖金组</h3>
            <button @click="editRebateVisible = false" class="text-gray-400 hover:text-white">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="p-5 relative">
            <div class="mb-4">
              <label class="block text-xs text-gray-400 mb-2">为成员 <span class="text-white">{{ memberDetail?.username }}</span> 设置新奖金组</label>
              <div class="relative w-full">
                <select v-model.number="editRebateValue" class="w-full appearance-none bg-dark-bg border border-dark-border rounded-xl px-4 py-3 text-white focus:outline-none focus:border-brand-primary transition-colors text-center font-bold text-lg cursor-pointer">
                  <option disabled value="">请选择奖金组</option>
                  <option v-for="opt in rebateOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-gray-400">
                  <i class="fas fa-chevron-down"></i>
                </div>
              </div>
              <p class="text-xs text-brand-warning mt-2">提示: 新奖金组不能高于您当前的奖金组</p>
            </div>
            <!-- Loading Overlay for submission -->
            <div v-if="submittingRebate" class="absolute inset-0 bg-dark-surface/80 flex items-center justify-center rounded-b-2xl z-10">
              <i class="fas fa-spinner fa-spin text-2xl text-brand-primary"></i>
            </div>
          </div>
          <div class="p-4 bg-dark-card border-t border-dark-border flex gap-3">
            <button @click="editRebateVisible = false" class="flex-1 py-3 rounded-xl bg-dark-bg border border-dark-border text-gray-300 font-medium active:scale-95 transition-transform" :disabled="submittingRebate">
              取消
            </button>
            <button @click="submitRebateEdit" :disabled="submittingRebate || !editRebateValue" class="flex-1 py-3 rounded-xl bg-brand-primary text-white font-bold shadow-glow-blue active:scale-95 transition-transform disabled:opacity-50 disabled:active:scale-100">
              确认修改
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import { useAuthStore } from '../../../store/auth'
import { getAgentSummary, getAgentMembers, getCommissionDetail, getMemberDetail, updateMemberRebate, getInviteLinks, createInviteLink, deleteInviteLink } from '@/api/agent'

const authStore = useAuthStore()

const activeTab = ref('links')
const summary = ref({
  invite_code: authStore.userInfo.username || '------',
  commission_month: 0,
  team_total: 0,
  my_rebate: 1996
})

const teamMembers = ref<any[]>([])
const commissionList = ref<any[]>([])
const commissionLoading = ref(false)

const inviteLinkList = ref<any[]>([])
const linksLoading = ref(false)
const newLinkRebate = ref<number | ''>('')
const generatingLink = ref(false)

// 会员详情卡片
const detailVisible = ref(false)
const detailLoading = ref(false)
const memberDetail = ref<any>(null)

// 修改奖金组
const editRebateVisible = ref(false)
const editRebateValue = ref<number | ''>('')
const submittingRebate = ref(false)

const rebateOptions = computed(() => {
  const options = []
  const max = Number(summary.value.my_rebate) || 1996
  // 从最大返点一直到 1900，步长为 2
  for (let r = max; r >= 1900; r -= 2) {
    const pct = ((r - 1900) / 20).toFixed(1)
    const formattedPct = pct.endsWith('.0') ? pct.slice(0, -2) : pct
    options.push({ label: `${r} - ${formattedPct}%`, value: r })
  }
  return options
})

const openEditRebate = () => {
  if (memberDetail.value) {
    editRebateValue.value = Number(memberDetail.value.rebate) || ''
    editRebateVisible.value = true
  }
}

const submitRebateEdit = async () => {
  if (!editRebateValue.value || !memberDetail.value) return
  
  submittingRebate.value = true
  try {
    // request.ts 拦截器会在 code !== 200 时直接 reject
    // 所以只要能走到这里，说明请求成功
    await updateMemberRebate({
      uid: memberDetail.value.uid,
      rebate: Number(editRebateValue.value)
    })
    
    alert('奖金组修改成功')
    
    memberDetail.value.rebate = Number(editRebateValue.value)
    editRebateVisible.value = false
  } catch (err: any) {
    console.error('修改奖金组失败', err)
    let errorMsg = '修改失败，请重试'
    if (err.msg) errorMsg = err.msg
    else if (err.response?.data?.msg) errorMsg = err.response.data.msg
    else if (err.message) errorMsg = err.message // 从拦截器的 reject 中提取错误或默认文字
    
    alert(errorMsg)
  } finally {
    submittingRebate.value = false
  }
}

const showMemberDetail = async (uid: number | string) => {
  detailVisible.value = true
  detailLoading.value = true
  memberDetail.value = null
  try {
    const res: any = await getMemberDetail(uid)
    const d = res?.uid ? res : (res?.data || res)
    if (d?.uid) memberDetail.value = d
  } catch (err) {
    console.error('获取成员详情失败', err)
  } finally {
    detailLoading.value = false
  }
}

const fetchData = async () => {
  try {
    const [sRes, mRes]: any[] = await Promise.all([
      getAgentSummary(),
      getAgentMembers({ limit: 50 })
    ])
    if (sRes && sRes.data) {
      summary.value.commission_month = parseFloat(sRes.data.commission_month || '0')
      summary.value.team_total = sRes.data.team_total || 0
      summary.value.invite_code = sRes.data.invite_code || summary.value.invite_code
      if (sRes.data.my_rebate) summary.value.my_rebate = Number(sRes.data.my_rebate)
    } else if (sRes) { 
      // fallback if the structure is somewhat flat depending on the framework proxy behavior
      summary.value.commission_month = parseFloat(sRes.commission_month || '0')
      summary.value.team_total = sRes.team_total || 0
      summary.value.invite_code = sRes.invite_code || summary.value.invite_code
      if (sRes.my_rebate) summary.value.my_rebate = Number(sRes.my_rebate)
    }
    
    let mData = mRes?.data?.list ? mRes.data.list : (mRes?.list ? mRes.list : [])
    teamMembers.value = mData.map((m: any) => ({
      id: m.uid,
      name: m.username,
      joinTime: formatTime(m.regtime),
      contribution: parseFloat(m.commission || '0')
    }))
    
  } catch(err) {
    console.error('代理数据获取失败', err)
  }
}

const loadCommission = async () => {
  commissionLoading.value = true
  try {
    const res: any = await getCommissionDetail({ limit: 50 })
    let cData = res?.data?.list ? res.data.list : (res?.list ? res.list : [])
    commissionList.value = cData
  } catch(err) {
    console.error('佣金明细获取失败', err)
  } finally {
    commissionLoading.value = false
  }
}

const loadInviteLinks = async () => {
  linksLoading.value = true
  try {
    const res: any = await getInviteLinks()
    inviteLinkList.value = res?.data?.list ? res.data.list : (res?.list ? res.list : [])
  } catch(err) {
    console.error('获取推广链接失败', err)
  } finally {
    linksLoading.value = false
  }
}

const generateInviteLink = async () => {
  if (!newLinkRebate.value) return
  generatingLink.value = true
  try {
    await createInviteLink({ rebate: Number(newLinkRebate.value) })
    alert('生成成功')
    newLinkRebate.value = ''
    await loadInviteLinks()
  } catch(err: any) {
    console.error('生成失败', err)
    let errorMsg = '生成失败'
    if (err.msg) errorMsg = err.msg
    else if (err.response?.data?.msg) errorMsg = err.response.data.msg
    else if (err.message) errorMsg = err.message
    alert(errorMsg)
  } finally {
    generatingLink.value = false
  }
}

const removeInviteLink = async (id: number) => {
  if (!confirm('确定作废该链接吗？已注册用户不受影响。')) return
  try {
    await deleteInviteLink({ id })
    alert('作废成功')
    await loadInviteLinks()
  } catch(err: any) {
    console.error('作废失败', err)
    let errorMsg = '作废失败'
    if (err.msg) errorMsg = err.msg
    else if (err.response?.data?.msg) errorMsg = err.response.data.msg
    else if (err.message) errorMsg = err.message
    alert(errorMsg)
  }
}

const copyCustomLink = (code: string) => {
  const link = `${location.origin}/register?ref=${code}`
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(link).catch(() => fallbackCopy(link))
  } else {
    fallbackCopy(link)
  }
}

watch(activeTab, (newVal) => {
  if (newVal === 'commission' && commissionList.value.length === 0) {
    loadCommission()
  }
  if (newVal === 'links' && inviteLinkList.value.length === 0) {
    loadInviteLinks()
  }
})

const formatTime = (timestamp: number | string) => {
  if (!timestamp) return '-'
  const date = new Date(Number(timestamp) * 1000)
  const pad = (n: number) => n < 10 ? '0' + n : n
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`
}

const fallbackCopy = (text: string) => {
  const textArea = document.createElement("textarea")
  textArea.value = text
  textArea.style.position = "fixed"
  textArea.style.left = "-9999px"
  textArea.style.top = "0"
  document.body.appendChild(textArea)
  textArea.focus()
  textArea.select()
  try {
    document.execCommand('copy')
    alert('复制成功')
  } catch (err) {
    console.error('Fallback copy failed', err)
  }
  document.body.removeChild(textArea)
}



onMounted(() => fetchData())
</script>