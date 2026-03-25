import request from '@/utils/request';

/**
 * 获取代理总览数据 (团队人数, 本月佣金等)
 */
export function getAgentSummary() {
  return request({
    url: '&c=agent&a=summary',
    method: 'get'
  });
}

/**
 * 获取下级会员列表
 */
export function getAgentMembers(params: {
  page?: number;
  limit?: number;
}) {
  return request({
    url: '&c=agent&a=members',
    method: 'get',
    params
  });
}

/**
 * 获取佣金明细列表
 */
export function getCommissionDetail(params: {
  page?: number;
  limit?: number;
}) {
  return request({
    url: '&c=agent&a=commission',
    method: 'get',
    params
  });
}

/**
 * 获取某个下级成员的详情（资金、奖金组）
 */
export function getMemberDetail(uid: number | string) {
  return request({
    url: '&c=agent&a=member_detail',
    method: 'get',
    params: { uid }
  });
}

/**
 * 修改下级玩家的奖金组
 */
export function updateMemberRebate(data: { uid: number | string; rebate: number }) {
  return request({
    url: '&c=agent&a=update_rebate',
    method: 'post',
    data
  });
}

/**
 * 获取专属邀请链接列表
 */
export function getInviteLinks() {
  return request({
    url: '&c=agent&a=invite_links',
    method: 'get'
  });
}

/**
 * 创建专属邀请链接
 */
export function createInviteLink(data: { rebate: number }) {
  return request({
    url: '&c=agent&a=create_invite_link',
    method: 'post',
    data
  });
}

/**
 * 作废专属邀请链接
 */
export function deleteInviteLink(data: { id: number | string }) {
  return request({
    url: '&c=agent&a=delete_invite_link',
    method: 'post',
    data
  });
}
