@extends('layouts.admin.app')
@section('nav-status-telegram', 'active')
@section('nav-status-telegram-command', 'active')
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
                            搜索命令：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="command" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>
                
                @if( auth('admin')->user()->can('添加命令') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加命令',
                                {
                                'bot_rid':['机器人ID','select',[botData],''],
                                'command_type':['命令类型','select',[CommandType],''],
                                'command':['命令','text',''],
                                'description':['描述','text',''],
                                'seq_sn':['排序','text','','数字越大越靠前']
                                },
                                '{{route("admin.telegram.command.add")}}',get_online_data)">添加命令
                    </button>
                @endif
                
                @if( auth('admin')->user()->can('同步命令') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-alert" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('同步命令',
                                {
                                'bot_rid':['同步的机器人','select',[botData],'']
                                },
                                '{{route("admin.telegram.command.sync")}}',get_online_data)">同步命令
                    </button>
                @endif
                
                @if( auth('admin')->user()->can('复制命令') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm export_button" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('复制命令',
                                {
                                'paste_bot_rid':['覆盖机器人','select',[botData],''],
                                'copy_bot_rid':['来源机器人','select',[botData],'']
                                },
                                '{{route("admin.telegram.command.copy_paste")}}',get_online_data)">复制命令
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 命令是在输入 / 后，可以快捷显示的命令。命令只能输入英文+数字，否则同步不成功<p>
                    2. 添加的命令，需要在 <b>关键字回复</b> 设置关键字，不然没回复内容。注意设置关键字的时候，命令前面加/。比如命令为start，则设置关键字为/start<p>
                    3. 编辑完机器人的命令后，请点击同步命令按钮，同步到tg机器人
                </div>
                
                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.telegram.command.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">机器人token</th>
                                <th lay-data="{field:'bot_firstname',align:'center'}">机器人显示名称</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'command',align:'center'}">命令</th>
                                <th lay-data="{field:'description',align:'center'}">描述</th>
                                <th lay-data="{field:'command_type_val',align:'center'}">命令类型</th>
                                <th lay-data="{field:'seq_sn',align:'center'}">排序</th>
                                <th lay-data="{field:'create_time',align:'center'}">创建时间</th>
                                <th lay-data="{field:'update_time',align:'center'}">修改时间</th>
                                <th lay-data="{toolbar:'#tpl_opt',align:'center'}">操作</th>
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
            @if( auth('admin')->user()->can('修改命令') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改命令',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'bot_rid':['机器人ID','select',[botData],'@{{d.bot_rid}}'],
                        'command_type':['命令类型','select',[CommandType],'@{{d.command_type}}'],
                        'command':['命令','text','@{{d.command}}'],
                        'description':['描述','text','@{{d.description}}'],
                        'seq_sn':['排序','text','@{{d.seq_sn}}'],
                    },
                    '{{route("admin.telegram.command.update")}}',get_online_data);">修改
                </button>
            @endif
            @if( auth('admin')->user()->can('删除命令') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.telegram.command.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
        </div>
    </script>

    <script>
        var botData = @json($botData, JSON_UNESCAPED_UNICODE);
        var CommandType = @json($CommandType, JSON_UNESCAPED_UNICODE);
    
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
