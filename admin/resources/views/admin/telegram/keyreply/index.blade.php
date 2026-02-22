@extends('layouts.admin.app')
@section('nav-status-telegram', 'active')
@section('nav-status-telegram-keyreply', 'active')
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
                            搜索关键字：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="monitor_word" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加关键字回复') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加关键字回复',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'key_type':['关键字类型','select',[KeyreplyKeytype],''],
                                'monitor_word':['关键字','text',''],
                                'reply_content':['回复内容','textarea','',''],
                                'opt_type':['操作指令','select',[KeyreplyOpttype],'']
                                },
                                '{{route("admin.telegram.keyreply.add")}}',get_online_data)">添加关键字回复
                    </button>
                @endif
                
                @if( auth('admin')->user()->can('复制关键字') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm export_button" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('复制关键字',
                                {
                                'paste_bot_rid':['覆盖机器人','select',[botData],''],
                                'copy_bot_rid':['来源机器人','select',[botData],'']
                                },
                                '{{route("admin.telegram.keyreply.copy_paste")}}',get_online_data)">复制关键字
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 关键字一次可添加多个，用英文逗号隔开！每个机器人的关键字回复单独设置<br>
                    2. 如果需要上传回复图片，编辑上传。回复内容可以加字体效果，详情看主页的描述
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.telegram.keyreply.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">机器人token</th>
                                <th lay-data="{field:'bot_firstname',align:'center'}">机器人显示名称</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'key_type_val',align:'center'}">关键字类型</th>
                                <th lay-data="{field:'monitor_word',align:'center'}">关键字</th>
                                <th lay-data="{field:'reply_photo',align:'center',templet:function (d) {return '<img src=&#34;'+d.reply_photo+'&#34;>';}}">回复图片</th>
                                <th lay-data="{field:'reply_content',align:'center'}">回复内容</th>
                                <th lay-data="{field:'opt_type_val',align:'center'}">操作指令</th>
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
            @if( auth('admin')->user()->can('修改关键字回复') || auth('admin')->user()->hasrole('超级管理员') )
                <a href ="{{route('admin.telegram.keyreply.show')}}?rid=@{{ d.rid }}" class="layui-btn layui-btn-sm edit_button">修改</a>
            @endif
            
            @if( auth('admin')->user()->can('删除关键字回复人') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.telegram.keyreply.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
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
        var KeyreplyStatus = @json($KeyreplyStatus, JSON_UNESCAPED_UNICODE);
        var KeyreplyKeytype = @json($KeyreplyKeytype, JSON_UNESCAPED_UNICODE);
        var KeyreplyOpttype = @json($KeyreplyOpttype, JSON_UNESCAPED_UNICODE);
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
                        form_func('{{route("admin.telegram.keyreply.change_status")}}', {
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
