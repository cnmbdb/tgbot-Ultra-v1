@extends('layouts.admin.app')
@section('nav-status-telegram', 'active')
@section('nav-status-telegram-telegrambot', 'active')
@section('contents')

    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索机器人名称：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="bot_username" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加机器人') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加机器人',
                                {
                                'bot_token':['机器人token','text',''],
                                'bot_admin_username':['机器人管理员','text','','@开头的用户名'],
                                'comments':['备注','text','']
                                },
                                '{{route("admin.telegram.telegrambot.add")}}',get_online_data)">添加机器人
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 添加或者修改机器人token后，请执行更新操作，执行webhook操作！！<P>
                    2. 申请机器人用BotFather，如果机器人需要在群组使用,要打开设置：bot settings->group privacy,改为turn off(关闭)。allow groups,改为turn groups on(开启)<P>
                    3. 如果群组开启了审核入群，机器人可以自动审核入群。但要先给机器人管理权限！！<P>
                    <span style='color:blue'>4. 如果需要充值功能，请配置充值钱包地址 和 开始拉取交易时间，注意钱包地址不能和闪兑，能量，会员使用同一个钱包地址</span>
                </div>
                
                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.telegram.telegrambot.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center',width:450}">机器人token</th>
                                <th lay-data="{field:'bot_admin_username',align:'center',width:200}">机器人管理员</th>
                                <th lay-data="{field:'bot_firstname',align:'center',width:200}">机器人显示名称</th>
                                <th lay-data="{field:'bot_username',align:'center',width:200}">机器人名称</th>
                                <th lay-data="{field:'comments',align:'center',width:300}">备注</th>
                                <th lay-data="{field:'create_time',align:'center',width:200}">创建时间</th>
                                <th lay-data="{field:'update_time',align:'center',width:200}">修改时间</th>
                                <th lay-data="{toolbar:'#tpl_opt',align:'center',width:300}">操作</th>
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
            @if( auth('admin')->user()->can('修改机器人') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改机器人',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_token':['机器人token','text','@{{d.bot_token}}'],
                        'bot_admin_username':['机器人管理员','text','@{{d.bot_admin_username}}'],
                        'comments':['备注','text','@{{d.comments}}'],
                    },
                    '{{route("admin.telegram.telegrambot.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('更新机器人') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-primary layui-btn-sm" style="background-color:#ff9dea !important" lay-event="gengxin" data-rid="@{{d.rid}}">更新
                </button>
            @endif
            @if( auth('admin')->user()->can('注册Webhook') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" style="background-color:blueviolet !important" lay-event="regwebhook" data-rid="@{{d.rid}}" data-bot-username="@{{d.bot_username}}" data-bot-token="@{{d.bot_token}}">Webhook
                </button>
            @endif
            @if( auth('admin')->user()->can('删除机器人') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" lay-event="delete" data-rid="@{{d.rid}}">删除
                </button>
            @endif
            @if( auth('admin')->user()->can('修改机器人充值') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改机器人充值',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_token':['机器人token','span','@{{d.bot_token}}'],
                        'recharge_wallet_addr':['充值钱包地址','text','@{{d.recharge_wallet_addr}}'],
                        'get_tx_time':['开始拉取交易时间','datetime','@{{d.get_tx_time}}']
                    },
                    '{{route("admin.telegram.telegrambot.recharge")}}',get_online_data);">充值
                </button>
            @endif
        </div>
    </script>

    <script>
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

            //监听工具条事件
            table.on('tool(userTable)', function(obj){
                var data = obj.data;
                var layEvent = obj.event;
                
                if(layEvent === 'gengxin'){
                    // 更新机器人信息
                    confirm_opt(function(){
                        form_func('{{route("admin.telegram.telegrambot.gengxin")}}', {'rid': data.rid}, get_online_data);
                    });
                } else if(layEvent === 'regwebhook'){
                    // 注册Webhook
                    tools_add('注册Webhook',
                        {
                            'rid':['','hidden', data.rid],
                            'bot_username':['机器人名称','span', data.bot_username],
                            'bot_token':['机器人token','span', data.bot_token]
                        },
                        '{{route("admin.telegram.telegrambot.regwebhook")}}', get_online_data);
                } else if(layEvent === 'delete'){
                    // 删除机器人
                    confirm_opt(function(){
                        form_func('{{route("admin.telegram.telegrambot.delete")}}', {'rid': data.rid}, get_online_data);
                    });
                }
            });
        });

        function get_online_data() {
            layui.use('table', function () {
                layui.table.reload('userTable');
            });
        }
    </script>
@endsection
