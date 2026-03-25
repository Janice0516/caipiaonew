import { LotteryTemplate, PlayTab, PlayRow, SubmitPayload } from './interfaces'
import { formatCodeName } from './dictionary'

export const lhcTemplate: LotteryTemplate = {
  getTabs(): PlayTab[] {
    return [
      { id: 'tm', name: '特码' },
      { id: 'sx', name: '生肖' },
      { id: 'bm', name: '两面' }  // 旧版：第1~6球及特码的大小单双
    ]
  },
  getPlayRows(tab: string): PlayRow[] {
    if (tab === 'tm') {
      return [
        { id: 'tm_num', title: '特码', range: [1, 49] },
        { id: 'tm_dxds', title: '大小单双', codes: ['DA', 'X', 'D', 'S'] }
      ]
    }
    if (tab === 'sx') {
      return [
        { id: 'sx_tm', title: '特肖', codes: ['SHU', 'NIU', 'HU', 'TU', 'LONG', 'SHE', 'MA', 'YANG', 'HOU', 'JI', 'GOU', 'ZHU'] }
      ]
    }
    if (tab === 'bm') {
      // 旧版两面：第1~6球各自大/小/单/双（code格式：{球位}D / {球位}DA / {球位}S / {球位}X）
      // 特码大小单双：7D/7DA/7S/7X（与 tm_dxds 相同，这里合并展示）
      return [
        { id: 'bm_ball1', title: '第一球', codes: ['1DA', '1X', '1D', '1S'] },
        { id: 'bm_ball2', title: '第二球', codes: ['2DA', '2X', '2D', '2S'] },
        { id: 'bm_ball3', title: '第三球', codes: ['3DA', '3X', '3D', '3S'] },
        { id: 'bm_ball4', title: '第四球', codes: ['4DA', '4X', '4D', '4S'] },
        { id: 'bm_ball5', title: '第五球', codes: ['5DA', '5X', '5D', '5S'] },
        { id: 'bm_ball6', title: '第六球', codes: ['6DA', '6X', '6D', '6S'] },
        { id: 'bm_tm', title: '特码', codes: ['7DA', '7X', '7D', '7S'] }
      ]
    }
    return []
  },
  buildSubmitCode(rowId: string, n: number | string): SubmitPayload {
    if (rowId === 'tm_num') {
      return { code: `7A${n}`, displayName: String(n) }
    }
    if (rowId === 'tm_dxds') {
      return { code: `7${n}`, displayName: formatCodeName(String(n)) }
    }
    if (rowId === 'sx_tm') {
      return { code: String(n), displayName: formatCodeName(String(n)) }
    }
    // 两面 Tab: bm_ball1~bm_ball6 和 bm_tm 的 code 已经是完整的后端 code（如 1DA, 2X, 7D 等）
    if (rowId.startsWith('bm_')) {
      return { code: String(n), displayName: formatCodeName(String(n)) }
    }
    return { code: String(n), displayName: formatCodeName(String(n)) }
  },
  getNumericBallClass(n: number): string {
    const red = [1, 2, 7, 8, 12, 13, 18, 19, 23, 24, 29, 30, 34, 35, 40, 45, 46]
    const blue = [3, 4, 9, 10, 14, 15, 20, 21, 25, 26, 31, 32, 36, 37, 41, 42, 47, 48]
    if (red.includes(n)) return 'ball-8'
    if (blue.includes(n)) return 'ball-2'
    return 'ball-10'
  }
}
