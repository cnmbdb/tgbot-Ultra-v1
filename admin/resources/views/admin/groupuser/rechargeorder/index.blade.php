@extends('layouts.admin.app')
@section('nav-status-groupuser', 'active')
@section('nav-status-groupuser-rechargeorder', 'active')
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
                            搜索开通用户名：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="recharge_tg_username" value="" autocomplete="off">
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
                    1. 未支付的订单，有效期为15分钟，过期后会失效并给用户发送TG消息通知<p>
                    2. 失效的订单不能再次支付<p>
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.groupuser.rechargeorder.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人</th>
                                <th lay-data="{field:'recharge_tg_uid',align:'center'}">充值TG用户ID</th>
                                <th lay-data="{field:'recharge_tg_username',align:'center'}">充值TG用户名</th>
                                <th lay-data="{field:'recharge_coin_name',align:'center'}">充值币种</th>
                                <th lay-data="{field:'recharge_pay_price',align:'center'}">充值金额</th>
                                <th lay-data="{field:'need_pay_price',align:'center'}">应支付金额</th>
                                <th lay-data="{field:'status_val',align:'center'}">状态</th>
                                <th lay-data="{field:'create_time',align:'center'}">创建时间</th>
                                <th lay-data="{field:'expire_time',align:'center'}">过期时间</th>
                                <th lay-data="{field:'cancel_time',align:'center'}">取消时间</th>
                                <th lay-data="{field:'complete_time',align:'center'}">完成时间</th>
                                <th lay-data="{field:'tx_hash',align:'center'}">交易hash</th>
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
