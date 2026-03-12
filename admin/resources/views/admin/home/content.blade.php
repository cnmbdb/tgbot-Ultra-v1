{{-- 仪表盘内容（被 index 引入，不要 @extends） --}}
<div class="dashboard-next">
    <div class="row">
        <div class="col-lg-12">
            <div class="dashboard-cards">

                <div class="next-card">
                    <div class="next-card-header">
                        <span class="next-card-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" x2="20" y1="19" y2="19"/></svg></span>
                        <h3 class="next-card-title">内置命令</h3>
                        <span class="next-card-badge">可直接发给机器人</span>
                    </div>
                    <div class="next-card-body">
                        <ul class="next-list">
                            <li><strong>z0</strong>：查询欧意C2C实时汇率。z0 查所有，z1 只查银行卡，z2 只查支付宝，z3 只查微信</li>
                            <li><strong>统计</strong>：统计当天、当月、历史总兑换订单情况</li>
                            <li><strong>能量统计</strong>：统计当天、当月、历史总能量订单情况</li>
                            <li><strong>预支给xxx</strong>：仅闪兑钱包→TG通知对象可发起，私聊机器人。格式：预支给xxxxxxx 15（地址+trx数量，默认15）</li>
                            <li><strong>能量给xxx 65000 0</strong>：仅能量平台→TG管理员可发起，私聊机器人。格式：能量给xxx 65000 0（0=1小时,1=1天,3=3天）。也可用「能量强制给xxx」不校验TRX</li>
                            <li><strong>查授权xxx</strong>：查询波场地址授权。例：查授权TYASr5UV6HEcXatwdFQfmUqMUxHLS</li>
                            <li><strong>激活地址xxx</strong>：仅转1 TRX激活地址，仅闪兑钱包→TG通知对象可发起，私聊。例：激活地址TYASr5UV6HEcwdFQfmLVUqMUxHLS</li>
                            <li><strong>下发trx/下发usdt</strong>：仅闪兑钱包→TG通知对象可发起，私聊。格式：下发trx xxx 10</li>
                            <li><strong>授权波场/多签波场 地址 私钥 [权限ID,默认0]</strong></li>
                            <li><strong>直接发波场地址</strong>：可查询余额、转账、多签等信息</li>
                            <li><strong>增加笔数 / 绑定笔数</strong>：给地址增加笔数或绑定用户ID，发命令给机器人可查格式</li>
                        </ul>
                    </div>
                </div>

                <div class="next-card">
                    <div class="next-card-header">
                        <span class="next-card-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg></span>
                        <h3 class="next-card-title">回复消息与广告内容样式</h3>
                    </div>
                    <div class="next-card-body">
                        <p class="next-text-muted">以下标签可用于回复内容、广告内容中：</p>
                        <ul class="next-list next-list-code">
                            <li>粗体：<code>&lt;b&gt;文字&lt;/b&gt;</code> 或 <code>&lt;strong&gt;文字&lt;/strong&gt;</code></li>
                            <li>斜体：<code>&lt;i&gt;文字&lt;/i&gt;</code> 或 <code>&lt;em&gt;文字&lt;/em&gt;</code></li>
                            <li>底线：<code>&lt;u&gt;文字&lt;/u&gt;</code> 或 <code>&lt;ins&gt;文字&lt;/ins&gt;</code></li>
                            <li>删除线：<code>&lt;s&gt;文字&lt;/s&gt;</code> 或 <code>&lt;del&gt;文字&lt;/del&gt;</code></li>
                            <li>遮挡码：<code>&lt;span class="tg-spoiler"&gt;文字&lt;/span&gt;</code></li>
                            <li>超链接：<code>&lt;a href="链接"&gt;文字&lt;/a&gt;</code></li>
                            <li>TG用户链接：<code>&lt;a href="tg://user?id=123456789"&gt;文字&lt;/a&gt;</code></li>
                            <li>等宽(可复制)：<code>&lt;code&gt;文字&lt;/code&gt;</code>、<code>&lt;pre&gt;文字&lt;/pre&gt;</code></li>
                            <li>代码块：<code>&lt;pre&gt;&lt;code class="language-xxx"&gt;文字&lt;/code&gt;&lt;/pre&gt;</code></li>
                        </ul>
                        <p class="next-text-warning">所有不属于标签的 <code>&lt;</code> <code>&gt;</code> <code>&amp;</code> 需替换为 HTML 实体：&amp;lt; &amp;gt; &amp;amp;</p>
                    </div>
                </div>

                <div class="next-card">
                    <div class="next-card-header">
                        <span class="next-card-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
                        <h3 class="next-card-title">内置变量</h3>
                    </div>
                    <div class="next-card-body">
                        <p class="next-text-muted">在回复内容中直接填写变量名即可替换：</p>
                        <ul class="next-list next-list-vars">
                            <li><code>${trxusdtrate}</code> 闪兑汇率（根据机器人对应闪兑地址）</li>
                            <li><code>${trx10usdtrate}</code> 闪兑汇率（10U）</li>
                            <li><code>${trx100usdtrate}</code> 闪兑汇率（100U）</li>
                            <li><code>${trx1000usdtrate}</code> 闪兑汇率（1000U）</li>
                            <li><code>${trxusdtwallet}</code> 闪兑钱包地址</li>
                            <li><code>${trxusdtshownotes}</code> 闪兑钱包显示说明</li>
                            <li><code>${tgbotadmin}</code> 机器人管理员名</li>
                            <li><code>${tgbotname}</code> 机器人名</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.dashboard-next { padding: 20px 0; }
.dashboard-cards { display: flex; flex-direction: column; gap: 24px; }
.next-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
    overflow: hidden;
}
.next-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-bottom: 1px solid #e5e7eb;
}
.next-card-icon { font-size: 20px; color: #3b82f6; }
.next-card-icon .fa { font-size: inherit; }
.next-card-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #111827;
}
.next-card-badge {
    margin-left: auto;
    font-size: 12px;
    color: #6b7280;
    background: #e5e7eb;
    padding: 4px 10px;
    border-radius: 20px;
}
.next-card-body { padding: 20px; }
.next-list {
    margin: 0;
    padding-left: 20px;
    list-style: disc;
    color: #374151;
    line-height: 1.8;
    font-size: 14px;
}
.next-list li { margin-bottom: 8px; }
.next-list li strong { color: #1d4ed8; }
.next-list-code code,
.next-list-vars code {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 13px;
    color: #1d4ed8;
}
.next-text-muted { color: #6b7280; margin-bottom: 12px; font-size: 14px; }
.next-text-warning { color: #b45309; font-size: 13px; margin-top: 12px; }
</style>
