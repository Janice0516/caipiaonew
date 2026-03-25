<template>
  <div class="fixed inset-0 overflow-y-auto bg-dark-bg text-gray-200">
    <div class="flex flex-col items-center min-h-full p-4">
      <div class="w-full max-w-md my-auto flex flex-col items-center py-8">
        <h1 class="text-3xl font-bold mb-8 text-brand-primary animate-pulse-slow">
          云鼎国际注册
        </h1>
        
        <div class="w-full bg-dark-card p-8 rounded-2xl shadow-glow-card border border-dark-border relative overflow-hidden group">
          <!-- 背景装饰 -->
          <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand-primary/10 rounded-full blur-2xl group-hover:bg-brand-primary/20 transition-all duration-500"></div>
          <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-brand-secondary/10 rounded-full blur-2xl group-hover:bg-brand-secondary/20 transition-all duration-500"></div>

        <form @submit.prevent="handleRegister" class="space-y-4 relative z-10">
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1">账号</label>
            <input 
              v-model="registerForm.username"
              type="text" 
              class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-gray-600 text-sm"
              placeholder="请输入账号"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1">真实姓名</label>
            <input 
              v-model="registerForm.name"
              type="text" 
              class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-gray-600 text-sm"
              placeholder="请输入真实姓名 (2-5字)"
              required
            />
          </div>
          
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1">密码</label>
            <input 
              v-model="registerForm.password"
              type="password" 
              class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-gray-600 text-sm"
              placeholder="含大写英文字母、数字和特殊符号"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1">确认密码</label>
            <input 
              v-model="registerForm.confirmPassword"
              type="password" 
              class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-gray-600 text-sm"
              placeholder="请再次输入密码"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1">邀请码</label>
            <input 
              v-model="registerForm.agent"
              type="text" 
              class="w-full bg-dark-bg border border-dark-border rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-gray-600 text-sm"
              placeholder="请输入邀请码"
            />
          </div>

          <!-- 验证码部分 -->
          <div>
            <label class="block text-xs font-medium text-gray-400 mb-1">验证码</label>
            <div class="flex gap-4">
              <input 
                v-model="registerForm.code"
                type="text" 
                class="flex-1 min-w-0 bg-dark-bg border border-dark-border rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 placeholder-gray-600 text-sm"
                placeholder="验证码"
                required
              />
              <img 
                :src="captchaUrl" 
                @click="refreshCaptcha" 
                class="w-[100px] h-10 shrink-0 object-contain bg-white rounded-lg cursor-pointer border border-dark-border"
                alt="captcha"
                title="点击刷新验证码"
              />
            </div>
          </div>

          <!-- 错误提示框 -->
          <div v-if="errorMsg" class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-2 rounded-lg text-xs">
            {{ errorMsg }}
          </div>

          <button 
            type="submit" 
            :disabled="loading"
            class="w-full bg-gradient-to-r from-brand-primary to-brand-secondary text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-glow-blue transform hover:-translate-y-0.5 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center mt-4"
          >
            <span v-if="loading" class="animate-spin mr-2">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? '注册中...' : '立即注册' }}
          </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
          <p>已有账号？ <router-link to="/login" class="text-brand-primary hover:underline">去登录</router-link></p>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { getCaptchaUrl } from '@/api/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const registerForm = ref({
  username: '',
  name: '',
  password: '',
  confirmPassword: '',
  agent: (route.query.ref as string) || '',
  code: ''
})

const loading = ref(false)
const errorMsg = ref('')
const captchaUrl = ref('')

const refreshCaptcha = () => {
  captchaUrl.value = getCaptchaUrl()
}

onMounted(() => {
  refreshCaptcha()
})

const handleRegister = async () => {
  if (registerForm.value.password !== registerForm.value.confirmPassword) {
    errorMsg.value = '两次输入的密码不一致'
    return
  }

  // 强制强密码规则：至少1个大写字母、1个数字、1个符号
  const pwdRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/
  if (!pwdRegex.test(registerForm.value.password)) {
    errorMsg.value = '密码必须包含至少一个大写英文字母、一个数字和一个特殊符号'
    return
  }
  
  if (registerForm.value.name.length < 2 || registerForm.value.name.length > 5) {
    errorMsg.value = '姓名长度应为2-5个汉字'
    return
  }

  errorMsg.value = ''
  loading.value = true
  
  try {
    const success = await authStore.registerAction(registerForm.value)
    if (success) {
      alert('注册成功，请登录！')
      router.push('/login')
    }
  } catch (error: any) {
    errorMsg.value = error.message || '注册失败，请检查填写信息'
    refreshCaptcha()
  } finally {
    loading.value = false
  }
}
</script>
