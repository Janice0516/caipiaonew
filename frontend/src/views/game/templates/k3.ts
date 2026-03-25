import { LotteryTemplate, PlayTab, PlayRow, SubmitPayload } from './interfaces'
import { formatCodeName } from './dictionary'

export const k3Template: LotteryTemplate = {
  getTabs(): PlayTab[] {
    return [
      { id: 'hz', name: '和值' },
      { id: '3tx', name: '三通选' }
    ]
  },
  getPlayRows(tab: string): PlayRow[] {
    if (tab === 'hz') {
      return [
        { id: 'hz_num', title: '和值', range: [4, 17] },
        { id: 'hz_dxds', title: '混合', codes: ['DA', 'X', 'D', 'S', 'DD', 'DS', 'XD', 'XS', 'BZ'] }
      ]
    }
    if (tab === '3tx') {
      return [
        { id: '3tx', title: '三同号通选', codes: ['BZ'] }
      ]
    }
    // Default fallback
    return [
      { id: 'hz_num', title: '和值', range: [4, 17] },
      { id: 'hz_dxds', title: '混合', codes: ['DA', 'X', 'D', 'S', 'DD', 'DS', 'XD', 'XS', 'BZ'] }
    ]
  },
  buildSubmitCode(rowId: string, n: number | string): SubmitPayload {
    if (rowId === 'hz_num') {
      return { code: `S${n}`, displayName: `${n}` }
    }
    return { code: String(n), displayName: formatCodeName(String(n)) }
  }
}
