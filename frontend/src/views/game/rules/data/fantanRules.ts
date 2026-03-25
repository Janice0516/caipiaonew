import { RulesGroup } from './rulesInterfaces'

export const fantanRules: RulesGroup[] = [
  {
    id: 'fan',
    name: '番',
    icon: 'fas fa-coins',
    items: [
      {
        name: '番（1~4）',
        desc: '将开彩珠数除以4，取其余数作为开奖结果；余数为0时，结果为4番。投注对应番号即可。',
        win: '珠数除以4的余数 = 所投番号（余0即4）',
        example: '开彩27颗珠，27÷4=6余3 → 开3番，投3番中奖',
        accent: 'gold'
      }
    ]
  },
  {
    id: 'ds',
    name: '单双',
    icon: 'fas fa-circle-half-stroke',
    items: [
      {
        name: '单（甲）',
        desc: '开彩结果为1番或3番视为"单"中奖。',
        win: '开彩结果为1番或3番',
        example: '开3番 → 单中奖',
        accent: 'blue'
      },
      {
        name: '双（乙）',
        desc: '开彩结果为2番或4番视为"双"中奖。',
        win: '开彩结果为2番或4番',
        example: '开4番 → 双中奖',
        accent: 'blue'
      }
    ]
  },
  {
    id: 'nian',
    name: '念',
    icon: 'fas fa-link',
    items: [
      {
        name: '念（含让）',
        desc: '从4个番号中选择2个连续番号投注，开彩结果为所投两个番号之一则视为中奖，但其中一个番号赔率会让（含让番）。',
        win: '开彩结果为所投两个番号之一（其中一个会让本金）',
        example: '投1念2：开1番全赢，开2番让1赔，其余不中',
        accent: 'purple'
      }
    ]
  },
  {
    id: 'zheng',
    name: '正',
    icon: 'fas fa-dot-circle',
    items: [
      {
        name: '正（含捎）',
        desc: '投注一个番号的同时"捎"（顺带）相邻的番号。开彩结果恰好为所指定番号视为全赢，结果为相邻捎番视为小赢（让本金），其余不中。',
        win: '恰好开所投番号 → 全赢；开相邻捎番 → 小赢',
        example: '投正1：开1番全赢，开2或4番让1赔，其余不中',
        accent: 'cyan'
      }
    ]
  },
  {
    id: 'jiao',
    name: '角',
    icon: 'fas fa-arrows-turn-right',
    items: [
      {
        name: '角',
        desc: '投注两个相邻番号（如4-1、1-2、2-3、3-4），开彩结果为所投两番号之一视为中奖。赔率低于单番，但中奖机率更高。',
        win: '开彩结果为两个番号中的任意一个',
        example: '投4-1角：开4番或1番均中奖',
        accent: 'orange'
      }
    ]
  }
]
