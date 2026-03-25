import request from '@/utils/request';

export interface ActivityItem {
  id: number;
  title: string;
  desc: string;
  tag: string;
  type: number;       // 1=签到 2=充值任务 3=投注任务 4=展示型
  reward: number;
  target: number;
  cycle: number;      // 1=每日 2=每周 3=一次性
  gradient: string;
  progress: number;   // 0-100
  completed: boolean;
  claimed: boolean;
  time_label: string;
}

export interface ActivityListResponse {
  tasks: ActivityItem[];
  banners: ActivityItem[];
  today_recharge: number;
  today_bet: number;
  done_count: number;
  total_count: number;
}

/**
 * 获取活动列表（含用户当前完成状态）
 */
export function getActivityList() {
  return request({
    url: '&c=activity&a=get_list',
    method: 'get',
  });
}

/**
 * 领取活动奖励
 */
export function claimActivityReward(activity_id: number) {
  return request({
    url: '&c=activity&a=claim',
    method: 'post',
    data: { activity_id },
  });
}
