@extends('layouts.admin.app')
@section('nav-status-monitor', 'active')
@section('nav-status-monitor-bot', 'active')
@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
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

                @if( auth('admin')->user()->can('添加机器人监控') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加机器人监控',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'price_usdt_5':['5个监控包','text','0'],
                                'price_usdt_10':['10个监控包','text','0'],
                                'price_usdt_20':['20个监控包','text','0'],
                                'price_usdt_50':['50个监控包','text','0'],
                                'price_usdt_100':['100个监控包','text','0'],
                                'price_usdt_200':['200个监控包','text','0'],
                                'comments':['备注','text','']
                                },
                                '{{route("admin.monitor.bot.add")}}',get_online_data)">添加机器人监控
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 监控包单独设置价格，如价格设置为0表示不开启该套餐<p>
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.monitor.bot.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">通知机器人</th>
                                <th lay-data="{field:'bot_username',align:'center'}">通知机器人名称</th>
                                <th lay-data="{field:'price_usdt_5',align:'center'}">5个监控包</th>
                                <th lay-data="{field:'price_usdt_10',align:'center'}">10个监控包</th>
                                <th lay-data="{field:'price_usdt_20',align:'center'}">20个监控包</th>
                                <th lay-data="{field:'price_usdt_50',align:'center'}">50个监控包</th>
                                <th lay-data="{field:'price_usdt_100',align:'center'}">100个监控包</th>
                                <th lay-data="{field:'price_usdt_200',align:'center'}">200个监控包</th>
                                <th lay-data="{align:'center', templet:'#status',width:200}">状态</th>
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
            @if( auth('admin')->user()->can('修改机器人监控') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改机器人监控',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_rid':['机器人ID','select',[botData],'@{{d.bot_rid}}'],
                        'price_usdt_5':['5个监控包','text','@{{d.price_usdt_5}}'],
                        'price_usdt_10':['10个监控包','text','@{{d.price_usdt_10}}'],
                        'price_usdt_20':['20个监控包','text','@{{d.price_usdt_20}}'],
                        'price_usdt_50':['50个监控包','text','@{{d.price_usdt_50}}'],
                        'price_usdt_100':['100个监控包','text','@{{d.price_usdt_100}}'],
                        'price_usdt_200':['200个监控包','text','@{{d.price_usdt_200}}'],
                        'comments':['备注','text','@{{d.comments}}']
                    },
                    '{{route("admin.monitor.bot.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除机器人监控') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.monitor.bot.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
                        form_func('{{route("admin.monitor.bot.change_status")}}', {
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
