import { defineStore } from 'pinia'
import { login, logout, register } from '@/api/auth'
import { getUserBalance, getUserProfile } from '@/api/user'

interface UserInfo {
  uid: number
  username: string
  balance: number
  avatar: string
  nickname: string
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    isLoggedIn: !!localStorage.getItem('token'), // Simple persistent check
    token: localStorage.getItem('token') || '', // 从后端拿到的 JWT / Token
    userInfo: JSON.parse(localStorage.getItem('userInfo') || '{"uid":0,"username":"","balance":0,"avatar":"","nickname":""}') as UserInfo
  }),
  actions: {
    async loginAction(data: any): Promise<boolean> {
      try {
        const res: any = await login(data);
        const token = res.data?.token || res.token;
        const user = res.data?.user || res.user;
        
        if (token) {
          this.isLoggedIn = true;
          this.token = token;
          
          this.userInfo = {
            uid: user?.uid || 0,
            username: user?.username || '',
            balance: user?.money || 0.00,
            avatar: 'https://api.dicebear.com/7.x/avataaars/svg?seed=' + (user?.username || 'default'),
            nickname: user?.nickname || ''
          };
          
          // Persist
          localStorage.setItem('token', this.token);
          localStorage.setItem('userInfo', JSON.stringify(this.userInfo));
          return true;
        }
        return false;
      } catch (error) {
        console.error('Login action error:', error);
        throw error;
      }
    },
    async registerAction(data: any): Promise<boolean> {
      try {
        const res: any = await register(data);
        // 由于 request.ts 的响应拦截器中，如果成功则返回 res.data
        // 所以这里 res 通常就是 { uid: ... } 之类的内容，不再包含 code
        // 只要不抛出异常，就说明注册成功
        return !!res || true;
      } catch (error) {
        console.error('Register action error:', error);
        throw error;
      }
    },
    async fetchBalance() {
      if (!this.isLoggedIn) return;
      try {
        const res: any = await getUserBalance();
        // PHP returns { code: 200, data: { balance: "..." } }
        const balance = res.data?.balance !== undefined ? res.data.balance : res.balance;
        if (balance !== undefined) {
          this.userInfo.balance = parseFloat(balance);
          localStorage.setItem('userInfo', JSON.stringify(this.userInfo));
        }
      } catch (error) {
        console.error('Failed to fetch balance', error);
      }
    },
    async fetchProfile() {
      if (!this.isLoggedIn) return;
      try {
        const res: any = await getUserProfile();
        const data = res.data || res;
        
        if (data && typeof data === 'object') {
          // Merge to retain things like balance if not present or just update the object
          this.userInfo = {
            ...this.userInfo,
            uid: data.uid || this.userInfo.uid,
            username: data.username || this.userInfo.username,
            avatar: data.avatar || 'https://api.dicebear.com/7.x/avataaars/svg?seed=' + (data.username || this.userInfo.username || 'default'),
            nickname: data.nickname || this.userInfo.nickname,
            // also load extra properties the backend profile might return 
            // but we keep the critical ones strongly typed explicitly
          };
          
          if (data.money !== undefined) {
            this.userInfo.balance = parseFloat(data.money);
          }
          
          Object.assign(this.userInfo, data);
          
          localStorage.setItem('userInfo', JSON.stringify(this.userInfo));
        }
      } catch (error) {
        console.error('Failed to fetch profile', error);
      }
    },
    async logoutAction() {
      try {
        await logout();
      } finally {
        this.isLoggedIn = false;
        this.token = '';
        this.userInfo = {
          uid: 0,
          username: '',
          balance: 0.00,
          avatar: '',
          nickname: ''
        };
        localStorage.removeItem('token');
        localStorage.removeItem('userInfo');
      }
    }
  }
})
