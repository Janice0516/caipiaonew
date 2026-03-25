import request from '@/utils/request';

/**
 * 获取当前开奖状态 (当前期号，倒计时，上一期结果)
 * 极高频调用
 */
export function getCurrentState(gameid: number | string) {
  return request({
    url: `&c=lottery&a=current_state&gameid=${gameid}`,
    method: 'get'
  });
}

/**
 * 获取历史开奖记录 (走势图使用)
 */
export function getHistoryResults(gameid: number | string, limit: number = 20) {
  return request({
    url: `&c=lottery&a=history_results&gameid=${gameid}&limit=${limit}`,
    method: 'get'
  });
}
