@extends('layouts.admin.app')
@section('nav-status-premium', 'active')
@section('nav-status-premium-platform', 'active')
@section('contents')

    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索平台hash：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="platform_hash" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加会员平台') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加会员平台',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'platform_name':['会员平台','select',[PlatformName],''],
                                'platform_hash':['平台hash','text',''],
                                'platform_cookie':['平台cookie','text',''],
                                'tg_admin_uid':['TG管理员用户ID','text','','多个用英文逗号隔开'],
                                'tg_notice_obj_receive':['TG通知对象(收款)','text','','多个用英文逗号隔开'],
                                'tg_notice_obj_send':['TG通知对象(成功)','text','','多个用英文逗号隔开'],
                                'receive_wallet':['收款钱包','text','','只能为波场钱包地址'],
                                'get_tx_time':['开始拉取交易时间','datetime',''],
                                'comments':['备注','textarea','','']
                                },
                                '{{route("admin.premium.platform.add")}}',get_online_data)">添加会员平台
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 自己搭建的会员平台，要注意获取hash和cookie值，地址：<a href="https://fragment.com/premium" target="_blank">https://fragment.com/premium</a>。登录自己的ton钱包，按F12可以看到请求的hash和cookie值<p>
                    2. 使用之前需要先充值TON币，仅 <b>TG管理员用户ID</b> 对应的用户才可使用会员相关命令，命令见主页<p>
                    3. 收款钱包，不能和闪兑钱包用同一个。<b style="color:blue">一定要注意开始拉取交易时间</b><p>
                    4. 助记词为<a href="https://tonkeeper.com" target="_blank">telegram钱包</a>的钱包助记词，空格分隔，比如aaa bbb ccc ddd
                    5. 重要重要：使用的钱包需要v4r2的地址有ton币，且该地址一定要转出过一次ton给其他地址，完成初始化操作！！！
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.premium.platform.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">机器人</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'platform_name_val',align:'center'}">会员平台</th>
                                <th lay-data="{field:'tg_admin_uid',align:'center'}">TG管理员用户ID</th>
                                <th lay-data="{field:'platform_hash',align:'center'}">平台hash</th>
                                <th lay-data="{field:'platform_cookie',align:'center'}">平台cookie</th>
                                <th lay-data="{field:'platform_phrase',align:'center'}">助记词</th>
                                <th lay-data="{align:'center', templet:'#status',width:200}">状态</th>
                                <th lay-data="{field:'receive_wallet',align:'center'}">收款钱包</th>
                                <th lay-data="{field:'get_tx_time',align:'center'}">开始拉取交易时间</th>
                                <th lay-data="{field:'tg_notice_obj_receive',align:'center'}">TG通知对象(收款)</th>
                                <th lay-data="{field:'tg_notice_obj_send',align:'center'}">TG通知对象(成功)</th>
                                <th lay-data="{field:'create_time',align:'center'}">创建时间</th>
                                <!--<th lay-data="{field:'update_time',align:'center'}">修改时间</th>-->
                                <th lay-data="{field:'comments',align:'center'}">备注</th>
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
            @if( auth('admin')->user()->can('修改会员平台') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改会员平台',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_rid':['机器人ID','select',[botData],'@{{d.bot_rid}}'],
                        'platform_name':['会员平台','select',[PlatformName],'@{{d.platform_name}}'],
                        'tg_admin_uid':['TG管理员用户ID','text','@{{d.tg_admin_uid}}'],
                        'platform_hash':['平台hash','text','@{{d.platform_hash}}'],
                        'tg_notice_obj_receive':['TG通知对象(收款)','text','@{{d.tg_notice_obj_receive}}'],
                        'tg_notice_obj_send':['TG通知对象(成功)','text','@{{d.tg_notice_obj_send}}'],
                        'receive_wallet':['收款钱包','text','@{{d.receive_wallet}}'],
                        'get_tx_time':['开始拉取交易时间','datetime','@{{d.get_tx_time}}'],
                        'comments':['备注','textarea','@{{d.comments}}']
                    },
                    '{{route("admin.premium.platform.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('修改cookie') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" style="background-color:blueviolet !important" onclick="javascript:tools_add('修改cookie',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'platform_name':['会员平台','span','@{{d.platform_name}}'],
                        'platform_hash':['平台hash','span','@{{d.platform_hash}}'],
                        'platform_cookie':['平台cookie','text','@{{d.platform_cookie}}']
                    },
                    '{{route("admin.premium.platform.updateapikey")}}',get_online_data);">COOKIE
                </button>
            @endif
            @if( auth('admin')->user()->can('修改助记词') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm" style="background-color:blueviolet !important" onclick="javascript:tools_add('修改助记词',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'platform_name':['会员平台','span','@{{d.platform_name}}'],
                        'platform_hash':['平台hash','span','@{{d.platform_hash}}'],
                        'platform_phrase':['助记词','text','@{{d.platform_phrase}}']
                    },
                    '{{route("admin.premium.platform.updatephrase")}}',get_online_data);">助记词
                </button>
            @endif
            @if( auth('admin')->user()->can('删除会员平台') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.premium.platform.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
        var PlatformName = @json($PlatformName, JSON_UNESCAPED_UNICODE);
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
            
            //解决开关取消不回显的问题,修改状态
            form.on('switch(status)', function (obj) {
                confirm_opt(
                    () => {
                        form_func('{{route("admin.premium.platform.change_status")}}', {
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
