import request from '@/utils/request';

/**
 * 获取充值渠道列表
 */
export function getRechargeChannels() {
  return request({
    url: '&c=finance&a=recharge_channels',
    method: 'get'
  });
}

/**
 * 提交充值申请
 */
export function submitRecharge(data: {
  pay_type: string;
  money: number;
  [key: string]: any;
}) {
  return request({
    url: '&c=finance&a=recharge_submit',
    method: 'post',
    data
  });
}

/**
 * 提交提现申请
 */
export function submitWithdraw(data: {
  money: number;
  account_type: number; // 1:银联 2:数字货币
  money_password?: string;
  comment?: string;
  [key: string]: any;
}) {
  return request({
    url: '&c=finance&a=withdraw_submit',
    method: 'post',
    data
  });
}

/**
 * 获取资金流水记录
 */
export function getTransactionList(params: {
  page?: number;
  limit?: number;
  type?: number;
}) {
  return request({
    url: '&c=finance&a=record_list',
    method: 'get',
    params
  });
}
