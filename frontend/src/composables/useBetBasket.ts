/**
 * useBetBasket.ts
 * 管理下注购物篮: 号码选择/取消、金额配置、注单汇总
 */
import { ref, computed } from 'vue'

export function useBetBasket() {
  const selections = ref<Record<string, (number | string)[]>>({})

  const chips = [10, 50, 100, 500, 1000]
  const selectedChip = ref(10)
  const customAmount = ref<number | ''>('')
  const multiplier = ref(1)
  const moneyUnit = ref(1)
  const moneyModes = [
    { label: '元', val: 1 },
    { label: '角', val: 0.1 },
    { label: '分', val: 0.01 },
    { label: '厘', val: 0.001 }
  ]
  const moneyUnitName = computed(() => {
    const map: Record<number, string> = { 1: 'yuan', 0.1: 'jiao', 0.01: 'fen', 0.001: 'li' }
    return map[moneyUnit.value] || 'yuan'
  })

  const totalBets = computed(() => {
    let count = 0
    for (const key in selections.value) count += selections.value[key].length
    return count
  })

  const totalAmount = computed(() => {
    const chip = customAmount.value || selectedChip.value
    const raw = totalBets.value * Number(chip) * multiplier.value * moneyUnit.value
    return parseFloat(raw.toFixed(3))
  })

  const toggleBall = (rowId: string, number: number | string) => {
    if (!selections.value[rowId]) selections.value[rowId] = []
    const index = selections.value[rowId].indexOf(number)
    if (index > -1) {
      selections.value[rowId].splice(index, 1)
    } else {
      selections.value[rowId].push(number)
    }
  }

  const isSelected = (rowId: string, number: number | string) =>
    selections.value[rowId]?.includes(number)

  const quickSelect = (row: any, type: string) => {
    selections.value[row.id] = []
    const newSelects: (number | string)[] = []
    
    if (row.range) {
        const min = row.range[0]
        const max = row.range[1]
        const mid = (min + max) / 2
        
        for (let i = min; i <= max; i++) {
        if (type === '大' && i > mid) newSelects.push(i)
        if (type === '小' && i < mid) newSelects.push(i)
        if (type === '单' && i % 2 !== 0) newSelects.push(i)
        if (type === '双' && i % 2 === 0) newSelects.push(i)
        }
    } else if (row.codes) {
        // Fallback or ignore for code-based rows
    }
    
    if (type !== '清') selections.value[row.id] = newSelects
  }

  const clearAll = (onClear?: () => void) => {
    selections.value = {}
    if (onClear) onClear()
  }

  return {
    selections, chips, selectedChip, customAmount, multiplier,
    moneyUnit, moneyModes, moneyUnitName, totalBets, totalAmount,
    toggleBall, isSelected, quickSelect, clearAll
  }
}
