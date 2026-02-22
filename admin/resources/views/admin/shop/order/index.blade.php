@extends('layouts.admin.app')
@section('nav-status-shop', 'active')
@section('nav-status-shop-order', 'active')
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
                            卡号：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="cdkey_no" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.shop.order.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">机器人token</th>
                                <th lay-data="{field:'bot_firstname',align:'center'}">机器人显示名称</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'tg_uid',align:'center'}">下单tgID</th>
                                <th lay-data="{field:'tg_username',align:'center'}">下单tg用户名</th>
                                <th lay-data="{field:'cdkey_no',align:'center'}">卡号</th>
                                <th lay-data="{field:'pay_type_val',align:'center'}">支付方式</th>
                                <th lay-data="{field:'pay_price',align:'center'}">支付金额</th>
                                <th lay-data="{field:'pay_time',align:'center'}">支付时间</th>
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
            @if( auth('admin')->user()->can('修改备注') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改备注',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'cdkey_no':['卡号','span','@{{d.cdkey_no}}'],
                        'comments':['备注','text','@{{d.comments}}']
                    },
                    '{{route("admin.shop.order.update")}}',get_online_data);">修改
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
