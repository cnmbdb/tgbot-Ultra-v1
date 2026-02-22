@extends('layouts.admin.app')
@section('nav-status-energy', 'active')
@section('nav-status-energy-quick', 'active')
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
                            钱包地址：
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
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 此处订单为用户使用钱包余额下单<p>
                    2. 补发：只有处理状态为-失败，才能补发<p>
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.energy.quick.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人</th>
                                <th lay-data="{field:'tg_uid',align:'center'}">tg用户ID</th>
                                <th lay-data="{field:'wallet_addr',align:'center'}">钱包地址</th>
                                <th lay-data="{field:'energy_amount',align:'center'}">能量数量</th>
                                <th lay-data="{field:'package_name',align:'center'}">能量名称</th>
                                <th lay-data="{field:'pay_price',align:'center'}">支付金额</th>
                                <th lay-data="{field:'pay_type',align:'center'}">支付方式</th>
                                <th lay-data="{field:'pay_time',align:'center'}">支付时间</th>
                                <th lay-data="{field:'status',align:'center'}">状态</th>
                                <th lay-data="{field:'process_time',align:'center'}">处理时间</th>
                                <th lay-data="{field:'comments',align:'center'}">备注</th>
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
            @if( auth('admin')->user()->can('补发') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" onclick="javascript:tools_add('补发',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'tg_uid':['tg用户ID','span','@{{d.tg_uid}}'],
                        'wallet_addr':['钱包地址','span','@{{d.wallet_addr}}'],
                        'energy_amount':['能量数量','span','@{{d.energy_amount}}'],
                        'package_name':['能量名称','span','@{{d.package_name}}'],
                        'pay_price':['支付金额','span','@{{d.pay_price}}'],
                        'pay_type':['支付时间','span','@{{d.pay_type}}'],
                    },
                    '{{route("admin.energy.quick.reorder")}}',get_online_data);">补发
                </button>
            @endif
        </div>
    </script>

    <script>
        var botData = @json($botData, JSON_UNESCAPED_UNICODE);
        var status = @json($status, JSON_UNESCAPED_UNICODE);
        
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
