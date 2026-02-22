@extends('layouts.admin.app')
@section('nav-status-transit', 'active')
@section('nav-status-transit-wallet', 'active')
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
                            搜索闪兑出钱包：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="send_wallet" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加闪兑钱包') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加闪兑钱包',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'chain_type':['链类型','select',[ChainType],''],
                                'receive_wallet':['收款钱包地址','text',''],
                                'send_wallet':['出款钱包地址','text',''], 	
                                'show_notes':['显示说明','text','','如地址前几位xx'],
                                'auto_stock_min_trx':['TRX低于数量进货','text','','0表示不自动'],
                                'auto_stock_per_usdt':['USDT自动进货闪兑数量','text','','0表示不自动'],
                                'tg_notice_obj_receive':['TG通知对象(收款)','text',''],
                                'tg_notice_obj_send':['TG通知对象(出款)','text',''],
                                'get_tx_time':['开始拉取交易时间','datetime','']
                                },
                                '{{route("admin.transit.wallet.add")}}',get_online_data)">添加闪兑钱包
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 添加完闪兑钱包后，要设置出款钱包的私钥<p>
                    2. 添加完闪兑钱包后，要添加闪兑钱包币种，设置汇率<p>
                    3. TRX低于数量进货：当出款钱包TRX余额低于该值时，自动闪兑进货，0表示不自动。需要确保出款钱包至少有50TRX！<p>
                    4. USDT自动进货闪兑数量：每次自动进货，闪兑的USDT数量，0表示不自动。需要确保出款钱包USDT余额足够！<p>
                    5. 重要！！！如果要使用自动闪兑进货TRX，需要查看收款钱包是否有 TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE 合约地址的USDT授权，<b>如果没有，请点击授权先授权，不然闪兑不成功</b><p>
                    6. 重要！！！确保私钥填写正确，授权，转账，闪兑都需要私钥签名才能成功。<b style="color:blue">一定要注意开始拉取交易时间</b>
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.transit.wallet.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">通知机器人</th>
                                <th lay-data="{field:'bot_username',align:'center'}">通知机器人名称</th>
                                <th lay-data="{field:'receive_wallet',align:'center'}">收款钱包地址</th>
                                <th lay-data="{field:'send_wallet',align:'center'}">出款钱包地址</th>
                                <th lay-data="{field:'show_notes',align:'center'}">显示说明</th>
                                <th lay-data="{field:'auto_stock_min_trx',align:'center'}">TRX低于数量进货</th>
                                <th lay-data="{field:'auto_stock_per_usdt',align:'center'}">USDT自动进货闪兑数量</th>
                                <th lay-data="{field:'send_wallet_privatekey',align:'center'}">出款钱包私钥</th>
                                <th lay-data="{align:'center', templet:'#status',width:200}">状态</th>
                                <th lay-data="{field:'get_tx_time',align:'center'}">开始拉取交易时间</th>
                                <th lay-data="{field:'create_time',align:'center'}">创建时间</th>
                                <th lay-data="{field:'update_time',align:'center'}">修改时间</th>
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
            @if( auth('admin')->user()->can('修改闪兑钱包') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改闪兑钱包',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_rid':['机器人ID','select',[botData],'@{{d.bot_rid}}'],
                        'chain_type':['链类型','select',[ChainType],'@{{d.chain_type}}'],
                        'receive_wallet':['收款钱包地址','text','@{{d.receive_wallet}}'],
                        'send_wallet':['出款钱包地址','text','@{{d.send_wallet}}'],
                        'show_notes':['显示说明','text','@{{d.show_notes}}'],
                        'auto_stock_min_trx':['TRX低于数量进货','text','@{{d.auto_stock_min_trx}}'],
                        'auto_stock_per_usdt':['USDT自动进货闪兑数量','text','@{{d.auto_stock_per_usdt}}'],
                        'tg_notice_obj_receive':['TG通知对象(收款)','text','@{{d.tg_notice_obj_receive}}'],
                        'tg_notice_obj_send':['TG通知对象(出款)','text','@{{d.tg_notice_obj_send}}'],
                        'get_tx_time':['开始拉取交易时间','datetime','@{{d.get_tx_time}}']
                    },
                    '{{route("admin.transit.wallet.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('修改私钥') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" style="background-color:blueviolet !important" onclick="javascript:tools_add('修改私钥',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'chain_type':['链类型','span','@{{d.chain_type}}'],
                        'receive_wallet':['收款钱包地址','span','@{{d.receive_wallet}}'],
                        'send_wallet':['出款钱包地址','span','@{{d.send_wallet}}'],
                        'send_wallet_privatekey':['出款钱包私钥','text','@{{d.send_wallet_privatekey}}']
                    },
                    '{{route("admin.transit.wallet.updateprikey")}}',get_online_data);">私钥
                </button>
            @endif
            @if( auth('admin')->user()->can('授权闪兑合约') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-primary layui-btn-sm" style="background-color:#ff9dea !important" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.transit.wallet.approve")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">授权
                </button>
            @endif
            @if( auth('admin')->user()->can('手工进货') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" style="background-color: brown !important" onclick="javascript:tools_add('手工进货',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'chain_type':['链类型','span','@{{d.chain_type}}'],
                        'receive_wallet':['收款钱包地址','span','@{{d.receive_wallet}}'],
                        'send_wallet':['出款钱包地址','span','@{{d.send_wallet}}'],
                        'swapamount':['USDT进货数量','text','','有足够USDT且TRX大于50个']
                    },
                    '{{route("admin.transit.wallet.manualtrx")}}',get_online_data);">进货
                </button>
            @endif
            @if( auth('admin')->user()->can('删除闪兑钱包') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.transit.wallet.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
        var ChainType = @json($ChainType, JSON_UNESCAPED_UNICODE);
        var TransWalletStatus = @json($TransWalletStatus, JSON_UNESCAPED_UNICODE);
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
                        form_func('{{route("admin.transit.wallet.change_status")}}', {
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
