import { LotteryTemplate } from './interfaces'
import { cqsscTemplate } from './cqssc'
import { k3Template } from './k3'
import { pc28Template } from './pc28'
import { lhcTemplate } from './lhc'
import { fantanTemplate } from './fantan'
import { liuboTemplate } from './liubo'
import { pk10Template } from './pk10'

// 暂不支持的彩种模板 — 无Vue3模板文件（a11x5/klsf等）
const UNSUPPORTED_TEMPLATES = ['a11x5', 'klsf']

export const getLotteryTemplate = (templateName: string): LotteryTemplate | null => {
  // 不支持的彩种返回 null，GamePlay.vue 会显示友好提示
  if (UNSUPPORTED_TEMPLATES.includes(templateName)) return null
  switch (templateName) {
    case 'cqssc': return cqsscTemplate
    case 'k3': return k3Template
    case 'pc28': return pc28Template
    case 'lhc': return lhcTemplate
    case 'fantan': return fantanTemplate
    case 'liubo': return liuboTemplate
    case 'pk10': return pk10Template
    default: return pk10Template
  }
}

export * from './dictionary'
export * from './interfaces'
