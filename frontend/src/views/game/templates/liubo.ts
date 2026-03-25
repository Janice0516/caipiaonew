import { LotteryTemplate, PlayTab, PlayRow, SubmitPayload } from './interfaces'
import { formatCodeName } from './dictionary'

export const liuboTemplate: LotteryTemplate = {
  getTabs(): PlayTab[] {
    return [
      { id: 'basic', name: '大小单双' },
      { id: 'mixed', name: '综合' }
    ]
  },
  getPlayRows(tab: string): PlayRow[] {
    if (tab === 'basic') {
      return [
        { id: 'dxds', title: '大小单双', codes: ['DA', 'X', 'D', 'S', 'DD', 'XD', 'DS', 'XS'] }
      ]
    }
    if (tab === 'mixed') {
      return [
        { id: 'bs', title: '波色', codes: ['RED', 'BLUE', 'GREEN'] },
        { id: 'sx', title: '生肖', codes: ['SHU', 'NIU', 'HU', 'TU', 'LONG', 'SHE', 'MA', 'YANG', 'HOU', 'JI', 'GOU', 'ZHU'] },
        { id: 'jqys', title: '家禽野兽', codes: ['JQ', 'YS'] },
        { id: 'hs', title: '合数', codes: ['HS-DA', 'HS-X', 'HS-D', 'HS-S'] },
        { id: 'tws', title: '头尾数', codes: ['T0','T1','T2','T3','T4', 'W0','W1','W2','W3','W4','W5','W6','W7','W8','W9'] }
      ]
    }
    return []
  },
  buildSubmitCode(_rowId: string, n: number | string): SubmitPayload {
    return { code: String(n), displayName: formatCodeName(String(n)) }
  }
}
