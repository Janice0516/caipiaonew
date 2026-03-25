import { RulesGroup } from './rulesInterfaces'

export const liuboRules: RulesGroup[] = [
  {
    id: 'basic',
    name: '基本玩法',
    icon: 'fas fa-dice-six',
    items: [
      {
        name: '大 / 小',
        desc: '三颗骰子之和（4~10）为"小"，（11~17）为"大"，出现豹子不计大小。',
        win: '总和 11~17 → 大；总和 4~10 → 小',
        example: '三骰开出 4,5,6 合计15 → 大中奖',
        accent: 'blue'
      },
      {
        name: '单 / 双',
        desc: '三颗骰子之和为奇数叫"单"，偶数叫"双"，出现豹子不计单双。',
        win: '总和为奇数 → 单；总和为偶数 → 双',
        example: '合计11（奇数）→ 单中奖',
        accent: 'blue'
      },
      {
        name: '豹子',
        desc: '三颗骰子点数完全相同（如1,1,1 或 5,5,5），出现豹子时大小单双均无效，赔率最高。',
        win: '三骰点数完全相同',
        example: '三骰开出 2,2,2 → 豹子中奖',
        accent: 'gold'
      }
    ]
  },
  {
    id: 'hz',
    name: '和值',
    icon: 'fas fa-hashtag',
    items: [
      {
        name: '和值 4~17',
        desc: '精确投注三骰总点数，开出总和与所投数值完全一致即中奖，出现豹子另计。',
        win: '总和 = 所投数值（且非豹子）',
        example: '投注和值=9，三骰开 2,3,4 合计9 → 中奖',
        accent: 'purple'
      }
    ]
  },
  {
    id: 'single',
    name: '单骰',
    icon: 'fas fa-dice-one',
    items: [
      {
        name: '单个点数（1~6）',
        desc: '投注1~6中的某个点数，三颗骰子中有几颗骰子开出该点数，赔率相应增加（出现1颗赔1倍，2颗赔2倍，3颗赔3倍）。',
        win: '三颗骰子中至少有1颗开出所投点数',
        example: '投注点数3，三骰开出 3,3,5 → 两颗中奖，赔率×2',
        accent: 'cyan'
      }
    ]
  }
]
