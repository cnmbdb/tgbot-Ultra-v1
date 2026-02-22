@extends('layouts.admin.app')
@section('nav-status-groupuser', 'active')
@section('nav-status-groupuser-user', 'active')
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
                            搜索用户名：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="tg_username" value="" autocomplete="off">
                            </div>
                            搜索用户ID：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="tg_uid" value="" autocomplete="off">
                            </div>
                            搜索用户昵称：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="tg_nickname" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>
                
                @if( auth('admin')->user()->can('群发消息') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('群发消息',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'message':['消息内容','textarea','','支持样式，请看主页说明']
                                },
                                '{{route("admin.groupuser.user.batchsendmessage")}}',get_online_data)">群发消息
                    </button>
                @endif
                @if( auth('admin')->user()->can('批量删除停用') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm delete_button" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('群发消息',
                                {
                                'bot_rid':['选择机器人','select',[botData],'']
                                },
                                '{{route("admin.groupuser.user.batchdelete")}}',get_online_data)">批量删除停用
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 可对用户发送消息：单独发或者群发
                </div>
                
                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.groupuser.user.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'tg_uid',align:'center'}">用户ID</th>
                                <th lay-data="{field:'tg_username',align:'center'}">用户名</th>
                                <th lay-data="{field:'tg_nickname',align:'center'}">用户昵称</th>
                                <th lay-data="{field:'status_val',align:'center'}">当前状态</th>
                                <th lay-data="{field:'cash_trx',align:'center'}">可用TRX余额</th>
                                <th lay-data="{field:'cash_usdt',align:'center'}">可用USDT余额</th>
                                <th lay-data="{field:'max_monitor_wallet',align:'center'}">最大监控数量</th>
                                <th lay-data="{field:'first_time',align:'center'}">关注时间</th>
                                <th lay-data="{field:'last_time',align:'center'}">最近时间</th>
                                <th lay-data="{field:'stop_time',align:'center'}">停用时间</th>
                                <th lay-data="{toolbar:'#tpl_opt',align:'center'}">操作</th>
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
            @if( auth('admin')->user()->can('发送消息') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('发送消息',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_username':['机器人名称','span','@{{d.bot_username}}'],
                        'tg_username':['用户名','span','@{{d.tg_username}}'],
                        'tg_nickname':['用户昵称','span','@{{d.tg_nickname}}'],
                        'message':['消息内容','textarea','','支持样式，请看主页说明'],
                    },
                    '{{route("admin.groupuser.user.sendmessage")}}',get_online_data);">发送消息
                </button>
            @endif
            @if( auth('admin')->user()->can('用户充值') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm add_button" onclick="javascript:tools_add('用户充值',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_username':['机器人名称','span','@{{d.bot_username}}'],
                        'tg_username':['用户名','span','@{{d.tg_username}}'],
                        'tg_nickname':['用户昵称','span','@{{d.tg_nickname}}'],
                        'cash_trx':['可用TRX余额','text','@{{d.cash_trx}}'],
                        'cash_usdt':['可用USDT余额','text','@{{d.cash_usdt}}'],
                        'max_monitor_wallet':['最大监控地址数量','text','@{{d.max_monitor_wallet}}'],
                    },
                    '{{route("admin.groupuser.user.rechargemanual")}}',get_online_data);">充值
                </button>
            @endif
            @if( auth('admin')->user()->can('删除用户') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.groupuser.user.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
        </div>
    </script>

    <script>
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
        });

        function get_online_data() {
            layui.use('table', function () {
                layui.table.reload('userTable');
            });
        }
    </script>
@endsection
