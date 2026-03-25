import { RulesGroup } from './rulesInterfaces'

export const lhcRules: RulesGroup[] = [
  {
    id: 'tm',
    name: '特码',
    icon: 'fas fa-circle',
    items: [
      {
        name: '特码 1~49',
        desc: '选择1\u201e49中任意号码下注，当期开奖特别号码（第7个球）与所投号码相同即中奖。赔率约48倍。',
        win: '开奖特别号码 = 所投号码',
        example: '投注特码33，开奖特别号为33 → 中奖',
        accent: 'gold'
      },
      {
        name: '特码 大/小',
        desc: '特别号码25~49为"特码大"，1~24为"特码小"。49为特别号码，通常另行规则。',
        win: '特别号25~49 → 特码大；1~24 → 特码小',
        example: '特别号开出28 → 特码大中奖',
        accent: 'blue'
      },
      {
        name: '特码 单/双',
        desc: '特别号码为奇数叫"特单"（1,3,5...），偶数叫"特双"（2,4,6...）。',
        win: '特别号为奇数 → 特单；偶数 → 特双',
        example: '特别号开出17 → 特单中奖',
        accent: 'blue'
      }
    ]
  },
  {
    id: 'sx',
    name: '生肖',
    icon: 'fas fa-yin-yang',
    items: [
      {
        name: '特肖',
        desc: '根据开奖特别号码所属的生肖进行投注。12个生肖（鼠、牛、虎、兔、龙、蛇、马、羊、猴、鸡、狗、猪），每种生肖对应4个或5个号码。',
        win: '开奖特别号码的生肖 = 所投生肖',
        example: '投注"马"，开奖特别号为马年号码 → 中奖',
        accent: 'green'
      }
    ]
  },
  {
    id: 'bm',
    name: '两面',
    icon: 'fas fa-th',
    items: [
      {
        name: '第一球~第六球 大/小',
        desc: '对第1~6个开奖号码分别投注大小。号码25~49为大，1~24为小。',
        win: '对应球位号码 ≥ 25 → 大；≤ 24 → 小',
        example: '投注第二球大，第二球开出30 → 中奖',
        accent: 'blue'
      },
      {
        name: '第一球~第六球 单/双',
        desc: '对第1~6个开奖号码分别投注单双。号码为奇数叫单，偶数叫双。',
        win: '对应球位号码为奇数 → 单；偶数 → 双',
        example: '投注第四球单，第四球开出11 → 中奖',
        accent: 'blue'
      },
      {
        name: '特码 大/小/单/双（两面）',
        desc: '与特码Tab的大小单双规则相同，对第7个球（特别号）进行大小单双投注。',
        win: '特别号满足所投大/小/单/双条件',
        accent: 'cyan'
      }
    ]
  }
]
