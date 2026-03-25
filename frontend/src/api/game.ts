import request from '@/utils/request';

/**
 * 获取大厅游戏列表
 */
export function getLobbyList() {
  return request({
    url: '&c=game&a=lobby_list',
    method: 'get'
  });
}

/**
 * 获取指定游戏的房间与准入配置
 */
export function getRoomConfig(gameid: number | string) {
  return request({
    url: `&c=game&a=room_config&gameid=${gameid}`,
    method: 'get'
  });
}

/**
 * 获取指定游戏的玩法和赔率数据
 */
export function getOddsData(gameid: number | string) {
  return request({
    url: `&c=game&a=odds_data&gameid=${gameid}`,
    method: 'get'
  });
}

/**
 * 获取指定游戏的历史开奖结果
 */
export function getHistoryResults(gameid: number | string, limit = 20) {
  return request({
    url: `&c=lottery&a=history_results&gameid=${gameid}&limit=${limit}`,
    method: 'get'
  });
}
