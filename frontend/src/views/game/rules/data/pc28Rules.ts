import { RulesGroup } from './rulesInterfaces'

export const pc28Rules: RulesGroup[] = [
  {
    id: 'mixed',
    name: '混合玩法',
    icon: 'fas fa-layer-group',
    items: [
      {
        name: '大 / 小',
        desc: '三个球号码之和（0\u201e27），总和≥14为"大"，总和≤13为"小"，出现豹子不计大小。',
        win: '总和 ≥ 14 → 大；总和 ≤ 13 → 小',
        example: '开出 5,6,7 合计18 → 大中奖',
        accent: 'blue'
      },
      {
        name: '单 / 双',
        desc: '三个球号码之和为奇数是"单"，偶数是"双"，出现豹子不计单双。',
        win: '总和为奇数 → 单；总和为偶数 → 双',
        example: '合计15（奇数）→ 单中奖',
        accent: 'blue'
      },
      {
        name: '大单 / 大双 / 小单 / 小双',
        desc: '组合类型：总和≥14且奇数为"大单"，总和≥14且偶数为"大双"，总和≤13且奇数为"小单"，总和≤13且偶数为"小双"。',
        win: '总和同时满足大小和单双条件',
        example: '合计16（大且双）→ 大双中奖',
        accent: 'blue'
      },
      {
        name: '极大',
        desc: '三个球号码之和大于或等于22时投注"极大"的注单视为中奖，赔率较高。',
        win: '总和 ≥ 22',
        example: '开出 7,8,9 合计24 → 极大中奖',
        accent: 'orange'
      },
      {
        name: '极小',
        desc: '三个球号码之和小于或等于6时投注"极小"的注单视为中奖，赔率较高。',
        win: '总和 ≤ 6',
        example: '开出 0,2,3 合计5 → 极小中奖',
        accent: 'orange'
      },
      {
        name: '豹子',
        desc: '三个球号码完全相同（出现几率极低），赔率极高。',
        win: '三球号码完全相同（如0,0,0 或 5,5,5）',
        example: '开出 3,3,3 → 豹子中奖（赔率约60倍）',
        accent: 'gold'
      },
      {
        name: '红波 / 绿波 / 蓝波',
        desc: '按开奖总和归属颜色波段投注：\n红波（1,4,7,10,13,16,19,22,25）\n绿波（2,5,8,11,14,17,20,23,26）\n蓝波（0,3,6,9,12,15,18,21,24,27）',
        win: '总和落在所投颜色波段内',
        example: '合计14 → 绿波中奖',
        accent: 'green'
      }
    ]
  },
  {
    id: 'haoma',
    name: '特码',
    icon: 'fas fa-hashtag',
    items: [
      {
        name: '特码 0 ~ 27',
        desc: '精确投注三球之和（0\u201e27），开奖总和与所投数字完全相同视为中奖，赔率极高。',
        win: '总和 = 所投特码号码',
        example: '投注特码14，开出 5,4,5 合计14 → 中奖',
        accent: 'purple'
      }
    ]
  }
]
