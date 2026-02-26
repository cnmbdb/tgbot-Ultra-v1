@extends('layouts.admin.app')
@section('nav-status-energy', 'active')
@section('nav-status-energy-platform', 'active')
@section('contents')

    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索平台用户UID：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="platform_uid" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加能量平台') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加能量平台',
                                {
                                'poll_group':['轮询组','select',[PollGroup],''],
                                'platform_name':['能量平台','select',[PlatformName],''],
                                'platform_uid':['平台用户UID','text',''],
                                'platform_apikey':['平台用户apikey','text','','NL-API平台填写API密码，其他平台见说明'],
                                'permission_id':['签名权限ID','text','0','仅自己质押代理需要'],
                                'alert_platform_balance':['余额预警值','text','','0表示不预警'],
                                'tg_notice_bot_rid':['TG通知机器人','select',[botData],''],
                                'tg_notice_obj':['TG通知对象','text','','多个用英文逗号隔开'],
                                'seq_sn':['轮询排序','text','0',''],
                                'comments':['备注','textarea','','NL-API平台可填写：nl_api_url=https://tgnl-home.hfz.pw']
                                },
                                '{{route("admin.energy.platform.add")}}',get_online_data)">添加能量平台
                    </button>
                @endif
           
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 第一个能量平台(neee.cc)：添加之前，需要先前往 <a href="https://www.neee.cc/#/?code=112764" target="_blank">https://www.neee.cc/#/?code=112764</a> 注册用户，获取用户UID和apikey。使用前需要先给平台账号充值<p>
                    2. 第二个能量平台(RentEnergysBot)：添加之前，需要先前往 <a href="https://t.me/RentEnergysBot" target="_blank">https://t.me/RentEnergysBot</a> ，选择 API访问 即可生成密钥，选择这个平台，在添加的时候，平台用户UID 填写为-即可。使用前需要先给平台账号充值<p>
                    3. <b>第三个(自己质押代理)</b>：添加之前，需要先前往<a href="https://www.tronlink.org/" target="_blank">TronLink钱包</a>质押能量。添加时，平台用户UID填写为质押的钱包地址，平台用户apikey填写为质押的钱包私钥。如果能量地址多签了，则需要修改签名权限ID，不然签名不成功<p>
                    4. <b>平台已关闭：</b>第四个能量平台(trongas.io)：添加之前，需要先前往 <a href="https://trongas.io/home" target="_blank">https://trongas.io/home</a> 注册用户，平台用户UID填写为用户账号，平台用户apikey填写为用户密码。使用前需要先给平台账号充值<p>
                    5. <b>平台已关闭：</b>第五个能量平台(机器人开发代理)：使用开发者的能量，平台用户UID填写TG用户的ID即可，同时需要关注开发者的机器人。该模式的能量价格详情咨询开发者(仅有笔数模式和1小时闪租65000和131000能量，注意笔数代理模式只能选自动)！<p>
                    6. 第6个能量平台(Sohu搜狐)：添加之前，需要先前往 <a href="https://t.me/sohu" target="_blank">https://t.me/sohu</a> ，联系搜狐客服开通账号，并把服务器IP发送给客服添加白名单，否则无法请求。添加时，平台用户UID可随意填写一个自己的标识，平台用户apikey填写为api令牌。<p>
                    7. <b>第7个能量平台(NL-API)</b>：连接tgnl-home能量池系统。添加时，平台用户UID填写为tgnl-home生成的API用户名，平台用户apikey填写为tgnl-home生成的API密码。备注可填写：nl_api_url=https://tgnl-home.hfz.pw（可选，默认使用环境变量配置）<p>
                    8. 使用之前需要先往平台充值trx余额或者自己质押，仅 <b>TG管理员用户ID</b> 对应的用户才可使用能量相关命令，命令见主页<p>
                    9. 平台用户余额每5秒更新一次，余额低于预警值时发送tg消息告警(10分钟告警一次)，当平台选择为自己质押代理时，平台用户余额表示自己地址剩余的可用能量数量<p>
                    10. 轮询排序：数值越大，优先级越高。平台用户余额大于0的时候，才会轮询。如果是自己质押，则平台用户余额需要大于质押能量数量
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.energy.platform.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'poll_group_val',align:'center'}">轮询组</th>
                                <th lay-data="{field:'platform_name_val',align:'center'}">能量平台</th>
                                <th lay-data="{align:'center', templet:'#status',width:200}">状态</th>
                                <th lay-data="{field:'platform_uid',align:'center'}">平台用户UID</th>
                                <th lay-data="{field:'platform_apikey',align:'center'}">平台用户apikey</th>
                                <th lay-data="{field:'platform_balance',align:'center'}">平台用户余额</th>
                                <th lay-data="{field:'alert_platform_balance',align:'center'}">余额预警值</th>
                                <th lay-data="{field:'tg_notice_obj',align:'center'}">TG通知对象</th>
                                <th lay-data="{field:'bot_username',align:'center'}">TG通知机器人</th>
                                <th lay-data="{field:'seq_sn',align:'center'}">轮询排序</th>
                                <th lay-data="{field:'last_alert_time',align:'center'}">余额预警时间</th>
                                <!--<th lay-data="{field:'create_time',align:'center'}">创建时间</th>-->
                                <!--<th lay-data="{field:'update_time',align:'center'}">修改时间</th>-->
                                <th lay-data="{field:'comments',align:'center'}">备注</th>
                                <th lay-data="{toolbar:'#tpl_opt',align:'center',width:260}">操作</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!--操作:修改，删除-->
    <script type="text/html" id="tpl_opt">
        <div class="layui-btn-group">
            @if( auth('admin')->user()->can('修改能量平台') || auth('admin')->user()->hasrole('超级管理员') )
                {{-- 余额充值按钮（具体校验在后端完成） --}}
                <button class="layui-btn layui-btn-sm layui-btn-normal" lay-event="nlapi_recharge" data-rid="@{{d.rid}}" data-uid="@{{d.platform_uid}}">余额充值</button>
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改能量平台',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'poll_group':['轮询组','select',[PollGroup],'@{{d.poll_group}}'],
                        'platform_name':['能量平台','select',[PlatformName],'@{{d.platform_name}}'],
                        'platform_uid':['平台用户UID','text','@{{d.platform_uid}}'],
                        'alert_platform_balance':['余额预警值','text','@{{d.alert_platform_balance}}'],
                        'tg_notice_bot_rid':['TG通知机器人','select',[botData],'@{{d.tg_notice_bot_rid}}'],
                        'tg_notice_obj':['TG通知对象','text','@{{d.tg_notice_obj}}'],
                        'seq_sn':['轮询排序','text','@{{d.seq_sn}}'],
                        'comments':['备注','textarea','@{{d.comments}}']
                    },
                    '{{route("admin.energy.platform.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('修改apikey') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" style="background-color:blueviolet !important" onclick="javascript:tools_add('修改apikey',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'platform_name_val':['能量平台','span','@{{d.platform_name_val}}'],
                        'platform_uid':['平台用户UID','span','@{{d.platform_uid}}'],
                        'platform_apikey':['平台用户apikey','text','@{{d.platform_apikey}}'],
                        'permission_id':['签名权限ID','text','@{{d.permission_id}}']
                    },
                    '{{route("admin.energy.platform.updateapikey")}}',get_online_data);">APIKEY
                </button>
            @endif
            @if( auth('admin')->user()->can('删除能量平台') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.energy.platform.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
        </div>
    </script>
    
    <!--操作:修改状态-->
    <script type="text/html" id="status">
        <input type="checkbox" name="status" value="@{{d.status}}" id="@{{ d.rid }}" lay-skin="switch" lay-text="开启|关闭"
                   lay-filter="status" contents="@{{d.status}}" @{{ d.status== 0 ? 'checked' : '' }}><br/>
    </script>

    <script>
        var PlatformName = @json($PlatformName, JSON_UNESCAPED_UNICODE);
        var Status = @json($Status, JSON_UNESCAPED_UNICODE);
        var PollGroup = @json($PollGroup, JSON_UNESCAPED_UNICODE);
        var botData = @json($botData, JSON_UNESCAPED_UNICODE);
    
        layui.use(['table', 'layer'], function () {
            var table = layui.table,
                form = layui.form;

            //搜索
            form.on('submit(go)', function (obj) {
                table.reload('userTable', {
                    page: {
                        curr: 1
                    },
                    where: obj.field
                });
                return false;
            });
            
            //解决开关取消不回显的问题,修改状态
            form.on('switch(status)', function (obj) {
                confirm_opt(
                    () => {
                        form_func('{{route("admin.energy.platform.change_status")}}', {
                            'rid': obj.elem.id,
                            'status': obj.value,
                            'contents': $(this).attr('contents'),
                        }, get_online_data);
                    },
                    () => {
                        obj.elem.checked = !obj.elem.checked;
                        form.render('checkbox');
                    }, '要修改功能项吗？'
                );
            });

            // 监听操作列事件（用于 NL-API 余额充值）
            table.on('tool(userTable)', function (obj) {
                var data = obj.data;
                var layEvent = obj.event;

                if (layEvent === 'nlapi_recharge') {
                    if (data.platform_name != 7) {
                        layui.layer.msg('仅支持 NL-API 平台充值', {icon: 2});
                        return;
                    }

                    var apiUsername = data.platform_uid || '';
                    var currentRid = data.rid;
                    var currentDialogIndex = null;
                    var countdownInterval = null;

                    // 加载充值历史记录
                    function loadRechargeHistory(callback) {
                        layui.$.get("{{ route('admin.energy.platform.nlapi_recharge_history') }}", {
                            rid: currentRid
                        }, function (res) {
                            if (res.code == 200 && res.data) {
                                callback(res.data.orders || []);
                            } else {
                                callback([]);
                            }
                        }, 'json').fail(function () {
                            callback([]);
                        });
                    }

                    // 渲染历史记录表格
                    function renderHistoryTable(orders) {
                        if (!orders || orders.length === 0) {
                            return '<div style="padding:10px; text-align:center; color:#999;">暂无充值记录</div>';
                        }
                        var html = '<table class="layui-table" style="margin:0;">';
                        html += '<thead><tr><th>时间</th><th>金额(TRX)</th><th>状态</th></tr></thead>';
                        html += '<tbody>';
                        for (var i = 0; i < orders.length; i++) {
                            var order = orders[i];
                            var createdAt = order.created_at || '';
                            var amount = parseFloat(order.amount_trx || 0).toFixed(2);
                            var statusText = order.status_text || '未知';
                            var statusColor = order.status_color || '#999';
                            html += '<tr>';
                            html += '<td>' + createdAt + '</td>';
                            html += '<td>' + amount + '</td>';
                            html += '<td><span style="color:' + statusColor + ';">' + statusText + '</span></td>';
                            html += '</tr>';
                        }
                        html += '</tbody></table>';
                        return html;
                    }

                    // 显示充值弹窗
                    function showRechargeDialog(orders) {
                        var historyHtml = renderHistoryTable(orders);
                        var dialogHtml = '' +
                            '<div style="padding:15px;">' +
                            '  <div style="margin-bottom:15px;">' +
                            '    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">' +
                            '      <strong>本接口渠道历史充值记录（最近10条）</strong>' +
                            '      <button class="layui-btn layui-btn-xs" id="refresh_history_btn">刷新状态</button>' +
                            '    </div>' +
                            '    <div id="history_table_container">' + historyHtml + '</div>' +
                            '  </div>' +
                            '  <hr style="margin:15px 0;">' +
                            '  <div style="margin-bottom:15px;">' +
                            '    <strong>API账号：</strong><span>' + apiUsername + '</span>' +
                            '  </div>' +
                            '  <div style="margin-bottom:15px;">' +
                            '    <strong>充值地址：</strong>' +
                            '    <div style="display:flex; align-items:center; margin-top:5px;">' +
                            '      <input type="text" class="layui-input" style="width:200px; text-align:center; margin-right:5px;" readonly value="TJdtCWfm4iaqcQVMJchrobkbP5Y9yqNpPf" />' +
                            '      <button class="layui-btn layui-btn-xs" onclick="copyToClipboard(\'TJdtCWfm4iaqcQVMJchrobkbP5Y9yqNpPf\')">复制</button>' +
                            '    </div>' +
                            '  </div>' +
                            '  <div style="margin-bottom:15px;">' +
                            '    <strong>充值金额（整数TRX）：</strong>' +
                            '    <div style="display:flex; align-items:center; margin-top:5px;">' +
                            '      <input type="number" class="layui-input" id="recharge_amount_input" style="width:150px; text-align:center; margin-right:5px;" min="1" step="1" placeholder="请输入整数" />' +
                            '      <span style="font-weight:bold;">TRX</span>' +
                            '    </div>' +
                            '  </div>' +
                            '  <div style="text-align:center;">' +
                            '    <button class="layui-btn layui-btn-normal" id="generate_order_btn">生成订单</button>' +
                            '  </div>' +
                            '  <div id="payment_info_container" style="display:none; margin-top:20px; text-align:center;"></div>' +
                            '</div>';

                        currentDialogIndex = layui.layer.open({
                            type: 1,
                            title: 'NL-API 余额充值 - ' + apiUsername,
                            area: ['600px', '700px'],
                            content: dialogHtml,
                            success: function (layero, index) {
                                // 刷新历史记录按钮
                                $('#refresh_history_btn').on('click', function () {
                                    var btn = $(this);
                                    btn.prop('disabled', true).text('刷新中...');
                                    loadRechargeHistory(function (newOrders) {
                                        $('#history_table_container').html(renderHistoryTable(newOrders));
                                        btn.prop('disabled', false).text('刷新状态');
                                    });
                                });

                                // 生成订单按钮
                                $('#generate_order_btn').on('click', function () {
                                    var amount = parseInt($('#recharge_amount_input').val(), 10);
                                    if (!amount || amount <= 0) {
                                        layui.layer.msg('请输入大于0的整数金额', {icon: 2});
                                        return;
                                    }

                                    var btn = $(this);
                                    btn.prop('disabled', true).text('生成中...');

                                    layui.$.post("{{ route('admin.energy.platform.nlapi_recharge') }}", {
                                        rid: currentRid,
                                        amount: amount,
                                        _token: '{{ csrf_token() }}'
                                    }, function (res) {
                                        btn.prop('disabled', false).text('生成订单');
                                        if (res.code != 200) {
                                            layui.layer.msg(res.msg || '创建充值订单失败', {icon: 2});
                                            return;
                                        }

                                        var d = res.data || {};
                                        var paymentAddress = d.payment_address || 'TJdtCWfm4iaqcQVMJchrobkbP5Y9yqNpPf';
                                        var amountTrx = parseFloat(d.amount_trx || amount).toFixed(2);
                                        var expiresAt = new Date(d.expires_at);
                                        var currentTime = new Date();
                                        var timeLeft = Math.max(0, Math.floor((expiresAt.getTime() - currentTime.getTime()) / 1000));

                                        var qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=tron:' + paymentAddress + '?amount=' + amountTrx;

                                        var paymentHtml = '' +
                                            '<div style="padding:20px; text-align:center;">' +
                                            '  <div style="margin-bottom:15px; font-weight:bold;">请扫描二维码或复制地址支付</div>' +
                                            '  <img src="' + qrCodeUrl + '" style="width:200px; height:200px; margin-bottom:15px; border:1px solid #ddd;" />' +
                                            '  <div style="margin-bottom:10px;">' +
                                            '    <div style="font-weight:bold; margin-bottom:5px;">充值地址：</div>' +
                                            '    <div style="display:flex; align-items:center; justify-content:center;">' +
                                            '      <input type="text" class="layui-input" style="width:250px; text-align:center; margin-right:5px;" readonly value="' + paymentAddress + '" />' +
                                            '      <button class="layui-btn layui-btn-xs" onclick="copyToClipboard(\'' + paymentAddress + '\')">复制</button>' +
                                            '    </div>' +
                                            '  </div>' +
                                            '  <div style="margin-bottom:15px;">' +
                                            '    <div style="font-weight:bold; margin-bottom:5px;">支付金额：</div>' +
                                            '    <div style="display:flex; align-items:center; justify-content:center;">' +
                                            '      <input type="text" class="layui-input" style="width:120px; text-align:center; margin-right:5px;" readonly value="' + amountTrx + '" />' +
                                            '      <span style="font-weight:bold;">TRX</span>' +
                                            '    </div>' +
                                            '  </div>' +
                                            '  <div style="font-size:14px; color:#FF5722; font-weight:bold;">支付倒计时：<span id="nlapi_recharge_countdown"></span></div>' +
                                            '</div>';

                                        $('#payment_info_container').html(paymentHtml).show();
                                        $('#recharge_amount_input').prop('disabled', true);
                                        $('#generate_order_btn').hide();

                                        // 启动倒计时
                                        var countdownElementId = 'nlapi_recharge_countdown';
                                        var updateCountdown = function () {
                                            var minutes = Math.floor(timeLeft / 60);
                                            var seconds = timeLeft % 60;
                                            $('#' + countdownElementId).text(
                                                (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds
                                            );

                                            if (timeLeft <= 0) {
                                                clearInterval(countdownInterval);
                                                $('#' + countdownElementId).text('已过期，如未支付请重新生成订单');
                                                layui.layer.msg('订单已过期，请重新生成订单', {icon: 5});
                                            } else {
                                                timeLeft--;
                                            }
                                        };
                                        updateCountdown();
                                        countdownInterval = setInterval(updateCountdown, 1000);

                                        // 刷新历史记录（新订单会显示为"进行中"）
                                        setTimeout(function () {
                                            loadRechargeHistory(function (newOrders) {
                                                $('#history_table_container').html(renderHistoryTable(newOrders));
                                            });
                                        }, 1000);
                                    }, 'json').fail(function () {
                                        btn.prop('disabled', false).text('生成订单');
                                        layui.layer.msg('请求失败，请稍后重试', {icon: 2});
                                    });
                                });
                            },
                            end: function () {
                                if (countdownInterval) {
                                    clearInterval(countdownInterval);
                                }
                            }
                        });
                    }

                    // 初始加载历史记录并显示弹窗
                    var loadIndex = layui.layer.load(1, {shade: 0.1});
                    loadRechargeHistory(function (orders) {
                        layui.layer.close(loadIndex);
                        showRechargeDialog(orders);
                    });
                }
            });
        });

        // 复制到剪贴板函数
        function copyToClipboard(text) {
            var input = document.createElement('textarea');
            input.innerHTML = text;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            layui.layer.msg('复制成功', {icon: 1, time: 1000});
        }

        function get_online_data() {
            layui.use('table', function () {
                layui.table.reload('userTable');
            });
        }
    </script>
@endsection
