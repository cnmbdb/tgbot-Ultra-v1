@extends('layouts.admin.app')
@section('nav-status-shop', 'active')
@section('nav-status-shop-bot', 'active')
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
                            商品名称：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="goods_name" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加机器人商品') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加机器人商品',
                                {
                                'bot_rid':['机器人','select',[botData],''],
                                'goods_rid':['商品','select',[ShopGoods],''],
                                'goods_trx_discount':['商品折扣(TRX)','text','1',''],
                                'goods_usdt_discount':['商品折扣(USDT)','text','1',''],
                                'comments':['备注','text','']
                                },
                                '{{route("admin.shop.bot.add")}}',get_online_data)">添加机器人商品
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 商品折扣：1表示无折扣，0.5表示5折，只能填写0到1的数字<p>
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.shop.bot.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">机器人token</th>
                                <th lay-data="{field:'bot_firstname',align:'center'}">机器人显示名称</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'goods_name',align:'center'}">商品名称</th>
                                <th lay-data="{field:'goods_trx_discount',align:'center'}">商品折扣(TRX)</th>
                                <th lay-data="{field:'goods_usdt_discount',align:'center'}">商品折扣(USDT)</th>
                                <th lay-data="{align:'center', templet:'#status',width:200}">状态</th>
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
            @if( auth('admin')->user()->can('修改机器人商品') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改机器人商品',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_rid':['机器人','select',[botData],'@{{d.bot_rid}}'],
                        'goods_rid':['商品','select',[ShopGoods],'@{{d.goods_rid}}'],
                        'goods_trx_discount':['商品折扣(TRX)','text','@{{d.goods_trx_discount}}'],
                        'goods_usdt_discount':['商品折扣(USDT)','text','@{{d.goods_usdt_discount}}'],
                        'comments':['备注','text','@{{d.comments}}']
                    },
                    '{{route("admin.shop.bot.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除机器人商品') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.shop.bot.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
        var ShopGoods = @json($ShopGoods, JSON_UNESCAPED_UNICODE);
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
                        form_func('{{route("admin.shop.bot.change_status")}}', {
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
