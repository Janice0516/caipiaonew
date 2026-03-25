import { LotteryTemplate, PlayTab, PlayRow, SubmitPayload } from './interfaces'
import { formatCodeName } from './dictionary'

export const pc28Template: LotteryTemplate = {
  getTabs(): PlayTab[] {
    return [
      { id: 'mixed', name: '混合' },  // 旧版：大小单双+极大极小+豹子+波色全在一 Tab
      { id: 'haoma', name: '特码' }   // 旧版：S0~S27 精确点数
    ]
  },
  getPlayRows(tab: string): PlayRow[] {
    if (tab === 'mixed') {
      return [
        // 基础两面
        { id: 'dxds', title: '大小单双', codes: ['DA', 'X', 'D', 'S'] },
        // 组合两面
        { id: 'combo', title: '大单双/小单双', codes: ['DD', 'DS', 'XD', 'XS'] },
        // 极大极小+豹子（旧版混合 Tab 包含此三项）
        { id: 'extra', title: '极大极小豹子', codes: ['JD', 'JX', 'BZ'] },
        // 波色（旧版混合 Tab 包含红绿蓝波）
        { id: 'bs', title: '波色', codes: ['HB', 'LVB', 'LB'] }
      ]
    }
    if (tab === 'haoma') {
      return [
        { id: 'bz_num', title: '特码', range: [0, 27] }
      ]
    }
    return []
  },
  buildSubmitCode(rowId: string, n: number | string): SubmitPayload {
    if (rowId === 'bz_num') {
      return { code: `S${n}`, displayName: `${n}` }
    }
    return { code: String(n), displayName: formatCodeName(String(n)) }
  }
}
