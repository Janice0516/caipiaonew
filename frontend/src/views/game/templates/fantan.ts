import { LotteryTemplate, PlayTab, PlayRow, SubmitPayload } from './interfaces'
import { formatCodeName } from './dictionary'

export const fantanTemplate: LotteryTemplate = {
  getTabs(): PlayTab[] {
    return [
      { id: 'fantan', name: '番摊' }
    ]
  },
  getPlayRows(_tab: string): PlayRow[] {
    return [
      // 番1~4，旧版后端 code = F1/F2/F3/F4
      { id: 'fan', title: '番', range: [1, 4] },
      // 单双
      { id: 'dxds', title: '单双', codes: ['D', 'S'] },
      // 念：旧版 code 格式 1L2, 1L3... (L 分隔，不是 N)
      { id: 'nian', title: '念', codes: ['1L2', '1L3', '1L4', '2L1', '2L3', '2L4', '3L1', '3L2', '3L4', '4L1', '4L2', '4L3'] },
      // 角：旧版 code 12J/23J/34J/41J — 注意旧版是 41J 不是 14J
      { id: 'jiao', title: '角', codes: ['12J', '23J', '34J', '41J'] },
      // 正：旧版 Z1/Z2/Z3/Z4
      { id: 'zheng', title: '正', codes: ['Z1', 'Z2', 'Z3', 'Z4'] }
    ]
  },
  buildSubmitCode(rowId: string, n: number | string): SubmitPayload {
    const code = String(n)
    if (rowId === 'fan') {
      // 旧版后端 key = F1/F2/F3/F4，必须加 F 前缀
      return { code: `F${n}`, displayName: `番${n}` }
    }
    // 其他 row（念/角/正/单双）将 n 直接作为 code（已经是字符串形式的正确 code）
    return { code, displayName: formatCodeName(code) }
  }
}
