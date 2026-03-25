import request from '@/utils/request';

/**
 * 提交投注注单
 * @param data.gameid  游戏ID
 * @param data.gamename 游戏名称
 * @param data.qishu   期数 (从 lottery currentState 获取)
 * @param data.wanfa   下注玩法字符串, 格式: "code@name@odds" 多注用 | 分隔
 * @param data.money   单注金额（以当前 unit 为单位, 默认元）
 * @param data.unit    单位: yuan | jiao | fen | li
 * @param data.roomid  房间号(如果游戏有房间限制时必传)
 */
export function submitBet(data: {
  gameid: number | string;
  gamename: string;
  qishu: string;
  wanfa: string;
  money: number;
  unit?: string;
  roomid?: number;
}) {
  return request({
    url: '&c=bet&a=submit',
    method: 'post',
    data
  });
}

/**
 * 获取历史注单
 */
export function getBetHistory(params: {
  gameid?: number;
  qishu?: string;
  page?: number;
  limit?: number;
}) {
  return request({
    url: '&c=bet&a=history',
    method: 'get',
    params
  });
}
