@extends('layouts.admin.app')
@section('nav-status-collection', 'active')
@section('nav-status-collection-wallet', 'active')
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

                @if( auth('admin')->user()->can('添加归集钱包') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加归集钱包',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'chain_type':['链类型','select',[ChainType],''],
                                'wallet_addr':['钱包地址','text',''],
                                'tg_notice_obj':['TG通知对象','text','','多个英文逗号隔开'],
                                'trx_collection_amount':['TRX归集金额','text','','0表示不归集'],
                                'usdt_collection_amount':['USDT归集金额','text','','0表示不归集'],
                                'trx_reserve_amount':['TRX预留金额','text','','每次归集预留在钱包的金额'],
                                'usdt_reserve_amount':['USDT预留金额','text','','每次归集预留在钱包的金额'],
                                'collection_wallet_addr':['归集钱包','text','',''],
                                'comments':['备注','text','']
                                },
                                '{{route("admin.collection.wallet.add")}}',get_online_data)">添加归集钱包
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 设置的通知对象，要关注这个机器人，也就是要和机器人聊过天，不然发不出去消息<p>
                    2. TRX归集金额 和 USDT归集金额：当归集金额超过对应余额后，会执行归集操作<p>
                    3. TRX预留金额 和 USDT预留金额：在归集的时候，会在钱包中留对应的金额。预留金额不能大于归集金额<p>
                    4. 5分钟检测一次钱包余额。在归集时，如果钱包矿工费不足，会从该机器人对应的闪兑钱包转入矿工费！请确保钱包矿工费足够！！
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.collection.wallet.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">通知机器人</th>
                                <th lay-data="{field:'bot_username',align:'center'}">通知机器人名称</th>
                                <th lay-data="{field:'chain_type',align:'center'}">链类型</th>
                                <th lay-data="{field:'wallet_addr',align:'center'}">钱包地址</th>
                                <th lay-data="{field:'wallet_addr_privatekey',align:'center'}">钱包私钥</th>
                                <th lay-data="{align:'center', templet:'#status',width:100}">状态</th>
                                <th lay-data="{field:'trx_balance',align:'center'}">TRX余额</th>
                                <th lay-data="{field:'usdt_balance',align:'center'}">USDT余额</th>
                                <th lay-data="{field:'trx_collection_amount',align:'center'}">TRX归集金额</th>
                                <th lay-data="{field:'usdt_collection_amount',align:'center'}">USDT归集金额</th>
                                <th lay-data="{field:'trx_reserve_amount',align:'center'}">TRX预留金额</th>
                                <th lay-data="{field:'usdt_reserve_amount',align:'center'}">USDT预留金额</th>
                                <th lay-data="{field:'collection_wallet_addr',align:'center'}">归集钱包</th>
                                <th lay-data="{field:'comments',align:'center'}">备注</th>
                                <th lay-data="{field:'create_time',align:'center'}">创建时间</th>
                                <th lay-data="{field:'last_collection_time',align:'center'}">最近归集时间</th>
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
            @if( auth('admin')->user()->can('修改归集钱包') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改归集钱包',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_rid':['机器人ID','select',[botData],'@{{d.bot_rid}}'],
                        'chain_type':['链类型','select',[ChainType],'@{{d.chain_type}}'],
                        'wallet_addr':['钱包地址','text','@{{d.wallet_addr}}'],
                        'tg_notice_obj':['TG通知对象','text','@{{d.tg_notice_obj}}'],
                        'trx_collection_amount':['TRX归集金额','text','@{{d.trx_collection_amount}}'],
                        'usdt_collection_amount':['USDT归集金额','text','@{{d.usdt_collection_amount}}'],
                        'trx_reserve_amount':['TRX预留金额','text','@{{d.trx_reserve_amount}}'],
                        'usdt_reserve_amount':['USDT预留金额','text','@{{d.usdt_reserve_amount}}'],
                        'collection_wallet_addr':['归集钱包','text','@{{d.collection_wallet_addr}}'],
                        'comments':['备注','text','@{{d.comments}}']
                    },
                    '{{route("admin.collection.wallet.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('修改私钥') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" style="background-color:blueviolet !important" onclick="javascript:tools_add('修改私钥',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'chain_type':['链类型','span','@{{d.chain_type}}'],
                        'wallet_addr':['钱包地址','span','@{{d.wallet_addr}}'],
                        'wallet_addr_privatekey':['钱包私钥','text','@{{d.wallet_addr_privatekey}}'],
                        'permission_id':['权限ID','text','@{{d.permission_id}}'],
                    },
                    '{{route("admin.collection.wallet.updateprikey")}}',get_online_data);">私钥
                </button>
            @endif
            @if( auth('admin')->user()->can('删除归集钱包') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.collection.wallet.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
        var CollectionWalletStatus = @json($CollectionWalletStatus, JSON_UNESCAPED_UNICODE);
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
                        form_func('{{route("admin.collection.wallet.change_status")}}', {
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
