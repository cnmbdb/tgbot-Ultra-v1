@extends('layouts.admin.app')
@section('nav-status-system', 'active')
@section('nav-status-system-role', 'active')
@section('contents')

    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                @if( auth('admin')->user()->can('添加角色') || auth('admin')->user()->hasrole('超级管理员') )
                    <button
                        onclick="javascript:tools_add('添加角色',
                                {'name':['名称','text','']},
                                '{{route("admin.system.role.add")}}',get_online_data)"
                        class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;">添加角色
                    </button>
                @endif
                
                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.system.role.get_data")}}',page:true,id:'groupTable',loading:true}"
                           lay-filter="groupTable">
                        <thead>
                        <tr>
                            <th lay-data="{field:'id',align:'center',width:80}">ID</th>
                            <th lay-data="{field:'name',align:'center'}">名称</th>
                            <th lay-data="{field:'created_at',align:'center'}">创建时间</th>
                            <th lay-data="{toolbar:'#tpl_opt',align:'center',width:250}">操作</th>
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
    @{{# if(d.name != '超级管理员'){ }}
        <div class="layui-btn-group">
            @if( auth('admin')->user()->can('编辑角色权限') || auth('admin')->user()->hasrole('超级管理员') )
                <a href="/admin/system/role/show_permissions/@{{d.id}}" class="layui-btn layui-btn-xs layui-btn-normal edit-permission" >编辑权限</a>
            @endif
            @if( auth('admin')->user()->can('修改角色名称') || auth('admin')->user()->hasrole('超级管理员') )
                <a class="layui-btn layui-btn-xs" href="javascript:;" onclick="javascript:tools_add('修改角色名称',
                    {
                    'id':['','hidden','@{{d.id}}'],
                    'name':['名称','text','@{{d.name}}'],
                    },
                    '/admin/system/role/update',get_online_data);">修改</a>
            @endif
            @if( auth('admin')->user()->can('删除角色') || auth('admin')->user()->hasrole('超级管理员') )
                <a class="layui-btn delete_button layui-btn-xs" onclick="javascript:confirm_opt(() => {
                    form_func('/admin/system/role/del',{'id':'@{{d.id}}'},get_online_data);
                    })">删除</a>
            @endif
        </div>
    @{{# } }}
</script>
<script>
    var table,form, $;
    layui.use(['table', 'layer', 'jquery'], function () {
        table = layui.table,
        form = layui.form
        $ = layui.jquery;
    });

    function get_online_data() {
        layui.use(['table', 'layer'], function () {
            var table = layui.table;
            table.reload('groupTable')
        });
    };
</script>
@endsection
