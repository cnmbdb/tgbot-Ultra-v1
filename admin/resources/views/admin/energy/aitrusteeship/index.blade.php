@extends('layouts.admin.app')
@section('nav-status-energy', 'active')
@section('nav-status-energy-aitrusteeship', 'active')
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
                            tg用户ID：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="tg_uid" value="" autocomplete="off">
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
                    1. 需要在 机器人能量 模块，开启对应机器人的智能托管，并设置对应价格<p>
                    2. 当状态为 管理员关闭 时，用户不能开启<p>
                    3. 智能托管默认每次代理能量都是65000的能量，可以改为131000，仅有这两种，改为其他值会代理不了<p>
                    4. 下单状态 如一直为<b>下单中</b>，先查看备注(看失败原因是什么)，如需要重新下单，点击刷新<p>
                    5. 最大购买次数：0表示不限制。当 总已购买次数 大于等于 最大购买次数 时，不再托管能量
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.energy.aitrusteeship.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <!--<th lay-data="{field:'bot_token',align:'center'}">机器人</th>-->
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'tg_uid',align:'center'}">tg用户ID</th>
                                <th lay-data="{field:'wallet_addr',align:'center'}">监控地址</th>
                                <th lay-data="{field:'status_val',align:'center'}">状态</th>
                                <th lay-data="{field:'is_buy_val',align:'center'}">下单状态</th>
                                <th lay-data="{field:'current_bandwidth_quantity',align:'center'}">当前带宽</th>
                                <th lay-data="{field:'current_energy_quantity',align:'center'}">当前能量</th>
                                <th lay-data="{field:'min_energy_quantity',align:'center'}">能量低于值购买</th>
                                <th lay-data="{field:'per_buy_energy_quantity',align:'center'}">每次购买能量</th>
                                <th lay-data="{field:'total_buy_energy_quantity',align:'center'}">总已购买能量</th>
                                <th lay-data="{field:'total_used_trx',align:'center'}">总已花费trx</th>
                                <th lay-data="{field:'total_buy_quantity',align:'center'}">总已购买次数</th>
                                <th lay-data="{field:'max_buy_quantity',align:'center'}">最大购买次数</th>
                                <th lay-data="{field:'create_time',align:'center'}">创建时间</th>
                                <th lay-data="{field:'back_comments',align:'center'}">后台备注</th>
                                <th lay-data="{field:'comments',align:'center'}">备注</th>
                                <th lay-data="{toolbar:'#tpl_opt',align:'center',width:160}">操作</th>
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
            @if( auth('admin')->user()->can('修改智能托管') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" onclick="javascript:tools_add('修改智能托管',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_token':['机器人','span','@{{d.bot_token}}'],
                        'bot_username':['机器人名称','span','@{{d.bot_username}}'],
                        'tg_uid':['tg用户ID','text','@{{d.tg_uid}}'],
                        'status':['状态','select',[Status],'@{{d.status}}'],
                        'wallet_addr':['监控地址','text','@{{d.wallet_addr}}'],
                        'min_energy_quantity':['能量低于值购买','text','@{{d.min_energy_quantity}}'],
                        'per_buy_energy_quantity':['每次购买能量数量','text','@{{d.per_buy_energy_quantity}}'],
                        'max_buy_quantity':['最大购买次数','text','@{{d.max_buy_quantity}}'],
                        'back_comments':['后台备注','text','@{{d.back_comments}}'],
                    },
                    '{{route("admin.energy.aitrusteeship.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除智能托管') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.energy.aitrusteeship.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
            @if( auth('admin')->user()->can('刷新智能托管') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn edit_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.energy.aitrusteeship.refresh")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">刷新
                </button>
            @endif
        </div>
    </script>

    <script>
        var Status = @json($Status, JSON_UNESCAPED_UNICODE);
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
