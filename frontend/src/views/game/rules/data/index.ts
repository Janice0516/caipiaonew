import { RulesGroup } from './rulesInterfaces'
import { pk10Rules } from './pk10Rules'
import { cqsscRules } from './cqsscRules'
import { k3Rules } from './k3Rules'
import { pc28Rules } from './pc28Rules'
import { lhcRules } from './lhcRules'
import { fantanRules } from './fantanRules'
import { liuboRules } from './liuboRules'

export type { RulesGroup, RuleItem } from './rulesInterfaces'

const rulesMap: Record<string, RulesGroup[]> = {
  pk10: pk10Rules,
  cqssc: cqsscRules,
  k3: k3Rules,
  pc28: pc28Rules,
  lhc: lhcRules,
  fantan: fantanRules,
  liubo: liuboRules,
}

export const getRulesData = (template: string): RulesGroup[] => {
  return rulesMap[template] ?? rulesMap['pk10']
}
