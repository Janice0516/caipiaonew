import { LotteryTemplate, PlayTab, PlayRow, SubmitPayload } from './interfaces'
import { formatCodeName } from './dictionary'

export const cqsscTemplate: LotteryTemplate = {
  getTabs(): PlayTab[] {
    return [
      { id: 'all', name: '两面' },      // 旧版：总和+龙虎和+逐球大小单双
      { id: 'haoma', name: '1-5名' },   // 旧版：5球各自0~9号码
      { id: 'qzh', name: '前中后' }     // 旧版：前三/中三/后三 豹子顺子对子半顺杂六
    ]
  },
  getPlayRows(tab: string): PlayRow[] {
    if (tab === 'all') {
      return [
        // 总和大小单双 + 龙虎和（旧版两面 Tab，code：ZHDA/ZHX/ZHD/ZHS/ZHL/ZHH/ZHHE）
        { id: 'zh', title: '总和', codes: ['ZHDA', 'ZHX', 'ZHD', 'ZHS'] },
        { id: 'lhh', title: '龙虎和', codes: ['ZHL', 'ZHH', 'ZHHE'] },
        // 第1~5球各自大小单双（旧版code：{球位}DA/{球位}X/{球位}D/{球位}S）
        { id: 'b1', title: '第一球', codes: ['1DA', '1X', '1D', '1S'] },
        { id: 'b2', title: '第二球', codes: ['2DA', '2X', '2D', '2S'] },
        { id: 'b3', title: '第三球', codes: ['3DA', '3X', '3D', '3S'] },
        { id: 'b4', title: '第四球', codes: ['4DA', '4X', '4D', '4S'] },
        { id: 'b5', title: '第五球', codes: ['5DA', '5X', '5D', '5S'] }
      ]
    }
    if (tab === 'haoma') {
      // 1-5名：5球各自选0~9号码
      return [
        { id: 'w1', title: '第一球', range: [0, 9] },
        { id: 'w2', title: '第二球', range: [0, 9] },
        { id: 'w3', title: '第三球', range: [0, 9] },
        { id: 'w4', title: '第四球', range: [0, 9] },
        { id: 'w5', title: '第五球', range: [0, 9] }
      ]
    }
    if (tab === 'qzh') {
      // 前中后三：豹子/顺子/对子/半顺/杂六（旧版code前缀 Q/Z/H + 字母）
      return [
        { id: 'q3', title: '前三', codes: ['QBZ', 'QSZ', 'QDZ', 'QBS', 'QZL'] },
        { id: 'z3', title: '中三', codes: ['ZBZ', 'ZSZ', 'ZDZ', 'ZBS', 'ZZL'] },
        { id: 'h3', title: '后三', codes: ['HBZ', 'HSZ', 'HDZ', 'HBS', 'HZL'] }
      ]
    }
    return []
  },
  buildSubmitCode(rowId: string, n: number | string): SubmitPayload {
    const rowToPosSSC: Record<string, number> = { 'w1': 1, 'w2': 2, 'w3': 3, 'w4': 4, 'w5': 5 }
    // 号码台：{球位}A{数字}
    if (rowToPosSSC[rowId] !== undefined) {
      return { code: `${rowToPosSSC[rowId]}A${n}`, displayName: String(n) }
    }
    // 其余所有 row（总和/龙虎和/逐球大小单双/前中后三）的 code 已内嵌在 codes 数组中
    return { code: String(n), displayName: formatCodeName(String(n)) }
  }
}
