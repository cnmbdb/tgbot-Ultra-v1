@extends('layouts.admin.app')
@section('nav-status-groupuser', 'active')
@section('nav-status-groupuser-rechargetrade', 'active')
@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索交易hash：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="tx_hash" value="" autocomplete="off">
                            </div>
                            发送钱包地址：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="transferfrom_address" value="" autocomplete="off">
                            </div>
                            接收钱包地址：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="transferto_address" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 会同时拉取USDT 和 TRX 交易<p>
                    2. 仅状态为 待充值-充值失败-金额无对应订单，可禁止
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.groupuser.rechargetrade.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'tx_hash',align:'center'}">交易hash</th>
                                <th lay-data="{field:'transferfrom_address',align:'center'}">发送钱包地址</th>
                                <th lay-data="{field:'transferto_address',align:'center'}">接收钱包地址</th>
                                <th lay-data="{field:'coin_name',align:'center'}">转入币名</th>
                                <th lay-data="{field:'amount',align:'center'}">转入数额</th>
                                <th lay-data="{field:'timestamp',align:'center'}">交易时间</th>
                                <th lay-data="{field:'get_time',align:'center'}">拉取时间</th>
                                <th lay-data="{field:'process_status_val',align:'center'}">处理状态</th>
                                <th lay-data="{field:'process_time',align:'center'}">处理时间</th>
                                <th lay-data="{field:'process_comments',align:'center'}">处理备注</th>
                                <th lay-data="{toolbar:'#tpl_opt',align:'center',width:100}">操作</th>
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
            @if( auth('admin')->user()->can('禁止充值') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm delete_button" onclick="javascript:tools_add('禁止充值',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'tx_hash':['交易hash','span','@{{d.tx_hash}}'],
                        'transferfrom_address':['发送钱包地址','span','@{{d.transferfrom_address}}'],
                        'coin_name':['转入币名','span','@{{d.coin_name}}'],
                        'amount':['转入数额','span','@{{d.amount}}'],
                        'timestamp':['交易时间','span','@{{d.timestamp}}'],
                        'process_status':['处理状态','span','@{{d.process_status}}'],
                    },
                    '{{route("admin.groupuser.rechargetrade.stoporder")}}',get_online_data);">禁止
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
        });

        function get_online_data() {
            layui.use('table', function () {
                layui.table.reload('userTable');
            });
        }
    </script>
@endsection
