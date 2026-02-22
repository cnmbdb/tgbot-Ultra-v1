@extends('layouts.admin.app')
@section('nav-status-system', 'active')
@section('nav-status-system-admin', 'active')
@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索用户：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify=""
                                       name="name" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加管理员') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('新增管理员用户',
                                {
                                'name':['用户名','text',''],
                                'password':['密码','password','',''],
                                'white_ip':['白名单IP','text','','不填不校验IP,多个英文逗号隔开'],
                                'status':['状态','radio',[{'1':'开启', '0':'禁用'}],'1']
                                },
                                '{{route("admin.system.admin.add")}}',get_online_data)">添加用户
                    </button>
                @endif

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.system.admin.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'id',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'name',align:'center'}">用户名</th>
                                {{-- <th lay-data="{field:'name',align:'center'}">昵称</th> --}}
                                {{-- <th lay-data="{field:'group_text',align:'center'}">用户组</th> --}}
                                <th lay-data="{field:'white_ip',align:'center'}">白名单IP</th>
                                <th lay-data="{field:'created_at',align:'center'}">创建时间</th>
                                <th lay-data="{toolbar:'#tpl_status',align:'center',width:100}">状态</th>
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
    <!--切换状态-->
    <script type="text/html" id="tpl_status">
        @if( auth('admin')->user()->can('修改管理员状态') || auth('admin')->user()->hasrole('超级管理员') )
            <input type="checkbox" name="status" value="@{{d.status}}" id="@{{ d.id }}" lay-skin="switch" lay-text="正常|禁用"
                   lay-filter="user_status" @{{ d.status== 1 ? 'checked' : '' }}>
        @endif
    </script>

    <!--操作:修改，删除-->
    <script type="text/html" id="tpl_opt">
        <div class="layui-btn-group">
            @if( auth('admin')->user()->can('修改管理员资料') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改管理员用户',
                    {
                        'id':['','hidden','@{{ d.id }}'],
                        'name':['用户账户','text','@{{d.name}}'],
                        'password':['密码','password','', '不填则不修改'],
                        'white_ip':['白名单IP','text','@{{d.white_ip}}', '不填不校验IP,多个英文逗号隔开'],
                        'role_id':['用户组','checkbox',[{{$roles}}],'@{{ d.role}}'],
                        'roles_before': ['', 'hidden', '@{{ d.role}}']
                    },
                    '{{route("admin.system.admin.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除管理员') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.system.admin.delete")}}',{'id':'@{{d.id}}'},get_online_data);
                    });">删除
                </button>
            @endif
        </div>
    </script>

    <script>
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

            //解决开关取消不回显的问题
            form.on('switch(user_status)', function (obj) {
                confirm_opt(
                    () => {
                        form_func('{{route("admin.system.admin.change_status")}}', {
                            'id': obj.elem.id,
                            'status': obj.value
                        }, get_online_data);
                    },
                    () => {
                        obj.elem.checked = !obj.elem.checked;
                        form.render('checkbox');
                    }, '要修改状态吗？'
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
