import { RulesGroup } from './rulesInterfaces'

export const k3Rules: RulesGroup[] = [
  {
    id: 'dxds',
    name: '大小单双',
    icon: 'fas fa-dice',
    items: [
      {
        name: '大 / 小',
        desc: '三个骰子点数之和（4\u201e17为有效投注范围，不含豹子），总和≥11为"大"，总和≤10为"小"，豹子不计结果。',
        win: '总和 ≥ 11 → 大；总和 ≤ 10 → 小（豹子取消）',
        example: '三骰开出 4,5,3 合计12 → 大中奖',
        accent: 'blue'
      },
      {
        name: '单 / 双',
        desc: '三骰总和为奇数叫"单"，偶数叫"双"，豹子不计结果。',
        win: '总和为奇数 → 单；总和为偶数 → 双（豹子取消）',
        example: '三骰开出 1,2,2 合计5（单）→ 单中奖',
        accent: 'blue'
      },
      {
        name: '大单 / 大双 / 小单 / 小双',
        desc: '组合投注，"大单"即总和≥11且为奇数，"大双"即总和≥11且为偶数，以此类推。豹子不计结果。',
        win: '总和同时满足大小和单双条件',
        example: '合计13（大且单）→ 大单中奖',
        accent: 'blue'
      }
    ]
  },
  {
    id: 'special',
    name: '豹子',
    icon: 'fas fa-star',
    items: [
      {
        name: '豹子',
        desc: '三个骰子点数完全相同（如1,1,1 / 3,3,3 / 6,6,6）即为豹子，赔率最高。出现豹子则大小单双均视为不中奖。',
        win: '三骰点数完全相同',
        example: '三骰开出 5,5,5 → 豹子中奖（赔率约100倍）',
        accent: 'gold'
      }
    ]
  },
  {
    id: 'hz',
    name: '和值点数',
    icon: 'fas fa-hashtag',
    items: [
      {
        name: '和值 4 ~ 17',
        desc: '投注三骰总和的精确数值（4\u201e17），豹子不算任何数。出现豹子时，所有和值投注均取消（除非特定规则另行规定）。',
        win: '总和 = 所投点数（且非豹子）',
        example: '投注和值=9，三骰开出 2,3,4 合计9 → 中奖',
        accent: 'purple'
      }
    ]
  }
]
