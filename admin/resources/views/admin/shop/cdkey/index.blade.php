@extends('layouts.admin.app')
@section('nav-status-shop', 'active')
@section('nav-status-shop-cdkey', 'active')
@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            <div class="layui-input-inline">
                                <select name="goods_rid" id="goods_rid">
                                    <option value="">选择商品</option>
                                    @foreach($ShopGoods as $k => $val)
                                        <option value="{{$k}}">{{$val}}</option>
                                    @endforeach
                                </select>
                            </div>
                            搜索卡号/地址：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="cdkey_no" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加卡密') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加卡密',
                                {
                                'goods_rid':['商品','select',[ShopGoods],''],
                                'cdkey_no':['卡号','text','','卡号/钱包地址'],
                                'cdkey_pwd':['卡密','text','','卡密/钱包私钥'],
                                'cdkey_trx_price':['卡密价格(TRX)','text','0',''],
                                'cdkey_usdt_price':['卡密价格(USDT)','text','0',''],
                                'seq_sn':['排序','text','0','']
                                },
                                '{{route("admin.shop.cdkey.add")}}',get_online_data)">添加卡密
                    </button>
                @endif
                @if( auth('admin')->user()->can('批量添加') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm export_button" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('批量添加',
                                {
                                'goods_rid':['商品','select',[ShopGoods],''],
                                'cdkey_no':['卡号','textarea','','格式为：卡号,卡密  中间英文逗号分隔,一行一条数据,卡号(地址)在前,卡密(私钥)在后'],
                                'cdkey_trx_price':['卡密价格(TRX)','text','0',''],
                                'cdkey_usdt_price':['卡密价格(USDT)','text','0',''],
                                'seq_sn':['排序','text','0','']
                                },
                                '{{route("admin.shop.cdkey.batchadd")}}',get_online_data)">批量添加
                    </button>
                @endif
                @if( auth('admin')->user()->can('批量上架') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm add_button" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('批量上架',
                                {
                                'goods_rid':['商品','select',[ShopGoods],'']
                                },
                                '{{route("admin.shop.cdkey.batchshang")}}',get_online_data)">批量上架
                    </button>
                @endif
                @if( auth('admin')->user()->can('批量下架') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm add_button" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('批量下架',
                                {
                                'goods_rid':['商品','select',[ShopGoods],'']
                                },
                                '{{route("admin.shop.cdkey.batchxia")}}',get_online_data)">批量下架
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 卡密价格：当卡密价格为0时,取商品价格<p>
                    2. 批量添加：严格按照格式：<b>卡号,卡密</b>   一行一条数据,中间是英文逗号分隔,卡号(地址)在前,卡密(私钥)在后<p>
                    3. 编辑时，卡密需要重新输入编辑
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.shop.cdkey.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'goods_name',align:'center'}">商品</th>
                                <th lay-data="{field:'cdkey_no',align:'center'}">卡号</th>
                                <th lay-data="{field:'cdkey_pwd',align:'center'}">卡密</th>
                                <th lay-data="{field:'cdkey_trx_price',align:'center'}">卡密价格(TRX)</th>
                                <th lay-data="{field:'cdkey_usdt_price',align:'center'}">卡密价格(USDT)</th>
                                <th lay-data="{field:'seq_sn',align:'center'}">排序</th>
                                <th lay-data="{field:'status_val',align:'center'}">状态</th>
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
            @if( auth('admin')->user()->can('查看卡密') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" onclick="javascript:tools_add('查看卡密',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'cdkey_no':['卡号','span','@{{d.cdkey_no}}'],
                        'cdkey_pwd':['卡密','span','@{{d.cdkey_pwd_en}}'],
                        'cdkey_trx_price':['卡密价格(TRX)','span','@{{d.cdkey_trx_price}}'],
                        'cdkey_usdt_price':['卡密价格(USDT)','span','@{{d.cdkey_usdt_price}}']
                    },
                    '{{route("admin.shop.cdkey.show")}}',get_online_data);">查看
                </button>
            @endif
            @if( auth('admin')->user()->can('修改卡密') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改卡密',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'goods_rid':['商品','select',[ShopGoods],'@{{d.goods_rid}}'],
                        'cdkey_no':['卡号','text','@{{d.cdkey_no}}'],
                        'cdkey_pwd':['卡密','text','@{{d.cdkey_pwd}}'],
                        'cdkey_trx_price':['卡密价格(TRX)','text','@{{d.cdkey_trx_price}}'],
                        'cdkey_usdt_price':['卡密价格(USDT)','text','@{{d.cdkey_usdt_price}}'],
                        'status':['状态','select',[Status],'@{{d.status}}'],
                        'seq_sn':['排序','text','@{{d.seq_sn}}']
                    },
                    '{{route("admin.shop.cdkey.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除卡密') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.shop.cdkey.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
        </div>
    </script>

    <script>
        var Status = @json($Status, JSON_UNESCAPED_UNICODE);
        var ShopGoods = @json($ShopGoods, JSON_UNESCAPED_UNICODE);
        
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
