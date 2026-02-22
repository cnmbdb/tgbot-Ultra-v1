@extends('layouts.admin.app')
@section('nav-status-transit', 'active')
@section('nav-status-transit-walletblack', 'active')
@section('contents')

    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索黑钱包地址：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="black_wallet" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加黑钱包') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加黑钱包',
                                {
                                'chain_type':['链类型','select',[ChainType],''],
                                'black_wallet':['黑钱包地址','text',''],
                                'comments':['备注','text','']
                                },
                                '{{route("admin.transit.walletblack.add")}}',get_online_data)">添加黑钱包
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 闪兑黑钱包里添加的钱包，不会自动回款！！！
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.transit.walletblack.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'chain_type',align:'center'}">链类型</th>
                                <th lay-data="{field:'black_wallet',align:'center'}">黑钱包地址</th>
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
            @if( auth('admin')->user()->can('修改黑钱包地址') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改黑钱包地址',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'chain_type':['链类型','select',[ChainType],'@{{d.chain_type}}'],
                        'black_wallet':['黑钱包地址','text','@{{d.black_wallet}}'],
                        'comments':['备注','text','@{{d.comments}}']
                    },
                    '{{route("admin.transit.walletblack.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除黑钱包地址') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.transit.walletblack.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
        </div>
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
