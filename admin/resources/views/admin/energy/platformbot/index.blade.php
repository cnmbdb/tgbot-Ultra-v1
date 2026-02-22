@extends('layouts.admin.app')
@section('nav-status-energy', 'active')
@section('nav-status-energy-platformbot', 'active')
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

                @if( auth('admin')->user()->can('添加机器人能量') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加机器人能量',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'poll_group':['轮询组','select',[PollGroup],''],
                                'tg_admin_uid':['TG管理员用户ID','text','','多个用英文逗号隔开'],
                                'tg_notice_obj_receive':['TG通知对象(收款)','text','','多个用英文逗号隔开'],
                                'tg_notice_obj_send':['TG通知对象(成功)','text','','多个用英文逗号隔开'],
                                'receive_wallet':['收款钱包','text','','用于套餐收款'],
                                'get_tx_time':['开始拉取交易时间','datetime',''],
                                'bishu_stop_day':['滞留暂停天数','text','','0'],
                                'comments':['备注','textarea','',''],
                                'agent_tg_uid':['代理用户ID','text','','是代理地址时，每笔需要扣款'],
                                'agent_per_price':['代理每笔TRX价格','text','','是代理地址时，扣用户的TRX余额']
                                },
                                '{{route("admin.energy.platformbot.add")}}',get_online_data)">添加机器人能量
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                1. 收款钱包，不能和闪兑钱包用同一个。<b style="color:blue">一定要注意开始拉取交易时间</b><p>
                    2. 如需开启智能托管，需要先在 机器人列表 模块设置机器人对应的充值收款地址，在 机器人能量 模块编辑 智能托管 的价格<p>
                    3. 笔数回收方式：到期回收(代理的能量到期了才会回收)，代理下一笔回收(在代理下一笔能量之前,先回收再代理,如果在代理期限内一直没有使用能量,则到期后也会回收)<p>
                    4. 笔数套餐：笔数代理方式:当为 自动 时，自己的机器人才会监控并代理能量。当为 提交到其他平台 时，则会直接提交给第三方平台托管监控，自己的机器人不代理能量。<p>
                    5. 笔数套餐：只有自己质押代理的时候，才能代理 笔数代理期限30天，如果是其他第三方平台，即使选择了30天，也会默认为1天<p>
                    6. 笔数套餐：笔数代理方式 设置为 提交到trongas.io 时，请登录 <a href="https://trongas.io/user/home" target="_blank">Trongas平台</a>->笔数模式->账号配置，填写下单通知接口地址，地址填写为：https://job.xxx.com/api/trongas/notice，其中job.xxx.com替换为你的任务域名地址(在系统设置->配置信息，job任务域名URL)，不添加回调接口则没有tg能量通知<p>
                    7. 笔数套餐：笔数代理方式 设置为 提交到搜狐 时，请登录 搜狐后台->智能托管和笔数->设置，填写消费回调，地址填写为：https://job.xxx.com/api/sohu/notice，其中job.xxx.com替换为你的任务域名地址(在系统设置->配置信息，job任务域名URL)，不添加回调接口则没有tg能量通知<p>
                    8. 当该地址是代理用户ID时，代理用户一定要关注对应的机器人，并充值trx余额。不要给代理机器人管理员预支权限，这部分无法扣款！<p>
                    9. 滞留暂停天数：当笔数超过这么多天都未使用时，自动暂停，0表示无限制
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.energy.platformbot.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">机器人</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'poll_group_val',align:'center'}">轮询组</th>
                                <th lay-data="{field:'tg_admin_uid',align:'center'}">TG管理员用户ID</th>
                                <th lay-data="{align:'center', templet:'#status',width:200}">状态</th>
                                <th lay-data="{field:'receive_wallet',align:'center'}">收款钱包</th>
                                <th lay-data="{field:'get_tx_time',align:'center'}">开始拉取交易时间</th>
                                <th lay-data="{field:'tg_notice_obj_receive',align:'center'}">TG通知对象(收款)</th>
                                <th lay-data="{field:'tg_notice_obj_send',align:'center'}">TG通知对象(成功)</th>
                                <th lay-data="{field:'bishu_stop_day',align:'center'}">滞留暂停天数</th>
                                <!--<th lay-data="{field:'create_time',align:'center'}">创建时间</th>-->
                                <!--<th lay-data="{field:'update_time',align:'center'}">修改时间</th>-->
                                <th lay-data="{field:'comments',align:'center'}">备注</th>
                                <th lay-data="{field:'is_open_ai_trusteeship_val',align:'center'}">智能托管</th>
                                <th lay-data="{field:'is_open_bishu_val',align:'center'}">笔数套餐</th>
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
            @if( auth('admin')->user()->can('修改机器人能量') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改机器人能量',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_rid':['机器人ID','select',[botData],'@{{d.bot_rid}}'],
                        'poll_group':['轮询组','select',[PollGroup],'@{{d.poll_group}}'],
                        'tg_admin_uid':['TG管理员用户ID','text','@{{d.tg_admin_uid}}'],
                        'tg_notice_obj_receive':['TG通知对象(收款)','text','@{{d.tg_notice_obj_receive}}'],
                        'tg_notice_obj_send':['TG通知对象(成功)','text','@{{d.tg_notice_obj_send}}'],
                        'receive_wallet':['收款钱包','text','@{{d.receive_wallet}}'],
                        'get_tx_time':['开始拉取交易时间','datetime','@{{d.get_tx_time}}'],
                        'comments':['备注','textarea','@{{d.comments}}'],
                        'agent_tg_uid':['代理用户ID','text','@{{d.agent_tg_uid}}'],
                        'agent_per_price':['代理每笔TRX价格','text','@{{d.agent_per_price}}'],
                    },
                    '{{route("admin.energy.platformbot.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除机器人能量') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.energy.platformbot.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
            @if( auth('admin')->user()->can('智能托管') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" style="background-color:blueviolet !important" onclick="javascript:tools_add('智能托管',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_username':['机器人名称','span','@{{d.bot_username}}'],
                        'is_open_ai_trusteeship':['开启智能托管','select',[IsOpenAiTrusteeship],'@{{d.is_open_ai_trusteeship}}'],
                        'trx_price_energy_32000':['65000能量TRX价格','text','@{{d.trx_price_energy_32000}}'],
                        'trx_price_energy_65000':['131000能量TRX价格','text','@{{d.trx_price_energy_65000}}'],
                        'per_energy_day':['智能代理期限','select',[AiEnergyDay],'@{{d.per_energy_day}}'],
                        'ai_trusteeship_recovery_type':['智能托管回收方式','select',[AiRecoveryType],'@{{d.ai_trusteeship_recovery_type}}'],
                    },
                    '{{route("admin.energy.platformbot.aitrusteeship")}}',get_online_data);">智能托管
                </button>
            @endif
            @if( auth('admin')->user()->can('笔数套餐') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" style="background-color:blueviolet !important" onclick="javascript:tools_add('笔数套餐',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_username':['机器人名称','span','@{{d.bot_username}}'],
                        'is_open_bishu':['开启笔数套餐','select',[IsOpenAiTrusteeship],'@{{d.is_open_bishu}}'],
                        'bishu_daili_type':['笔数代理方式','select',[BishuDailiType],'@{{d.bishu_daili_type}}'],
                        'per_bishu_usdt_price':['每笔USDT价格','text','@{{d.per_bishu_usdt_price}}'],
                        'per_bishu_energy_quantity':['每笔能量','text','@{{d.per_bishu_energy_quantity}}'],
                        'per_energy_day_bishu':['笔数代理期限','select',[EnergyDay],'@{{d.per_energy_day_bishu}}'],
                        'bishu_recovery_type':['笔数回收方式','select',[BishuRecoveryType],'@{{d.bishu_recovery_type}}'],
                        'bishu_stop_day':['滞留暂停天数','text','@{{d.bishu_stop_day}}'],
                    },
                    '{{route("admin.energy.platformbot.bishu")}}',get_online_data);">笔数套餐
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
        var PollGroup = @json($PollGroup, JSON_UNESCAPED_UNICODE);
        var Status = @json($Status, JSON_UNESCAPED_UNICODE);
        var botData = @json($botData, JSON_UNESCAPED_UNICODE);
        var IsOpenAiTrusteeship = @json($IsOpenAiTrusteeship, JSON_UNESCAPED_UNICODE);
        var EnergyDay = @json($EnergyDay, JSON_UNESCAPED_UNICODE);
        var AiEnergyDay = @json($AiEnergyDay, JSON_UNESCAPED_UNICODE);
        var BishuRecoveryType = @json($BishuRecoveryType, JSON_UNESCAPED_UNICODE);
        var BishuDailiType = @json($BishuDailiType, JSON_UNESCAPED_UNICODE);
        var AiRecoveryType = @json($AiRecoveryType, JSON_UNESCAPED_UNICODE);
    
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
                        form_func('{{route("admin.energy.platformbot.change_status")}}', {
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
        });

        function get_online_data() {
            layui.use('table', function () {
                layui.table.reload('userTable');
            });
        }
    </script>
@endsection
