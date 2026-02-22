@extends('layouts.admin.app')
@section('nav-status-transit', 'active')
@section('nav-status-transit-walletcoin', 'active')
@section('contents')

    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索闪兑收钱包：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="receive_wallet" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加闪兑币种') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加闪兑币种',
                                {
                                'transit_wallet_id':['收款钱包地址','select',[walletData],''],
                                'in_coin_name':['转入币名','select',[Coinname],''],
                                'out_coin_name':['回款币名','select',[Coinname],''],
                                'is_realtime_rate':['汇率方式','select',[IsRealtimeRate],''],
                                'profit_rate':['实时汇率利润','text','','仅汇率方式为实时的时候有效'],
                                'exchange_rate':['汇率','text','','仅汇率方式为固定的时候有效'],
                                'kou_out_amount':['回款金额扣除手续费','text',''],
                                'min_transit_amount':['最低转入','text',''],
                                'max_transit_amount':['最高转入','text',''],
                                'comments':['备注','textarea','','']
                                },
                                '{{route("admin.transit.walletcoin.add")}}',get_online_data)">添加闪兑币种
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 转入的金额乘以汇率就是要回款的金额<p>
                    2. 回款金额扣除手续费：从计算汇率后的回款金额中扣除后的金额后再回款<p>
                    3. 如果设置实时汇率，每3秒更新一次。实时(直减)表示兑换1U,扣除这么多TRX。实时(百分比)表示兑换1U,按百分比计算,设置为0.15表示15%的利润
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.transit.walletcoin.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'receive_wallet',align:'center'}">收款钱包地址</th>
                                <th lay-data="{field:'in_coin_name',align:'center'}">转入币名</th>
                                <th lay-data="{field:'out_coin_name',align:'center'}">回款币名</th>
                                <th lay-data="{field:'is_realtime_rate_val',align:'center'}">汇率方式</th>
                                <th lay-data="{field:'profit_rate',align:'center'}">实时汇率利润</th>
                                <th lay-data="{field:'exchange_rate',align:'center'}">汇率</th>
                                <th lay-data="{field:'kou_out_amount',align:'center'}">回款金额扣除手续费</th>
                                <th lay-data="{field:'min_transit_amount',align:'center'}">最低转入</th>
                                <th lay-data="{field:'max_transit_amount',align:'center'}">最高转入</th>
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
            @if( auth('admin')->user()->can('修改闪兑币种') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改闪兑币种',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'transit_wallet_id':['','hidden','@{{d.transit_wallet_id}}'],
                        'receive_wallet':['收款钱包地址','span','@{{d.receive_wallet}}'],
                        'in_coin_name':['转入币名','select',[Coinname],'@{{d.in_coin_name}}'],
                        'out_coin_name':['回款币名','select',[Coinname],'@{{d.out_coin_name}}'],
                        'is_realtime_rate':['汇率方式','select',[IsRealtimeRate],'@{{d.is_realtime_rate}}'],
                        'profit_rate':['实时汇率利润','text','@{{d.profit_rate}}'],
                        'exchange_rate':['汇率','text','@{{d.exchange_rate}}'],
                        'kou_out_amount':['回款金额扣除手续费','text','@{{d.kou_out_amount}}'],
                        'min_transit_amount':['最低转入','text','@{{d.min_transit_amount}}'],
                        'max_transit_amount':['最高转入','text','@{{d.max_transit_amount}}'],
                        'comments':['备注','textarea','@{{d.comments}}','']
                    },
                    '{{route("admin.transit.walletcoin.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除闪兑币种') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.transit.walletcoin.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
        </div>
    </script>

    <script>
        var Coinname = @json($Coinname, JSON_UNESCAPED_UNICODE);
        var walletData = @json($walletData, JSON_UNESCAPED_UNICODE);
        var IsRealtimeRate = @json($IsRealtimeRate, JSON_UNESCAPED_UNICODE);
        
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
