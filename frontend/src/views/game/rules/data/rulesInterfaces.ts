// 玩法说明数据接口定义

export interface RuleItem {
  name: string      // 玩法名
  desc: string      // 规则说明
  win: string       // 中奖条件
  example?: string  // 投注示例
  accent: 'blue' | 'gold' | 'green' | 'red' | 'purple' | 'cyan' | 'orange'
}

export interface RulesGroup {
  id: string
  name: string
  icon: string      // FontAwesome class
  items: RuleItem[]
}
