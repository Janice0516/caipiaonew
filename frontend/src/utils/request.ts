import axios from 'axios';
import { useAuthStore } from '@/store/auth'; 

const service = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/index.php?m=api',
  timeout: 15000,
  withCredentials: true // 允许携带 Cookie
});

// 请求拦截器
service.interceptors.request.use((config) => {
  // 修复 axios 拼接 URL 时自动添加斜杠的问题 (例如 baseURL: ...?m=api, url: &c=auth 会变成 ...?m=api/&c=auth)
  if (config.url && config.url.startsWith('&')) {
    config.url = (config.baseURL || '') + config.url;
    config.baseURL = ''; // 清空 baseURL，避免 axios 再次拼接
  }

  // 如果遇到需要在 Header 中携带 token
  // const authStore = useAuthStore();
  // if (authStore.token) {
  //   config.headers['Authorization'] = `Bearer ${authStore.token}`;
  // }
  
  // 对于表单请求可以在这统一处理，如果是 json 默认就是 application/json
  return config;
}, (error) => {
  return Promise.reject(error);
});

// 响应拦截器
service.interceptors.response.use(
  (response) => {
    const res = response.data;
    // 后台统一格式: { code: 200, msg: "...", data: {...} }
    if (res.code && res.code !== 200) {
      if (res.code === 401) {
        const authStore = useAuthStore();
        authStore.logoutAction();
        window.location.href = '/login'; // 跳回登录
      }
      return Promise.reject(new Error(res.msg || '请求失败'));
    }
    // 很多地方都直接取 res.games / res.token 等，所以这里直接返回 res.data
    return res.data || res; 
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default service;
