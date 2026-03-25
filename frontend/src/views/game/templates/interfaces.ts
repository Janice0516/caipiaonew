export interface PlayTab {
  id: string;
  name: string;
}

export interface PlayRow {
  id: string;
  title: string;
  range?: [number, number];
  codes?: string[];
}

export interface SubmitPayload {
  code: string;        // odds lookup key (e.g. '1DA' for pk10 大)
  displayName: string; // display text or position name (e.g. '冠军')
  wanfaCode?: string;  // when set: actual bet-type for submission (e.g. '大'), overrides code in wanfa string
}

export interface LotteryTemplate {
  /**
   * 返回该彩种的所有顶部选项卡
   */
  getTabs(): PlayTab[];

  /**
   * 根据当前选中的选项卡返回渲染区域的胶囊布局规则
   */
  getPlayRows(tab: string): PlayRow[];

  /**
   * 获取提交下注时，该号码球在该彩种中真实的数据库 Code 和 展示文本
   * @param rowId 行识别码
   * @param n 选中的号码或字符
   */
  buildSubmitCode(rowId: string, n: number | string): SubmitPayload;

  /**
   * 特殊处理：计算出该号码球对应的颜色/样式 Class。可选。
   */
  getNumericBallClass?(n: number): string;
}
