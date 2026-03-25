/**
 * useGameState.ts
 * 负责从后端拉取当前期数信息、倒计时管理和历史记录
 */
import { ref } from 'vue'
import { getCurrentState, getHistoryResults } from '@/api/lottery'

export function useGameState(gameId: string | string[]) {
  const gameInfo = ref({
    id: gameId,
    name: '加载中...',
    currentIssue: '...',
    nextIssueTime: Date.now() + 60000,
    serverTime: Date.now(),
    template: 'pk10',
    lastIssue: {
      issue: '...',
      numbers: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
    },
    odds: {
      'ranking': 9.95,
      '1-5': 9.95,
      '6-10': 9.95,
      'dragon': 1.99
    } as Record<string, number>,
    history: [] as any[]
  })

  const timeLeft = ref(0)
  let localTimer: number | null = null

  const fetchCurrentState = async () => {
    try {
      const res: any = await getCurrentState(gameId as string)
      if (res && res.currentIssue) {
        gameInfo.value.name = res.gameName || '游戏'
        gameInfo.value.currentIssue = res.currentIssue
        if (res.odds) gameInfo.value.odds = res.odds
        const now = Math.floor(Date.now() / 1000)
        const diff = Number(res.endTime) - now
        timeLeft.value = diff > 0 ? diff : 0
        if (res.template) gameInfo.value.template = res.template
        gameInfo.value.lastIssue.issue = res.lastIssue
        if (res.lastNumbers?.length > 0) {
          gameInfo.value.lastIssue.numbers = res.lastNumbers.map(Number)
        }
      }
    } catch (err) {
      console.error('Failed to fetch lotto state', err)
    }
  }

  const fetchHistory = async () => {
    try {
      const res: any = await getHistoryResults(gameId as string, 20)
      // API returns { list: [{qishu, haoma, sendtime}] }
      const list = res?.list || res?.records || []
      if (list.length > 0) {
        gameInfo.value.history = list.map((r: any) => {
          const haoma: string = r.haoma || ''
          const nums = haoma ? haoma.split(',').map(Number) : []
          const isPk10 = nums.length >= 10
          // 冠亚和（pk10：位置0+1；时时彩：所有数之和）
          const sum = isPk10 ? (nums[0] + nums[1]) : nums.reduce((a, b) => a + b, 0)
          // 龙虎（pk10：冠军 vs 第十名；其他游戏不适用）
          const dragon = isPk10 ? (nums[0] > nums[9] ? '龙' : '虎') : ''
          return {
            issue: String(r.qishu),
            numbers: nums,
            sum,
            dragon,
          }
        })
      }
    } catch (err) {
      console.error('Failed to fetch history', err)
    }
  }

  let wsStateConn: WebSocket | null = null;
  const initWebSocket = () => {
    if (wsStateConn) return;
    const wsUrl = `ws://${window.location.hostname}:8080/api/ws`;
    wsStateConn = new WebSocket(wsUrl);
    wsStateConn.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        if (data && data.action === 'draw_update') {
          // Force update upon Go Engine push
          setTimeout(() => {
            fetchCurrentState();
            fetchHistory();
          }, 800);
        }
      } catch (e) {}
    };
    wsStateConn.onclose = () => {
      wsStateConn = null;
      setTimeout(initWebSocket, 5000);
    };
  };

  const startTimer = () => {
    fetchCurrentState()
    initWebSocket()
    localTimer = window.setInterval(() => {
      if (timeLeft.value > 0) {
        timeLeft.value--
      } else {
        // Reduced fallback polling spam
        fetchCurrentState()
      }
    }, 2500)
  }

  const stopTimer = () => {
    if (localTimer) clearInterval(localTimer)
    if (wsStateConn) {
      wsStateConn.onclose = null; // Prevent auto-reconnect on normal unmount
      wsStateConn.close();
      wsStateConn = null;
    }
  }

  return { gameInfo, timeLeft, startTimer, stopTimer, fetchCurrentState, fetchHistory }
}
