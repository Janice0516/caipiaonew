import request from '@/utils/request';

/**
 * 登录
 */
export function login(data: any) {
  return request({
    url: '&c=auth&a=login',
    method: 'post',
    data
  });
}

/**
 * 注册
 */
export function register(data: any) {
  return request({
    url: '&c=auth&a=register',
    method: 'post',
    data
  });
}

/**
 * 退出登录
 */
export function logout() {
  return request({
    url: '&c=auth&a=logout',
    method: 'post' // 或者 get 根据后端怎么写的
  });
}

/**
 * 获取验证码图片URL
 */
export function getCaptchaUrl() {
  const baseURL = import.meta.env.VITE_API_BASE_URL || '/index.php?m=api';
  return `${baseURL}&c=auth&a=captcha&t=${new Date().getTime()}`;
}
