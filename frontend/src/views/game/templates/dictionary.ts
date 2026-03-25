// 全局公用的字典格式化与颜色获取逻辑

export const formatCodeName = (code: string): string => {
  const map: Record<string, string> = {
    // ── 通用两面 ──────────────────────────────
    'DA': '大', 'X': '小', 'D': '单', 'S': '双',
    'DD': '大单', 'DS': '大双', 'XD': '小单', 'XS': '小双',

    // ── PC28 波色 / 极值 ─────────────────────
    'RED': '红波', 'BLUE': '蓝波', 'GREEN': '绿波',
    'HB': '红波', 'LB': '蓝波', 'LVB': '绿波',
    'JD': '极大', 'JX': '极小', 'BZ': '豹子',

    // ── CQSSC 总和 ───────────────────────────
    'ZHDA': '总和大', 'ZHX': '总和小', 'ZHD': '总和单', 'ZHS': '总和双',
    // CQSSC 龙虎和
    'ZHL': '龙', 'ZHH': '虎', 'ZHHE': '和',

    // ── CQSSC 逐球大小单双（第1~5球）─────────────
    // 同样适用于 LHC 两面各球 (1DA~6S 格式相同)
    '1DA': '大', '1X': '小', '1D': '单', '1S': '双',
    '2DA': '大', '2X': '小', '2D': '单', '2S': '双',
    '3DA': '大', '3X': '小', '3D': '单', '3S': '双',
    '4DA': '大', '4X': '小', '4D': '单', '4S': '双',
    '5DA': '大', '5X': '小', '5D': '单', '5S': '双',
    '6DA': '大', '6X': '小', '6D': '单', '6S': '双',
    // LHC 第7球（特码）两面
    '7DA': '特码大', '7X': '特码小', '7D': '特码单', '7S': '特码双',

    // ── CQSSC 前中后三 ───────────────────────
    'QBZ': '豹子', 'QSZ': '顺子', 'QDZ': '对子', 'QBS': '半顺', 'QZL': '杂六',
    'ZBZ': '豹子', 'ZSZ': '顺子', 'ZDZ': '对子', 'ZBS': '半顺', 'ZZL': '杂六',
    'HBZ': '豹子', 'HSZ': '顺子', 'HDZ': '对子', 'HBS': '半顺', 'HZL': '杂六',

    // ── PK10 龙虎（位置格式 {n}L/{n}H）──────────
    '1L': '龙', '1H': '虎', '2L': '龙', '2H': '虎',
    '3L': '龙', '3H': '虎', '4L': '龙', '4H': '虎', '5L': '龙', '5H': '虎',
    // PK10 冠亚和大小单双
    'GYDA': '冠亚和大', 'GYX': '冠亚和小', 'GYD': '冠亚和单', 'GYS': '冠亚和双',

    // ── LHC 生肖 ────────────────────────────
    'SHU': '鼠', 'NIU': '牛', 'HU': '虎', 'TU': '兔', 'LONG': '龙', 'SHE': '蛇',
    'MA': '马', 'YANG': '羊', 'HOU': '猴', 'JI': '鸡', 'GOU': '狗', 'ZHU': '猪',
    'JQ': '家禽', 'YS': '野兽',

    // ── 番摊 ────────────────────────────────
    'F1': '一番', 'F2': '二番', 'F3': '三番', 'F4': '四番',
    'Z1': '一正', 'Z2': '二正', 'Z3': '三正', 'Z4': '四正',
    '1L2':'1念2', '1L3':'1念3', '1L4':'1念4', '2L1':'2念1', '2L3':'2念3', '2L4':'2念4',
    '3L1':'3念1', '3L2':'3念2', '3L4':'3念4', '4L1':'4念1', '4L2':'4念2', '4L3':'4念3',
    '12J': '1/2角', '23J': '2/3角', '34J': '3/4角', '41J': '4/1角',

    // ── 合数 / 头尾（LHC扩展）────────────────
    'HS-DA': '合数大', 'HS-X': '合数小', 'HS-D': '合数单', 'HS-S': '合数双',
    'T0':'0头', 'T1':'1头', 'T2':'2头', 'T3':'3头', 'T4':'4头',
    'W0':'0尾', 'W1':'1尾', 'W2':'2尾', 'W3':'3尾', 'W4':'4尾', 'W5':'5尾', 'W6':'6尾', 'W7':'7尾', 'W8':'8尾', 'W9':'9尾',
  }

  // 兜底：PC28 特码 S0~S27 → "0点"..."27点"
  if (code.startsWith('S') && !isNaN(Number(code.substring(1)))) {
    return `${code.substring(1)}点`
  }
  // 兜底：PK10 冠亚和值 GY3~GY19 → "和3"..."和19"
  if (code.startsWith('GY') && !isNaN(Number(code.substring(2)))) {
    return `和值${code.substring(2)}`
  }
  return map[code] || code
}


