@extends('layouts.admin.app')
@section('nav-status-premium', 'active')
@section('nav-status-premium-order', 'active')
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
                                <input class="layui-input layui-input-inline" lay-verify="" name="premium_tg_username" value="" autocomplete="off">
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
                           lay-data="{url:'{{route("admin.premium.order.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'premium_platform_rid',align:'center'}">机器人会员平台ID</th>
                                <th lay-data="{field:'buy_tg_uid',align:'center'}">下单TG用户ID</th>
                                <th lay-data="{field:'buy_tg_username',align:'center'}">下单TG用户名</th>
                                <th lay-data="{field:'premium_tg_username',align:'center'}">开通会员用户名</th>
                                <th lay-data="{field:'need_pay_usdt',align:'center'}">应支付USDT</th>
                                <th lay-data="{field:'premium_package_month',align:'center'}">开通会员月份</th>
                                <th lay-data="{field:'status_val',align:'center'}">状态</th>
                                <th lay-data="{field:'create_time',align:'center'}">创建时间</th>
                                <th lay-data="{field:'expire_time',align:'center'}">过期时间</th>
                                <th lay-data="{field:'cancel_time',align:'center'}">取消时间</th>
                                <th lay-data="{field:'source_type_val',align:'center'}">下单来源</th>
                                <th lay-data="{field:'comments',align:'center'}">备注</th>
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
        var PlatformName = @json($PlatformName, JSON_UNESCAPED_UNICODE);
        var SourceType = @json($SourceType, JSON_UNESCAPED_UNICODE);
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
                        form_func('{{route("admin.premium.platform.change_status")}}', {
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
