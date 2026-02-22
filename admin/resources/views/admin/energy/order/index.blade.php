@extends('layouts.admin.app')
@section('nav-status-energy', 'active')
@section('nav-status-energy-order', 'active')
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
                            平台用户UID：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="platform_uid" value="" autocomplete="off">
                            </div>
                            平台订单号：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="platform_order_id" value="" autocomplete="off">
                            </div>
                            接收地址：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="receive_address" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>
                
                @if( auth('admin')->user()->can('批量回收能量') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('批量回收能量',
                                {
                                'daili_address':['代理地址','text','','代理地址为能量平台中的地址'],
                                'recovery_address':['接收地址','text','','']
                                },
                                '{{route("admin.energy.order.batch_recovery_energy")}}',get_online_data)">批量回收能量
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 65000 能量，在对方地址有U的情况，可以转账给对方一次<p>
                    2. 131000 能量，在对方地址无U的情况，可以转账给对方一次<p>
                    3. 注意：不管对方地址有U还是无U，转账都需要消耗345带宽，如对方地址无带宽可用，则需要预支给对方地址1TRX<p>
                    4. 批量回收能量：回收代理地址 代理给 接收地址的所有能量<p>
                    5. 已回收：对于人工在钱包APP中回收了能量的情况，后台可以点击已回收，机器人就不会处理已回收的数据
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.energy.order.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'energy_platform_rid',align:'center'}">能量平台ID</th>
                                <th lay-data="{field:'platform_name_val',align:'center'}">能量平台</th>
                                <th lay-data="{field:'platform_uid',align:'center'}">平台用户UID</th>
                                <th lay-data="{field:'receive_address',align:'center'}">接收地址</th>
                                <th lay-data="{field:'platform_order_id',align:'center'}">平台订单号</th>
                                <th lay-data="{field:'energy_amount',align:'center'}">代理能量</th>
                                <th lay-data="{field:'energy_day_val',align:'center'}">代理期限</th>
                                <th lay-data="{field:'energy_time',align:'center'}">代理时间</th>
                                <th lay-data="{field:'source_type_val',align:'center'}">下单来源</th>
                                <th lay-data="{field:'recovery_status_val',align:'center'}">回收状态</th>
                                <th lay-data="{field:'recovery_time',align:'center'}">回收时间</th>
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
            @if( auth('admin')->user()->can('已回收') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn edit_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.energy.order.alreadyrecover")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">已回收
                </button>
            @endif
        </div>
    </script>
    
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
            
        });

        function get_online_data() {
            layui.use('table', function () {
                layui.table.reload('userTable');
            });
        }
    </script>
@endsection
