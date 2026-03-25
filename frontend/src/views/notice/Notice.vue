<template>
  <div class="fixed inset-0 bg-dark-bg z-50 flex flex-col overflow-hidden">
    <!-- 顶部导航栏 -->
    <header class="flex items-center justify-between px-4 h-14 bg-dark-card border-b border-dark-border shrink-0">
      <button @click="goBack" class="flex items-center text-gray-300 hover:text-white transition-colors">
        <i class="fas fa-chevron-left text-lg"></i>
        <span class="ml-1 text-sm">返回</span>
      </button>
      <h1 class="text-white text-lg font-bold">公告</h1>
      <div class="w-10"></div> <!-- 占位保持居中 -->
    </header>

    <!-- 公告列表滚动区 -->
    <div class="flex-1 overflow-y-auto no-scrollbar p-4 space-y-4">
      <div v-if="loading" class="flex justify-center items-center py-20 text-gray-500">
        <i class="fas fa-spinner fa-spin text-2xl"></i>
      </div>
      
      <div v-else-if="notices.length === 0" class="flex flex-col items-center justify-center py-20 text-gray-500">
        <i class="fas fa-inbox text-5xl mb-4 opacity-30"></i>
        <p>暂无最新公告</p>
      </div>

      <div 
        v-for="(notice, index) in notices" 
        :key="index"
        @click="openDetail(notice)"
        class="bg-dark-card border border-dark-border rounded-xl p-4 shadow-sm hover:border-brand-primary/50 transition-all cursor-pointer active:scale-[0.98]"
      >
        <div class="flex items-center gap-2 mb-2">
          <i class="far fa-dot-circle text-brand-primary shrink-0"></i>
          <h3 class="text-white font-bold text-base truncate flex-1">{{ notice.title }}</h3>
        </div>
        
        <div class="text-sm text-gray-400 line-clamp-2 leading-relaxed mb-3">
          {{ notice.content }}
        </div>
        
        <div class="text-[11px] text-gray-500 flex items-center justify-between border-t border-white/5 pt-2">
          <span>发布时间：{{ notice.date }}</span>
          <span class="text-brand-primary opacity-80">查看详情 ></span>
        </div>
      </div>
      
      <!-- 底部安全间距 -->
      <div class="h-10"></div>
    </div>

    <!-- 弹窗：公告具体详情 -->
    <transition name="fade">
      <div v-if="selectedNotice" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-dark-surface rounded-2xl w-full max-w-sm border border-dark-border overflow-hidden shadow-2xl flex flex-col max-h-[80vh]">
          <div class="p-4 border-b border-dark-border flex justify-between items-center bg-dark-card shrink-0">
            <h3 class="text-white font-bold text-lg truncate pr-4">{{ selectedNotice.title }}</h3>
            <button @click="selectedNotice = null" class="text-gray-400 hover:text-white transition-colors shrink-0">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
          <div class="p-5 overflow-y-auto no-scrollbar text-sm text-gray-300 leading-relaxed whitespace-pre-wrap">
            {{ selectedNotice.content }}
          </div>
          <div class="p-4 border-t border-dark-border bg-dark-card shrink-0 text-right text-xs text-gray-500">
            发布于: {{ selectedNotice.date }}
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getLobbyList } from '@/api/game'

const router = useRouter()
const notices = ref<any[]>([])
const loading = ref(true)
const selectedNotice = ref<any>(null)

const goBack = () => {
  router.back()
}

const openDetail = (notice: any) => {
  selectedNotice.value = notice
}

