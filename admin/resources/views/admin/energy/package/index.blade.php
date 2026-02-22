@extends('layouts.admin.app')
@section('nav-status-energy', 'active')
@section('nav-status-energy-package', 'active')
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
                            搜索套餐名称：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="package_name" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加能量套餐') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加能量套餐',
                                {
                                'bot_rid':['机器人','select',[botData],''],
                                'package_type':['套餐类型','select',[PackageType],''],
                                'package_name':['套餐名称','text',''],
                                'energy_amount':['套餐量','text','','必须大于等于65000'],
                                'energy_day':['套餐期限','select',[EnergyDay],''],
                                'trx_price':['TRX售价','text','','必须大于0'],
                                'agent_trx_price':['代理TRX售价','text','','机器人是代理的时候必填,代理成本价'],
                                'show_notes':['显示说明','textarea','',''],
                                'seq_sn':['排序','text','','数字越大越靠前']
                                },
                                '{{route("admin.energy.package.add")}}',get_online_data)">添加能量套餐
                    </button>
                @endif
                
                @if( auth('admin')->user()->can('复制能量套餐') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm export_button" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('复制能量套餐',
                                {
                                'paste_bot_rid':['覆盖机器人','select',[botData],''],
                                'copy_bot_rid':['来源机器人','select',[botData],'']
                                },
                                '{{route("admin.energy.package.copy_paste")}}',get_online_data)">复制能量套餐
                    </button>
                @endif
                
                @if( auth('admin')->user()->can('批量删除套餐') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm delete_button" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('批量删除套餐',
                                {
                                'bot_rid':['机器人','select',[botData],'']
                                },
                                '{{route("admin.energy.package.batchdelete")}}',get_online_data)">批量删除套餐
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 能量套餐可针对不同能量平台设置不同的能量套餐<p>
                    2. 能量套餐暂无USDT售价方式
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.energy.package.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">机器人</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'package_type_val',align:'center'}">套餐类型</th>
                                <th lay-data="{field:'package_name',align:'center'}">套餐名称</th>
                                <th lay-data="{field:'energy_amount',align:'center'}">套餐量</th>
                                <th lay-data="{field:'energy_day_val',align:'center'}">套餐期限</th>
                                <th lay-data="{field:'trx_price',align:'center'}">TRX售价</th>
                                <th lay-data="{field:'usdt_price',align:'center'}">USDT售价</th>
                                <th lay-data="{field:'seq_sn',align:'center'}">排序</th>
                                <th lay-data="{align:'center', templet:'#status',width:200}">状态</th>
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
            @if( auth('admin')->user()->can('修改能量套餐') || auth('admin')->user()->hasrole('超级管理员') )
                <a href ="{{route('admin.energy.package.show')}}?rid=@{{ d.rid }}" class="layui-btn layui-btn-sm edit_button">修改</a>
            @endif
            
            @if( auth('admin')->user()->can('删除能量套餐') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.energy.package.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
        var botData = @json($botData, JSON_UNESCAPED_UNICODE);
        var EnergyDay = @json($EnergyDay, JSON_UNESCAPED_UNICODE);
        var PackageType = @json($PackageType, JSON_UNESCAPED_UNICODE);
    
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
                        form_func('{{route("admin.energy.package.change_status")}}', {
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
