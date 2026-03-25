import { LotteryTemplate, PlayTab, PlayRow, SubmitPayload } from './interfaces'

// 位置映射（rowId → 第几名）
const POS_MAP: Record<string, number> = {
  'champion': 1, 'runner_up': 2, 'rank3': 3, 'rank4': 4, 'rank5': 5,
  'rank6': 6,   'rank7': 7,    'rank8': 8, 'rank9': 9, 'rank10': 10,
  'dt1': 1,     'dt2': 2,      'dt3': 3,   'dt4': 4,   'dt5': 5
}

const POS_NAMES: Record<number, string> = {
  1:'冠军', 2:'亚军', 3:'第三名', 4:'第四名', 5:'第五名',
  6:'第六名', 7:'第七名', 8:'第八名', 9:'第九名', 10:'第十名'
}

// 大小单双 投注代码 → {db键后缀, 玩法中文名}
const BET_MAP: Record<string, {suffix: string, name: string}> = {
  'DA': { suffix: 'DA', name: '大' },
  'X':  { suffix: 'X',  name: '小' },
  'D':  { suffix: 'D',  name: '单' },
  'S':  { suffix: 'S',  name: '双' },
}

export const pk10Template: LotteryTemplate = {
  getTabs(): PlayTab[] {
    return [
      { id: 'ranking', name: '冠亚军组合' },
      { id: '1-5',     name: '1-5名' },
      { id: '6-10',    name: '6-10名' },
      { id: 'dragon',  name: '龙虎' },
      { id: 'gyh',     name: '冠亚和' },
    ]
  },

  getPlayRows(tab: string): PlayRow[] {
    if (tab === 'ranking') {
      return [
        { id: 'champion',  title: '冠军',   range: [1, 10] },
        { id: 'runner_up', title: '亚军',   range: [1, 10] },
        { id: 'rank3',     title: '第三名', range: [1, 10] },
        { id: 'rank4',     title: '第四名', range: [1, 10] },
        { id: 'rank5',     title: '第五名', range: [1, 10] },
      ]
    }
    if (tab === '1-5') {
      return [
        { id: 'champion',  title: '冠军',   codes: ['DA','X','D','S'] },
        { id: 'runner_up', title: '亚军',   codes: ['DA','X','D','S'] },
        { id: 'rank3',     title: '第三名', codes: ['DA','X','D','S'] },
        { id: 'rank4',     title: '第四名', codes: ['DA','X','D','S'] },
        { id: 'rank5',     title: '第五名', codes: ['DA','X','D','S'] },
      ]
    }
    if (tab === '6-10') {
      return [
        { id: 'rank6',  title: '第六名',  range: [1, 10] },
        { id: 'rank7',  title: '第七名',  range: [1, 10] },
        { id: 'rank8',  title: '第八名',  range: [1, 10] },
        { id: 'rank9',  title: '第九名',  range: [1, 10] },
        { id: 'rank10', title: '第十名',  range: [1, 10] },
      ]
    }
    if (tab === 'dragon') {
      return [
        { id: 'dt1', title: '冠军',   codes: ['1L','1H'] },
        { id: 'dt2', title: '亚军',   codes: ['2L','2H'] },
        { id: 'dt3', title: '第三名', codes: ['3L','3H'] },
        { id: 'dt4', title: '第四名', codes: ['4L','4H'] },
        { id: 'dt5', title: '第五名', codes: ['5L','5H'] },
      ]
    }
    if (tab === 'gyh') {
      return [
        { id: 'gy_lm',  title: '冠亚和两面', codes: ['GYDA','GYX','GYD','GYS'] },
        { id: 'gy_num', title: '和值点数',   codes: ['GY3','GY4','GY5','GY6','GY7','GY8','GY9','GY10','GY11','GY12','GY13','GY14','GY15','GY16','GY17','GY18','GY19'] },
      ]
    }
    return []
  },

  buildSubmitCode(rowId: string, n: number | string): SubmitPayload {
    const pos = POS_MAP[rowId] || 1
    const posName = POS_NAMES[pos] || '冠军'

    // ── 龙虎 Tab：n 已经是 "1L"/"1H" 形式 ───────────────────────────────
    if (rowId.startsWith('dt')) {
      const code = String(n)   // "1L" / "1H"
      const isLong = code.endsWith('L')
      return { code, displayName: posName, wanfaCode: isLong ? '龙' : '虎' }
    }

    // ── 冠亚和 Tab ──────────────────────────────────────────────────────
    if (rowId.startsWith('gy')) {
      const code = String(n)   // "GYDA" / "GY3" etc.
      let betName = code
      if (code === 'GYDA')        betName = '冠亚大'
      else if (code === 'GYX')    betName = '冠亚小'
      else if (code === 'GYD')    betName = '冠亚单'
      else if (code === 'GYS')    betName = '冠亚双'
      else if (code.startsWith('GY')) betName = code.slice(2)   // "GY11" → "11"
      return { code, displayName: '冠亚', wanfaCode: betName }
    }

    // ── 大小单双 Tab (1-5 / 6-10) ────────────────────────────────────────
    const nStr = String(n)
    if (BET_MAP[nStr]) {
      const { suffix, name } = BET_MAP[nStr]
      // db odds key: e.g. "1DA", "2X"
      return { code: `${pos}${suffix}`, displayName: posName, wanfaCode: name }
    }

    // ── 具体号码排名（冠亚军组合 1-10 号数）─────────────────────────────────
    // wanfaCode = 数字字符串（backend default case: $hm == wfarr[0]）
    // odds key: 尝试 "1A1" 格式，db 没有则 GamePlay.vue fallback 到 1.98
    const num = Number(n)
    return { code: `${pos}A${num}`, displayName: posName, wanfaCode: String(num) }
  }
}