export const getCodeColorClass = (code: any): string => {
  const num = parseInt(code)
  if (!isNaN(num) && num.toString() === String(code)) {
    return `ball-${num}`
  }
  const text = formatCodeName(code)
  if (text.includes('大') || text.includes('龙')) return 'ball-8'
  if (text.includes('小') || text.includes('虎')) return 'ball-2'
  if (text.includes('单')) return 'ball-4'
  if (text.includes('双')) return 'ball-5'
  if (text.includes('和')) return 'ball-10'
  const charSum = text.split('').reduce((a, b) => a + b.charCodeAt(0), 0)
  return `ball-${(charSum % 10) + 1}`
}

export const getDefaultNumericBallClass = (n: number): string => {
  const mod = n > 10 ? (n % 10 === 0 ? 10 : n % 10) : n
  if (n === 0) return 'ball-0'
  return `ball-${mod}`
}

const LHC_RED = [1, 2, 7, 8, 12, 13, 18, 19, 23, 24, 29, 30, 34, 35, 40, 45, 46]
const LHC_BLUE = [3, 4, 9, 10, 14, 15, 20, 21, 25, 26, 31, 32, 36, 37, 41, 42, 47, 48]

export const getBallColorClass = (n: number, template: string): string => {
  if (template === 'lhc') {
    if (LHC_RED.includes(n)) return 'bg-red-500 text-white'
    if (LHC_BLUE.includes(n)) return 'bg-blue-500 text-white'
    return 'bg-green-500 text-white'
  }
  const colors = [
    'bg-yellow-400 text-black', 'bg-blue-500 text-white', 'bg-gray-700 text-white',
    'bg-orange-500 text-white', 'bg-cyan-400 text-black', 'bg-indigo-600 text-white',
    'bg-gray-400 text-black', 'bg-red-500 text-white', 'bg-red-800 text-white',
    'bg-green-500 text-white'
  ]
  const mod = n > 10 ? (n % 10 === 0 ? 10 : n % 10) : n
  return colors[mod - 1] || 'bg-gray-800 text-white'
}

export const getGlowColor = (n: number, template: string): string => {
  if (template === 'lhc') {
    if (LHC_RED.includes(n)) return '#EF4444'
    if (LHC_BLUE.includes(n)) return '#3B82F6'
    return '#10B981'
  }
  const colors = ['#FBBF24', '#3B82F6', '#374151', '#F97316', '#22D3EE', '#4F46E5', '#9CA3AF', '#EF4444', '#991B1B', '#10B981']
  const mod = n > 10 ? (n % 10 === 0 ? 10 : n % 10) : n
  return colors[mod - 1] || '#9CA3AF'
}

export const calcLhcZodiacMap = (): Record<string, number[]> => {
  const zods = ['鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪']
  const currentYear = new Date().getFullYear()
  const currentZodiacIndex = (currentYear - 2020) % 12
  const map: Record<string, number[]> = {}
  for (let i = 0; i < 12; i++) map[zods[i]] = []
  for (let num = 1; num <= 49; num++) {
    let animalIdx = (currentZodiacIndex - (num - 1)) % 12
    if (animalIdx < 0) animalIdx += 12
    map[zods[animalIdx]].push(num)
  }
  return map
}

