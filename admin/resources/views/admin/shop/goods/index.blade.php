@extends('layouts.admin.app')
@section('nav-status-shop', 'active')
@section('nav-status-shop-goods', 'active')
@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
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

                @if( auth('admin')->user()->can('添加商品') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加商品',
                                {
                                'goods_name':['商品名称','text',''],
                                'goods_type':['商品类型','select',[goodsType],''],
                                'goods_trx_price':['商品价格(TRX)','text','',''],
                                'goods_usdt_price':['商品价格(USDT)','text','',''],
                                'show_notes':['显示说明','textarea','',''],
                                'seq_sn':['排序','text','0'],
                                'comments':['备注','text','']
                                },
                                '{{route("admin.shop.goods.add")}}',get_online_data)">添加商品
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 商品价格：当卡密价格为0时,取商品价格<p>
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.shop.goods.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'goods_name',align:'center'}">商品名称</th>
                                <th lay-data="{field:'goods_trx_price',align:'center'}">商品价格(TRX)</th>
                                <th lay-data="{field:'goods_usdt_price',align:'center'}">商品价格(USDT)</th>
                                <th lay-data="{field:'goods_type_val',align:'center'}">商品类型</th>
                                <th lay-data="{field:'seq_sn',align:'center'}">排序</th>
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
            @if( auth('admin')->user()->can('修改商品') || auth('admin')->user()->hasrole('超级管理员') )
                <a href ="{{route('admin.shop.goods.show')}}?rid=@{{ d.rid }}" class="layui-btn layui-btn-sm edit_button">修改</a>
            @endif
            @if( auth('admin')->user()->can('删除商品') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.shop.goods.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
        var Status = @json($Status, JSON_UNESCAPED_UNICODE);
        var goodsType = @json($goodsType, JSON_UNESCAPED_UNICODE);
        
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
                        form_func('{{route("admin.shop.goods.change_status")}}', {
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
