<template>
  <div class="fixed inset-0 overflow-y-auto bg-dark-bg text-gray-200">
    <div class="flex flex-col items-center min-h-full p-4">
      <div class="w-full max-w-md my-auto flex flex-col items-center py-8">
        <h1 class="text-3xl font-bold mb-8 text-brand-primary animate-pulse-slow">
          云鼎国际登录
        </h1>
        
        <div class="w-full bg-dark-card p-8 rounded-2xl shadow-glow-card border border-dark-border relative overflow-hidden group">
          <!-- 背景装饰 -->
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand-primary/10 rounded-full blur-2xl group-hover:bg-brand-primary/20 transition-all duration-500"></div>
          <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-brand-secondary/10 rounded-full blur-2xl group-hover:bg-brand-secondary/20 transition-all duration-500"></div>

        <form @submit.prevent="handleLogin" class="space-y-6 relative z-10">
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">账号</label>
            <input 
              v-model="loginForm.username"
              type="text" 
              class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-gray-600"
              placeholder="请输入账号"
              required
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">密码</label>
            <input 
              v-model="loginForm.password"
              type="password" 
              class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-gray-600"
              placeholder="请输入密码"
              required
            />
          </div>

          <!-- 验证码部分 -->
          <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">验证码</label>
            <div class="flex gap-4">
              <input 
                v-model="loginForm.code"
                type="text" 
                class="flex-1 min-w-0 bg-dark-bg border border-dark-border rounded-lg px-4 py-3 text-white focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-gray-600"
                placeholder="验证码"
                required
              />
              <img 
                :src="captchaUrl" 
                @click="refreshCaptcha" 
                class="w-[120px] h-12 shrink-0 object-contain bg-white rounded-lg cursor-pointer border border-dark-border"
                alt="captcha"
                title="点击刷新验证码"
              />
            </div>
          </div>

          <!-- 错误提示框 -->
          <div v-if="errorMsg" class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg text-sm">
            {{ errorMsg }}
          </div>

          <button 
            type="submit" 
            :disabled="loading"
            class="w-full bg-gradient-to-r from-brand-primary to-brand-secondary text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-glow-blue transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center"
          >
            <span v-if="loading" class="animate-spin mr-2">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? '登录中...' : '立即登录' }}
          </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
          <p>未注册账号？ <router-link :to="{ path: '/register', query: $route.query }" class="text-brand-primary hover:underline">立即注册</router-link></p>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { getCaptchaUrl } from '@/api/auth'

const router = useRouter()
const authStore = useAuthStore()

const loginForm = ref({
  username: '',
  password: '',
  code: ''
})

const loading = ref(false)
const errorMsg = ref('')
const captchaUrl = ref('')

// 刷新验证码
const refreshCaptcha = () => {
  captchaUrl.value = getCaptchaUrl()
}

onMounted(() => {
  refreshCaptcha()
})

const handleLogin = async () => {
  if (!loginForm.value.username || !loginForm.value.password || !loginForm.value.code) {
    errorMsg.value = '请填写完整信息'
    return
  }
  
  errorMsg.value = ''
  loading.value = true
  
  try {
    const success = await authStore.loginAction(loginForm.value)
    if (success) {
      router.push('/')
    }
  } catch (error: any) {
    errorMsg.value = error.message || '登录失败，请检查账号密码或验证码'
    refreshCaptcha() // 登录失败后自动刷新验证码
    loginForm.value.code = '' // 清空之前输入的验证码
  } finally {
    loading.value = false
  }
}
</script>
