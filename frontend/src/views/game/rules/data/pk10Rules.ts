import { RulesGroup } from './rulesInterfaces'

export const pk10Rules: RulesGroup[] = [
  {
    id: 'ranking',
    name: '冠亚军组合',
    icon: 'fas fa-trophy',
    items: [
      {
        name: '冠军车号指定',
        desc: '从1~10号车中选择一个号码投注冠军名次，开奖结果冠军车号与所投号码相同视为中奖。',
        win: '开奖冠军车号 = 所投号码',
        example: '投注冠军 [5]，开奖结果第一名为5号车 → 中奖',
        accent: 'gold'
      },
      {
        name: '亚军车号指定',
        desc: '从1~10号车中选择一个号码投注亚军名次，开奖结果亚军车号与所投号码相同视为中奖。',
        win: '开奖亚军车号 = 所投号码',
        example: '投注亚军 [3]，开奖结果第二名为3号车 → 中奖',
        accent: 'gold'
      },
      {
        name: '第三名~第五名指定',
        desc: '同理，选择第3、4、5名对应的车号，开奖结果对应名次的车号与所投号码相同视为中奖。',
        win: '开奖对应名次车号 = 所投号码',
        accent: 'gold'
      }
    ]
  },
  {
    id: '1-5',
    name: '1-5名两面',
    icon: 'fas fa-balance-scale',
    items: [
      {
        name: '大 / 小',
        desc: '对第1~5名的车号下注大或小。车号大于或等于6为"大"，小于或等于5为"小"。',
        win: '车号≥6 → 大中奖；车号≤5 → 小中奖',
        example: '投注冠军大，冠军开出7号车 → 中奖',
        accent: 'blue'
      },
      {
        name: '单 / 双',
        desc: '对第1~5名的车号下注单或双。车号为奇数叫单（1,3,5,7,9），偶数叫双（2,4,6,8,10）。',
        win: '车号为奇数 → 单中奖；车号为偶数 → 双中奖',
        example: '投注亚军单，亚军开出9号车 → 中奖',
        accent: 'blue'
      }
    ]
  },
  {
    id: 'dragon',
    name: '龙虎',
    icon: 'fas fa-dragon',
    items: [
      {
        name: '冠军 龙/虎',
        desc: '"第一名"车号大于"第十名"车号视为【龙】中奖，反之小于视为【虎】中奖，相等则不中奖。',
        win: '第1名车号 > 第10名 → 龙；第1名 < 第10名 → 虎',
        example: '第1名=8，第10名=3 → 龙中奖',
        accent: 'red'
      },
      {
        name: '亚军 龙/虎',
        desc: '"第二名"车号大于"第九名"车号视为【龙】中奖，反之视为【虎】中奖。',
        win: '第2名车号 > 第9名 → 龙；反之 → 虎',
        accent: 'red'
      },
      {
        name: '第三名 龙/虎',
        desc: '"第三名"车号大于"第八名"车号视为【龙】，反之视为【虎】。',
        win: '第3名 > 第8名 → 龙；反之 → 虎',
        accent: 'red'
      },
      {
        name: '第四/五名 龙/虎',
        desc: '第4名 vs 第7名，第5名 vs 第6名，规则同上。',
        win: '名次车号较大 → 龙；较小 → 虎',
        accent: 'red'
      }
    ]
  },
  {
    id: 'gyh',
    name: '冠亚和',
    icon: 'fas fa-plus-circle',
    items: [
      {
        name: '冠亚和值',
        desc: '冠军车号 + 亚军车号 = 冠亚和值（范围3~19），可投注具体和值点数。',
        win: '冠亚和值 = 所投点数',
        example: '冠军5 + 亚军8 = 和值13，投注GY13 → 中奖',
        accent: 'purple'
      },
      {
        name: '冠亚和 大/小',
        desc: '冠亚和值大于11视为"大"，小于或等于11视为"小"，无平局。',
        win: '和值 > 11 → 大；和值 ≤ 11 → 小',
        accent: 'purple'
      },
      {
        name: '冠亚和 单/双',
        desc: '冠亚和值为奇数视为"单"，偶数视为"双"。',
        win: '和值为奇数 → 单；和值为偶数 → 双',
        example: '冠军4 + 亚军5 = 9（单）',
        accent: 'purple'
      }
    ]
  }
]
