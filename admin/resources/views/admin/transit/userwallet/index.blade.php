@extends('layouts.admin.app')
@section('nav-status-transit', 'active')
@section('nav-status-transit-userwallet', 'active')
@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索钱包：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="wallet_addr" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加预支') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加预支',
                                {
                                'chain_type':['链类型','select',[ChainType],''],
                                'wallet_addr':['钱包地址','text',''],
                                'yuzhi_sxf':['预支手续费','text','']
                                },
                                '{{route("admin.transit.userwallet.add")}}',get_online_data)">添加预支
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 如果钱包不存在,可以直接添加预支,如果钱包存在则修改预支<p>
                    2. 后台添加的预支,需要人工给钱包地址转手续费,在机器人自动预支的会自动转手续费<p>
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.transit.userwallet.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'chain_type',align:'center'}">链类型</th>
                                <th lay-data="{field:'wallet_addr',align:'center'}">钱包地址</th>
                                <th lay-data="{field:'total_transit_usdt',align:'center'}">总已闪兑USDT</th>
                                <th lay-data="{field:'total_transit_sxf',align:'center'}">总已闪兑手续费</th>
                                <th lay-data="{field:'total_yuzhi_sxf',align:'center'}">总已预支手续费</th>
                                <th lay-data="{field:'need_feedback_sxf',align:'center'}">未还预支</th>
                                <th lay-data="{field:'send_feedback_sxf',align:'center'}">已还预支</th>
                                <th lay-data="{field:'last_transit_time',align:'center'}">最近闪兑时间</th>
                                <th lay-data="{field:'last_yuzhi_time',align:'center'}">最近预支时间</th>
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
            @if( auth('admin')->user()->can('修改预支') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改预支',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'chain_type':['链类型','span','@{{d.chain_type}}'],
                        'wallet_addr':['钱包地址','span','@{{d.wallet_addr}}'],
                        'need_feedback_sxf':['未还预支','span','@{{d.need_feedback_sxf}}'],
                        'send_feedback_sxf':['已还预支','span','@{{d.send_feedback_sxf}}'],
                        'yuzhi_sxf':['预支手续费','text','']
                    },
                    '{{route("admin.transit.userwallet.update")}}',get_online_data);">修改预支
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
