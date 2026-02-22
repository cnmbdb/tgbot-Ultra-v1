@extends('layouts.admin.app')
@section('nav-status-monitor', 'active')
@section('nav-status-monitor-wallet', 'active')
@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            <div class="layui-input-inline">
                                <select name="bot_rid" id="bot_rid">
                                    <option value="">选择机器人</option>
                                    @foreach($botData as $k => $val)
                                        <option value="{{$k}}">{{$val}}</option>
                                    @endforeach
                                </select>
                            </div>
                            搜索监控钱包：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="monitor_wallet" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加监控钱包') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加监控钱包',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'chain_type':['链类型','select',[ChainType],''],
                                'monitor_wallet':['监控钱包地址','text',''],
                                'tg_notice_obj':['TG通知对象','text','','多个英文逗号隔开'],
                                'balance_alert':['余额预警金额','text','','0表示不监控余额'],
                                'comments':['备注','text','']
                                },
                                '{{route("admin.monitor.wallet.add")}}',get_online_data)">添加监控钱包
                    </button>
                @endif
                
                @if( auth('admin')->user()->can('批量添加监控') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('批量添加监控',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'chain_type':['链类型','select',[ChainType],''],
                                'monitor_wallet':['监控钱包地址','textarea','','格式为：钱包地址,备注  中间英文逗号分隔,一行一条数据,地址在前,备注在后'],
                                'tg_notice_obj':['TG通知对象','text','',''],
                                'balance_alert':['余额预警金额','text','','0表示不监控余额']
                                },
                                '{{route("admin.monitor.wallet.batchadd")}}',get_online_data)">批量添加监控
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 设置的通知对象，要关注这个机器人，也就是要和机器人聊过天，不然发不出去消息<p>
                    2. 监控地址的USDT交易(双向),TRX交易(双向),授权(双向),多签(双向),质押(双向)<p>
                    3. 批量添加监控，格式为：钱包地址,备注  中间英文逗号分隔,一行一条数据,地址在前,备注在后
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.monitor.wallet.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">通知机器人</th>
                                <th lay-data="{field:'bot_username',align:'center'}">通知机器人名称</th>
                                <th lay-data="{field:'chain_type',align:'center'}">链类型</th>
                                <th lay-data="{field:'monitor_wallet',align:'center'}">监控钱包地址</th>
                                <th lay-data="{field:'balance_alert',align:'center'}">余额预警金额</th>
                                <th lay-data="{align:'center', templet:'#status',width:200}">状态</th>
                                <th lay-data="{field:'monitor_usdt_transaction_val',align:'center'}">USDT交易监控</th>
                                <th lay-data="{field:'monitor_trx_transaction_val',align:'center'}">TRX交易监控</th>
                                <th lay-data="{field:'monitor_approve_transaction_val',align:'center'}">授权监控</th>
                                <th lay-data="{field:'monitor_multi_transaction_val',align:'center'}">多签监控</th>
                                <th lay-data="{field:'monitor_pledge_transaction_val',align:'center'}">质押监控</th>
                                <th lay-data="{field:'comments',align:'center'}">备注</th>
                                <th lay-data="{field:'create_time',align:'center'}">创建时间</th>
                                <th lay-data="{field:'update_time',align:'center'}">修改时间</th>
                                <th lay-data="{toolbar:'#tpl_opt',align:'center',width:200}">操作</th>
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
            @if( auth('admin')->user()->can('修改监控钱包') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改监控钱包',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_rid':['机器人ID','select',[botData],'@{{d.bot_rid}}'],
                        'chain_type':['链类型','select',[ChainType],'@{{d.chain_type}}'],
                        'monitor_wallet':['监控钱包地址','text','@{{d.monitor_wallet}}'],
                        'balance_alert':['余额预警金额','text','@{{d.balance_alert}}'],
                        'tg_notice_obj':['TG通知对象','text','@{{d.tg_notice_obj}}'],
                        'comments':['备注','text','@{{d.comments}}']
                    },
                    '{{route("admin.monitor.wallet.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除监控钱包') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.monitor.wallet.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
        var ChainType = @json($ChainType, JSON_UNESCAPED_UNICODE);
        var MonitorWalletStatus = @json($MonitorWalletStatus, JSON_UNESCAPED_UNICODE);
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
                        form_func('{{route("admin.monitor.wallet.change_status")}}', {
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
