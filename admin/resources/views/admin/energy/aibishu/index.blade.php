@extends('layouts.admin.app')
@section('nav-status-energy', 'active')
@section('nav-status-energy-aibishu', 'active')
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
                
                @if( auth('admin')->user()->can('添加地址笔数') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加地址笔数',
                                {
                                'bot_rid':['机器人','select',[botData],''],
                                'tg_uid':['TG用户ID','text',''],
                                'wallet_addr':['钱包地址','text','','地址必须是激活的'],
                                'total_buy_usdt':['总购买USDT','text','0'],
                                'max_buy_quantity':['最大购买次数','text','0'],
                                'total_buy_quantity':['已购买次数','text','0'],
                                'bishu_stop_day':['滞留暂停天数','text','0'],
                                'back_comments':['后台备注','text','']
                                },
                                '{{route("admin.energy.aibishu.add")}}',get_online_data)">添加地址笔数
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 需要在 机器人能量 模块，开启对应机器人的笔数套餐，并设置对应价格<p>
                    2. 当状态为 管理员关闭 时，用户不能开启<p>
                    3. 默认每次代理能量都是65000的能量，可以改为131000，仅有这两种<p>
                    4. 下单状态 如一直为<b>下单中</b>，先查看备注(看失败原因是什么)，如需要重新下单，点击刷新<p>
                    5. 滞留暂停天数：当笔数超过这么多天都未使用时，自动暂停，0表示无限制
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.energy.aibishu.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <!--<th lay-data="{field:'bot_token',align:'center'}">机器人</th>-->
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'tg_uid',align:'center'}">tg用户ID</th>
                                <th lay-data="{field:'wallet_addr',align:'center'}">地址</th>
                                <th lay-data="{field:'status_val',align:'center'}">状态</th>
                                <th lay-data="{field:'is_buy_val',align:'center'}">下单状态</th>
                                <th lay-data="{field:'current_bandwidth_quantity',align:'center'}">当前带宽</th>
                                <th lay-data="{field:'current_energy_quantity',align:'center'}">当前能量</th>
                                <th lay-data="{field:'total_buy_energy_quantity',align:'center'}">总已购买能量</th>
                                <th lay-data="{field:'total_buy_quantity',align:'center'}">已购买次数</th>
                                <th lay-data="{field:'max_buy_quantity',align:'center'}">最大购买次数</th>
                                <th lay-data="{field:'total_buy_usdt',align:'center'}">总转入USDT</th>
                                <th lay-data="{field:'bishu_stop_day',align:'center'}">滞留暂停天数</th>
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
            @if( auth('admin')->user()->can('修改笔数能量') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" onclick="javascript:tools_add('修改笔数能量',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_rid':['机器人名称','select',[botData],'@{{d.bot_rid}}'],
                        'tg_uid':['tg用户ID','text','@{{d.tg_uid}}'],
                        'status':['状态','select',[Status],'@{{d.status}}'],
                        'wallet_addr':['地址','text','@{{d.wallet_addr}}'],
                        'max_buy_quantity':['最大购买次数','text','@{{d.max_buy_quantity}}'],
                        'total_buy_quantity':['已购买次数','text','@{{d.total_buy_quantity}}'],
                        'bishu_stop_day':['滞留暂停天数','text','@{{d.bishu_stop_day}}'],
                        'back_comments':['后台备注','text','@{{d.back_comments}}'],
                    },
                    '{{route("admin.energy.aibishu.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除笔数能量') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.energy.aibishu.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
            @if( auth('admin')->user()->can('刷新笔数能量') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn edit_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.energy.aibishu.refresh")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
