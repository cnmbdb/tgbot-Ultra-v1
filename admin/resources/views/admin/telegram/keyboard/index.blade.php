@extends('layouts.admin.app')
@section('nav-status-telegram', 'active')
@section('nav-status-telegram-keyboard', 'active')
@section('contents')
    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索键盘名称：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="keyboard_name" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加键盘名称') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加键盘名称',
                                {
                                'keyboard_type':['键盘类型','select',[KeyboardType],''],
                                'keyboard_name':['键盘名称','text',''],
                                'inline_type':['内联按钮类型','select',[InlineType],''],
                                'keyboard_value':['内联按钮值','text',''],
                                'seq_sn':['显示排序','text','']
                                },
                                '{{route("admin.telegram.keyboard.add")}}',get_online_data)">添加键盘名称
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 添加键盘名称后，需要前往 关键字键盘 设置关键字对应回复的键盘<p>
                    2. 键盘的显示效果：在聊天页面输入框的下方，增加键盘按钮<p>
                    3. 内联按钮的显示效果：在消息内容的下方，增加内联按钮<p>
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.telegram.keyboard.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'keyboard_type_val',align:'center'}">键盘类型</th>
                                <th lay-data="{field:'keyboard_name',align:'center'}">键盘名称</th>
                                <th lay-data="{field:'inline_type_val',align:'center'}">内联按钮类型</th>
                                <th lay-data="{field:'keyboard_value',align:'center'}">内联按钮值</th>
                                <th lay-data="{field:'seq_sn',align:'center'}">显示排序</th>
                                <th lay-data="{align:'center', templet:'#status',width:200}">状态</th>
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
            @if( auth('admin')->user()->can('修改键盘名称') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改键盘名称',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'keyboard_type':['内联按钮类型','select',[KeyboardType],'@{{d.keyboard_type}}'],
                        'keyboard_name':['键盘名称','text','@{{d.keyboard_name}}'],
                        'inline_type':['内联按钮类型','select',[InlineType],'@{{d.inline_type}}'],
                        'keyboard_value':['内联按钮值','text','@{{d.keyboard_value}}'],
                        'seq_sn':['显示排序','text','@{{d.seq_sn}}']
                    },
                    '{{route("admin.telegram.keyboard.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除键盘名称') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.telegram.keyboard.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
        </div>
    </script>
    
    <!--操作:修改状态-->
    <script type="text/html" id="status">
        <input type="checkbox" name="status" value="@{{d.status}}" id="@{{ d.rid }}" lay-skin="switch" lay-text="启动|禁用"
                   lay-filter="status" contents="@{{d.status}}" @{{ d.status== 0 ? 'checked' : '' }}><br/>
    </script>

    <script>
        var KeyboardStatus = @json($KeyboardStatus, JSON_UNESCAPED_UNICODE);
        var KeyboardType = @json($KeyboardType, JSON_UNESCAPED_UNICODE);
        var InlineType = @json($InlineType, JSON_UNESCAPED_UNICODE);
        
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
                        form_func('{{route("admin.telegram.keyboard.change_status")}}', {
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
