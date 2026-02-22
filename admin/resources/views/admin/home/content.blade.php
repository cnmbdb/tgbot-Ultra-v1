@extends('layouts.admin.app')
@section('nav-status-home', 'active')

@section('contents')
    
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <div class="layui-form-item">
                        <div class="layui-form-item">
                        <div style="font-size:25px;color:rebeccapurple;margin:10px;">
                            内置命令：<span style="color:red;font-size:16px">内置命令可以直接发给机器人</span>
                        </div>
                        <div style="font-size:16px;">
                            <span style="color:red">z0</span>：查询欧意C2C实时汇率。z0是查所有，z1是只查银行卡，z2是只查支付宝，z3是只查微信
                            <br><br>
                            <span style="color:red">统计</span>：可以统计当天，当月，历史总的兑换订单情况。
                            <br><br>
                            <span style="color:red">能量统计</span>：可以统计当天，当月，历史总的能量订单情况。
                            <br><br>
                            <span style="color:red">预支给xxx</span>：只能是闪兑钱包->TG通知对象(收款)，才能发起该命令且只能私聊发给机器人。格式为：预支给xxxxxxx 15。xxxxxxx是钱包地址，15是trx数量，如果不输入后面的数量，则默认预支15个trx。比如  预支给TYASr5UV6HEcXatwdFQfmUqMUxHLS 15
                            <br><br>
                            <span style="color:red">能量给xxx 65000 0</span>：只能是能量平台->TG管理员用户ID，才能发起该命令且只能私聊发给机器人。格式为：能量给xxx 65000 0。xxx是钱包地址，65000是能量数量，0表示1小时,1表示1天,3表示3天。如果仅输入能量给xxx，则默认给xxx 65000能量 1小时使用权限。比如  能量给TYASr5UV6HEcXatwdFQfmLqMUxHLS 或者 能量给TYASr5UV6HEcXatwdFQfmUqMUxHLS 65000 0
                            <br>PS: 也可以使用命令：能量强制给xxx  表示不校验TRX是否足够等条件<br><br>
                            <span style="color:red">查授权xxx</span>：查询波场地址的授权。格式为：查授权xxx   xxx是钱包地址，比如  查授权TYASr5UV6HEcXatwdFQfmUqMUxHLS
                            <br><br>
                            <span style="color:red">激活地址xxx</span>：激活波场地址，仅转1 TRX激活地址，只能是闪兑钱包->TG通知对象(收款)，才能发起该命令且只能私聊发给机器人。格式为：激活地址xxx   xxx是钱包地址，比如  激活地址TYASr5UV6HEcwdFQfmLVUqMUxHLS
                            <br><br>
                            <span style="color:red">下发trx/下发usdt</span>：只能是闪兑钱包->TG通知对象(收款)，才能发起该命令且只能私聊发给机器人。格式为：下发trx xxx 10 。xxx是钱包地址,10是下发数量，比如  下发trx TYASr5UV6HEcXatwdFQfVUqMUxHLS 10
                            <br><br>
                            <span style="color:red">授权波场/多签波场 地址 私钥 [权限ID,默认0]：例如 授权波场/多签波场 TYASr5UV6HEcXatwdFQLVUqMUxHLS 790c2e063e35e0f71acd065bfbe12b8a186565d6e61</span>
                            <br><br>
                            <span style="color:red">直接发波场地址，可查询波场地址余额，转账，多签等信息</span>
                            <br><br>
                            <span style="color:red">增加笔数：可以给地址增加笔数次数。 绑定笔数：可以给地址绑定用户ID，通知消息。发送命令给机器人可查看命令格式</span>
                        </div>
                    </div>
                    <div style="font-size:25px;color:rebeccapurple;margin:10px;">
                        关于回复消息内容，广告内容里面的样式说明：
                    </div>
                    <div style="font-size:16px;">
                        粗体：&lt;b&gt;文字内容&lt;/b&gt; 或者 &lt;strong&gt;文字内容&lt;/strong&gt;
                        <br>斜体：&lt;i&gt;文字内容&lt;/i&gt; 或者 &lt;em&gt;文字内容&lt;/em&gt;
                        <br>底线：&lt;u&gt;文字内容&lt;/u&gt; 或者 &lt;ins&gt;文字内容&lt;/ins&gt;
                        <br>删除线：&lt;s&gt;文字内容&lt;/s&gt; 或者 &lt;strike&gt;文字内容&lt;/strike&gt; 或者 &lt;del&gt;文字内容&lt;/del&gt;
                        <br>遮挡码：&lt;span class="tg-spoiler"&gt;文字内容&lt;/span&gt; 或者 &lt;tg-spoiler&gt;文字内容&lt;/tg-spoiler&gt;
                        <br>超链接：&lt;a href="链接地址"&gt;文字内容&lt;/a&gt;
                        <br>TG用户链接：&lt;a href="tg://user?id=123456789"&gt;文字内容&lt;/a&gt;
                        <br>等宽(点击复制)：&lt;code&gt;文字内容&lt;/code&gt;
                        <br>多行等宽(点击复制)：&lt;pre&gt;文字内容&lt;/pre&gt;
                        <br>代码块(点击复制)：&lt;pre&gt;&lt;code class="language-python"&gt;文字内容&lt;/code&gt;&lt;/pre&gt;
                        <br><br>
                        <span style="color:red">所有不属于标记或HTML实体的<，>和&符号必须替换为相应的HTML实体(< 用 &amp;lt; 替换。 > 用 &amp;gt;替换。 & 用 &amp;amp;替换)</span>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div style="font-size:25px;color:rebeccapurple;margin:10px;">
                        关于内置变量：
                    </div>
                    <div style="font-size:16px;">
                        <span style="color:red">${trxusdtrate}</span>  表示闪兑汇率，会根据机器人对应的闪兑地址，查找usdt兑换trx的汇率。
                        <br><br>
                        <span style="color:red">${trx10usdtrate}</span>  表示闪兑汇率（10U），会根据机器人对应的闪兑地址，查找usdt兑换trx的汇率。
                        <br><br>
                        <span style="color:red">${trx100usdtrate}</span>  表示闪兑汇率（100U），会根据机器人对应的闪兑地址，查找usdt兑换trx的汇率。
                        <br><br>
                        <span style="color:red">${trx1000usdtrate}</span>  表示闪兑汇率（1000U），会根据机器人对应的闪兑地址，查找usdt兑换trx的汇率。
                        <br><br>
                        <span style="color:red">${trxusdtwallet}</span>  表示闪兑钱包地址，会根据机器人查找对应的闪兑地址。
                        <br><br>
                        <span style="color:red">${trxusdtshownotes}</span>  表示闪兑钱包显示说明，会根据机器人查找对应的闪兑地址的闪兑说明。
                        <br><br>
                        <span style="color:red">${tgbotadmin}</span>  表示机器人管理员名，会根据机器人查找对应的管理员名。
                        <br><br>
                        <span style="color:red">${tgbotname}</span>  表示机器人名，会根据机器人查找对应的机器人名。
                        <br><br>
                        <span style="color:red">变量名字直接在回复内容中填写即可</span>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
@endsection

@section('scripts')
   
@endsection