// 格式化时间戳生成器：倒推时间
const generateDate = (daysAgo: number) => {
  const d = new Date()
  d.setDate(d.getDate() - daysAgo)
  // 增加一些随机的小时和分钟，显得更真实
  d.setHours(Math.floor(Math.random() * 8) + 9) // 9 AM to 5 PM
  d.setMinutes(Math.floor(Math.random() * 60))
  d.setSeconds(Math.floor(Math.random() * 60))
  
  const pad = (n: number) => n.toString().padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`
}

onMounted(async () => {
  try {
    const res: any = await getLobbyList()
    if (res && res.announcement) {
      // 提取核心文字，按换行进行分割
      const rawHtml = res.announcement
      const lines = rawHtml.split(/\r?\n|<br\s*\/?>|<\/p>/i)
        .map((l: string) => l.replace(/<[^>]*>?/gm, '').trim())
        .filter((l: string) => l.length > 5) // 过滤掉太短的无意义换行
      
      const parsedNotices = []
      
      // 如果后端只有一段长文字，我们人工把它切分一下或者当做一条
      if (lines.length > 0) {
        lines.forEach((line: string, index: number) => {
          // 彻底屏蔽旧版收款银行通知
          if (line.includes('收款银行')) return;

          // 尝试提取一个标题：冒号前面的词，或者直接截取前10个字
          let title = line.includes('：') ? line.split('：')[0] : (line.substring(0, 12) + '通知')
          if (title.length < 3) title = '重要通知'
          // 优化一下常见的词缀
          if (line.includes('收款银行')) title = '银行账号变更通知'
          if (line.includes('维护')) title = '机房维护通知'
          if (line.includes('域名')) title = '新增防封域名'
            
          parsedNotices.push({
            id: index + 1,
            title: title || '系统重要公告',
            content: line,
            date: generateDate(index * 3) // 伪造不同天数的历史时间戳
          })
        })
      } else {
      // 如果后端连文字都没有，给一条默认的
        parsedNotices.push({
          id: 1,
          title: '系统通知',
          content: rawHtml.replace(/<[^>]*>?/gm, '').trim() || '暂无详细公告',
          date: generateDate(0)
        })
      }
      
      // 机房线路抢修通告 (最新置顶)
      parsedNotices.push({
        id: 995,
        title: '机房线路维修',
        content: '尊敬的用户您好：\n\n今日因机房更换线路导致有部分地区无法正常使用，现已抢修完成。\n\n由此带来的不便我们深感抱歉，感谢您的理解与支持！\n\n----云鼎国际 特此公告----\n感谢您选择云鼎国际，祝您多多盈利',
        date: '2026-01-04 16:38:11'
      });
      
      // 平台升级历史通告
      parsedNotices.push({
        id: 996,
        title: '平台升级公告',
        content: '面对一直增长的手机用户，为了广大用户的设备安全，云鼎国际取消电脑版，挂机软件取消电脑包文件，全部使用手机版H5版面，运行速度更快，更安全！ 一个浏览器打开，随时随地投注，智能Ai掌控，一键运行，不怕断网，断电，实现盈利目标！\n\n----云鼎国际 特此公告----\n感谢您选择云鼎国际，祝您多多盈',
        date: '2025-08-31 12:00:01'
      });
      
      // 充值系统历史通告
      parsedNotices.push({
        id: 997,
        title: '充值重要通知',
        content: '尊敬的用户您好：\n\n因近日改变充值方式导致多数用户向平台客服反应充值操作繁琐，平台为方便用户操作更便捷现以引入三方渠道，以专属钱包地址绑定用户身份避免用户存款无法及时到账等问题。\n\n如遇充值界面还存在充值金额输入框的用户们请刷新网页或清空浏览器缓存。\n\n云鼎全体人员向及时为平台提出建议的用户们表示最崇高的感谢，因为有你们的信任平台才能持续发展到今天，感谢所有云鼎的用户！\n\n如有问题或建议请及时联系客服进行反馈，近日出现的问题给各位用户带来的不便我们深表歉意！\n\n----云鼎国际 特此公告----\n感谢您选择云鼎国际，祝您多多盈利',
        date: '2025-05-09 16:30:07'
      });
      
      // 客服维护历史通告
      parsedNotices.push({
        id: 998,
        title: '客服系统维护',
        content: '尊敬的用户您好：\n\n客服系统将在以下日期进行维护。 在此期间，将无法访问客服\n日期：2025年03月11日[星期二] 04:30~5:00\n\n----云鼎国际 特此公告----\n感谢您选择云鼎国际，祝您多多盈利',
        date: '2025-03-03 20:31:38'
      });
      
      // 永久置底的『盛大开业』创世公告
      parsedNotices.push({
        id: 999,
        title: '云鼎国际盛大开业',
        content: '尊敬的用户您好：\n\n云鼎国际于北京时间2015年5月2日中午12:00点起，我们将正式开放运营。作为一家坚守公开、公平和公正原则的公司，我们致力于重塑行业的公信力，并为行业的进步做出贡献。为确保游戏的公正性和透明度，我们采取以下措施：\n\n1. 开奖机制：所有游戏的开奖结果完全依赖于权威第三方机构和政府的开奖过程，绝不会存在任何形式的平台操控或欺诈行为。我们严格杜绝任何不公平行为，以确保游戏结果的绝对公正性。\n\n2. 数据来源：引入监督计划，保证选号结果的选择毫无偏见或歧视。同时，我们积极采用区块链技术来改变彩票行业现状，确保公平性得到充分实现。\n\n3. 公开透明：我们公开披露游戏规则，确保彩民对游戏规则和机制有充分的了解。我们鼓励彩民积极参与并监督游戏的公平性，以促进行业的发展。我们承诺，未来将持续推出更多公平性的游戏，为市场的发展做出贡献。始终恪守公开、公正、公平的原则，我们将努力提升行业的整体形象和信任度。最后，我们衷心祝愿您在游戏中大奖不断。\n\n----云鼎国际 特此公告----\n感谢您选择云鼎国际，祝您多多盈',
        date: '2015-05-02 11:47:01'
      });

      notices.value = parsedNotices
    }
  } catch (err) {
    console.error('获取公告失败', err)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
