<template>
  <div class="flex-1 overflow-y-auto no-scrollbar pb-20">
    <!-- 头部 -->
    <header class="px-4 py-4 flex justify-between items-center bg-dark-bg/80 backdrop-blur sticky top-0 z-10">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-primary to-purple-600 flex items-center justify-center font-bold text-white">
          Y
        </div>
        <h1 class="text-lg font-bold tracking-wide">云鼎国际</h1>
      </div>
      <div class="flex gap-4">
        <button @click="$router.push('/notice')" class="text-gray-400 hover:text-white transition-transform active:scale-95"><i class="far fa-bell text-lg"></i></button>
        <button @click="goToCS" class="text-gray-400 hover:text-white transition-transform active:scale-95"><i class="far fa-comment-dots text-lg"></i></button>
      </div>
    </header>

    <!-- 轮播图 (Swiper) -->
    <div class="px-4 mb-6" v-if="banners && banners.length > 0">
      <swiper
        :modules="[Autoplay, Pagination]"
        :slides-per-view="1"
        :space-between="10"
        :pagination="{ clickable: true }"
        :autoplay="{ delay: 3000, disableOnInteraction: false }"
        :loop="true"
        :observer="true"
        :observe-parents="true"
        class="w-full rounded-2xl shadow-glow-card"
      >
        <swiper-slide v-for="(banner, index) in banners" :key="index">
          <div @click="handleBannerClick(banner, index)" class="w-full relative overflow-hidden group rounded-2xl flex items-center justify-center cursor-pointer transition-transform active:scale-[0.98]">
            <img :src="banner.img || banner" class="w-full h-auto min-h-[8rem] object-contain block rounded-2xl" alt="banner" />
          </div>
        </swiper-slide>
      </swiper>
    </div>

    <!-- 公告栏 -->
    <div class="px-4 mb-6">
      <div @click="showNoticeModal = true" class="glass rounded-lg p-3 flex items-center gap-3 text-sm text-gray-400 cursor-pointer overflow-hidden relative">
        <i class="fas fa-volume-up text-brand-primary shrink-0 z-10"></i>
        <div class="flex-1 overflow-hidden relative h-5">
           <transition name="slide-up">
              <div :key="currentAnnIndex" class="absolute inset-x-0 w-full whitespace-nowrap text-ellipsis overflow-hidden">
                {{ announcementList.length > 0 ? announcementList[currentAnnIndex] : '系统公告：暂无公告' }}
              </div>
           </transition>
        </div>
      </div>
    </div>

    <!-- 热门彩种 -->
    <div class="px-4 mb-8">
      <div class="flex justify-between items-center mb-4">
        <h2 class="font-bold text-lg text-white">热门彩种</h2>
        <span class="text-xs text-gray-500 cursor-pointer" @click="$router.push('/lobby')">全部 ></span>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div v-for="game in hotGames" :key="game.id" @click="goToGame(game.id)"
             class="bg-dark-card p-4 rounded-xl border border-dark-border hover:border-brand-primary/50 active:scale-95 transition-all relative overflow-hidden group cursor-pointer">
          <div class="absolute right-0 top-0 w-16 h-16 bg-gradient-to-bl from-brand-primary/10 to-transparent rounded-bl-3xl"></div>
          <div class="relative z-10 flex flex-col items-start">
            <i :class="[game.icon, 'text-2xl mb-3', game.color]"></i>
            <h3 class="font-bold text-gray-200">{{ game.name }}</h3>
            <p class="text-xs text-gray-500 mt-1">{{ game.desc }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 近期中奖 -->
    <div class="px-4 mb-4">
      <h2 class="font-bold text-lg text-white mb-4">中奖榜单</h2>
      <div class="bg-dark-card rounded-xl border border-dark-border overflow-hidden h-48 relative shadow-inner py-4 px-2">
        <div class="animate-scroll-y flex flex-col gap-4">
          <div v-for="(winner, index) in displayWinners" :key="index" class="flex items-center justify-between shrink-0 px-2">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-xs text-gray-300 shrink-0">
                {{ winner.avatar }}
              </div>
              <div>
                <div class="text-sm text-gray-300">{{ winner.name }}</div>
                <div class="text-xs text-gray-500">{{ winner.game }}</div>
              </div>
            </div>
            <div class="text-brand-accent font-mono font-bold">
              + {{ winner.amount }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- 弹窗：系统公告 -->
    <transition name="fade">
      <div v-if="showNoticeModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-dark-surface rounded-2xl w-full max-w-sm border border-dark-border overflow-hidden shadow-2xl flex flex-col max-h-[80vh]">
          <div class="p-4 border-b border-dark-border flex justify-between items-center bg-dark-card shrink-0">
            <div class="flex items-center gap-2">
              <i class="fas fa-bullhorn text-brand-primary text-xl"></i>
              <h3 class="text-white font-bold text-lg">系统公告</h3>
            </div>
            <button @click="showNoticeModal = false" class="text-gray-400 hover:text-white transition-colors">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
          <div class="p-5 overflow-y-auto no-scrollbar text-sm text-gray-300 leading-relaxed announcement-content" v-html="rawAnnouncementHtml || '暂无详细公告内容...'">
          </div>
          <div class="p-4 border-t border-dark-border bg-dark-card shrink-0">
             <button @click="showNoticeModal = false" class="w-full py-3 rounded-xl bg-brand-primary text-white font-bold shadow-glow-blue active:scale-95 transition-transform">
               我知道了
             </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import { getLobbyList } from '@/api/game'

const router = useRouter()

const banners = ref<any[]>([])

const announcementList = ref<string[]>([])
const currentAnnIndex = ref(0)
const rawAnnouncementHtml = ref('')
const showNoticeModal = ref(false)

let annInterval: any = null
const startAnnCarousel = () => {
  if (announcementList.value.length > 1) {
    annInterval = setInterval(() => {
      currentAnnIndex.value = (currentAnnIndex.value + 1) % announcementList.value.length
    }, 3000)
  }
}
onUnmounted(() => {
  if (annInterval) clearInterval(annInterval)
})

const handleBannerClick = (banner: any, index: number) => {
  // 提取可能的链接字段 (针对后台复杂的 JSON 结构)
  const link = banner.link || banner.url || banner.href || '';
  
  if (link) {
    if (link.startsWith('http')) {
      window.location.href = link;
    } else {
      router.push(link);
    }
  } else {
    // 强制打底硬编码：第二张图跳挂机端，第一张图跳客服
    if (index === 1) {
      window.location.href = '/bot/';
    } else {
      window.location.href = '/kefu/chatlink.html';
    }
  }
}

const goToCS = () => {
  window.location.href = '/kefu/chatlink.html';
}

const goToGame = (id: number) => {
  router.push(`/game/${id}`)
}

const templateMap: Record<string, any> = {
  'race': { icon: 'fas fa-car', color: 'text-brand-primary' },
  'ssc': { icon: 'fas fa-ticket-alt', color: 'text-brand-danger' },
  'lhc': { icon: 'fas fa-dragon', color: 'text-brand-success' },
  'pc28': { icon: 'fas fa-egg', color: 'text-brand-accent' },
  'k3': { icon: 'fas fa-dice-three', color: 'text-red-400' },
  'default': { icon: 'fas fa-gamepad', color: 'text-purple-400' }
}

const hotGames = ref<any[]>([])

// 暂不支持的彩种（无模板或已关闭）
const HIDDEN_TEMPLATES = ['a11x5', 'klsf', 'fantan', 'liubo']

onMounted(async () => {
  try {
    const res: any = await getLobbyList()
    if (res && res.games) {
      // 过滤不支持的彩种，取前 4 个热门
      hotGames.value = res.games
        .filter((g: any) => !HIDDEN_TEMPLATES.includes(g.template))
        .slice(0, 4)
        .map((g: any) => {
          let tmpl = g.template || 'default'
          if (tmpl === 'xync') tmpl = 'race'

          const ui = templateMap[tmpl] || templateMap['default']
          return {
            id: g.gameid,
            name: g.name,
            desc: g.content || '热门推荐',
            icon: ui.icon,
            color: ui.color
          }
        })
    }
    
    // 公告处理
    if (res && res.announcement) {
      rawAnnouncementHtml.value = res.announcement
      // 提取纯文本用于滚动展示的预览截断，按换行符或段落切分
      const lines = res.announcement.split(/\r?\n|<br\s*\/?>|<\/p>/i)
        .map((l: string) => l.replace(/<[^>]*>?/gm, '').trim())
        .filter((l: string) => l.length > 0)
      
      if (lines.length > 0) {
        announcementList.value = lines
        startAnnCarousel()
      } else {
        announcementList.value = ['系统公告：暂无公告']
      }
    } else {
      announcementList.value = ['系统公告：暂无公告']
    }
    // 动态轮播图处理
    if (res && res.banners) {
       let parsedBanners = [];
       try { 
         parsedBanners = typeof res.banners === 'string' ? JSON.parse(res.banners) : res.banners; 
       } catch (e) {
         console.warn('解析轮播图配置失败', e);
       }
       if (parsedBanners && parsedBanners.length) {
         banners.value = parsedBanners;
       }
    }
    
  } catch (err) {
    console.error('获取首页数据失败', err)
  }
})

const firstNames = ['张', '王', '李', '赵', '陈', '刘', '杨', '黄', '吴', '周', '徐', '孙', '马', '朱', '胡', '林', '郭', '何', '高', 'A', 'B', 'C', 'L', 'S', 'W', 'Z'];
const gameNames = ['极速赛车', '幸运飞艇', '重庆时时彩', 'PC蛋蛋', '澳洲幸运10', '极速PK10', '分分彩', '三分彩', '台湾宾果', '六合彩'];

const generateWinners = (count = 20) => {
  const result = [];
  for (let i = 0; i < count; i++) {
    const fn = firstNames[Math.floor(Math.random() * firstNames.length)];
    const gn = gameNames[Math.floor(Math.random() * gameNames.length)];
    // Random amount up to 99999
    const amt = (Math.random() * 95000 + 500).toFixed(2);
    const formattedAmt = parseFloat(amt).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    result.push({
      id: i,
      name: `${fn}**`,
      avatar: fn,
      game: gn,
      amount: formattedAmt
    });
  }
  return result;
}

const winners = ref(generateWinners(20));
// Duplicate the list to make the infinite scroll seamless (scrolls through the first half and snaps to top)
const displayWinners = computed(() => [...winners.value, ...winners.value]);
</script>

<style scoped>
@keyframes scrollY {
  0% { transform: translateY(0); }
  100% { transform: translateY(-50%); /* Moves exactly the height of one original list */ }
}

.animate-scroll-y {
  /* Provide a smooth infinite linear scroll. 30s is good for 20 items */
  animation: scrollY 30s linear infinite;
}

.animate-scroll-y:hover {
  animation-play-state: paused;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.5s ease;
}
.slide-up-enter-from {
  opacity: 0;
  transform: translateY(100%);
}
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(-100%);
  position: absolute;
}

.announcement-content :deep(img) {
  max-width: 100%;
  border-radius: 8px;
  margin: 8px 0;
}
</style>
