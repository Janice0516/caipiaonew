import request from '@/utils/request';

/**
 * 获取个人资料
 */
export function getUserProfile() {
  return request({
    url: '&c=user&a=profile',
    method: 'get'
  });
}

/**
 * 获取高频余额
 */
export function getUserBalance() {
  return request({
    url: '&c=user&a=balance',
    method: 'get'
  });
}

/**
 * 修改登录密码
 */
export function updatePassword(data: any) {
  return request({
    url: '&c=user&a=password',
    method: 'post',
    data
  });
}

/**
 * 修改资金密码
 */
export function updateFundPassword(data: any) {
  return request({
    url: '&c=user&a=fund_password',
    method: 'post',
    data
  });
}

/**
 * 完善用户信息 (姓名, 银行卡, 数字货币)
 */
export function updateUserInfo(data: any) {
  return request({
    url: '&c=user&a=update_info',
    method: 'post',
    data
  });
}
