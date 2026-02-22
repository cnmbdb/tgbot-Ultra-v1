@extends('layouts.admin.app')
@section('nav-status-telegram', 'active')
@section('nav-status-telegram-telegrambotadkeyboard', 'active')
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
                            搜索广告内容：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="notice_ad" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加定时广告键盘') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm layui-btn-normal" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('添加定时广告键盘',
                                {
                                'ad_rid':['广告ID','select',[BotAdData],''],
                                'keyboard_rid':['键盘名称','select',[keyboardData],'']
                                },
                                '{{route("admin.telegram.telegrambotadkeyboard.add")}}',get_online_data)">添加定时广告键盘
                    </button>
                @endif
                
                @if( auth('admin')->user()->can('快捷添加定时广告键盘') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm add_button" style="margin-top:1px;margin-left:10px;" onclick="javascript:tools_add('快捷添加定时广告键盘',
                                {
                                'ad_rid':['广告ID','select',[BotAdData],''],
                                'keyboard_type':['键盘类型','select',[keyboardType],'']
                                },
                                '{{route("admin.telegram.telegrambotadkeyboard.fastadd")}}',get_online_data)">快捷添加定时广告键盘
                    </button>
                @endif
                
                <div style="margin-top:1px;margin-left:10px;color:red">
                    1. 添加了定时广告键盘后，机器人自动发送定时广告，并会拉起设置的键盘命令<p>
                    2. 同一个定时广告，可以设置多个关联键盘<p>
                    3. 注意：同一个定时广告，键盘类型只能设置为一种类型，比如都设置为键盘，或者设置为内联按钮<p>
                    4. 快捷添加定时广告键盘：给广告加上键盘类型对应的所有键盘
                </div>

                <div class="project-list">
                    <table class="layui-table"
                           lay-data="{url:'{{route("admin.telegram.telegrambotadkeyboard.get_data")}}',page:true,id:'userTable',loading:true}"
                           lay-filter="userTable">
                        <thead>
                            <tr>
                                <th lay-data="{field:'rid',align:'center',width:80}">ID</th>
                                <th lay-data="{field:'bot_token',align:'center'}">机器人token</th>
                                <th lay-data="{field:'bot_firstname',align:'center'}">机器人显示名称</th>
                                <th lay-data="{field:'bot_username',align:'center'}">机器人名称</th>
                                <th lay-data="{field:'ad_rid',align:'center'}">广告ID</th>
                                <th lay-data="{field:'keyboard_type_val',align:'center'}">键盘类型</th>
                                <th lay-data="{field:'keyboard_name',align:'center'}">键盘名称</th>
                                <th lay-data="{field:'inline_type_val',align:'center'}">内联按钮类型</th>
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
            @if( auth('admin')->user()->can('修改定时广告键盘') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn layui-btn-sm edit_button" onclick="javascript:tools_add('修改定时广告键盘',
                    {
                        'rid':['','hidden','@{{ d.rid }}'],
                        'ad_rid':['广告ID','select',[BotAdData],'@{{d.ad_rid}}'],
                        'keyboard_rid':['键盘名称','select',[keyboardData],'@{{d.keyboard_rid}}'],
                    },
                    '{{route("admin.telegram.telegrambotadkeyboard.update")}}',get_online_data);">修改
                </button>
            @endif
            
            @if( auth('admin')->user()->can('删除定时广告键盘') || auth('admin')->user()->hasrole('超级管理员') )
                <button class="layui-btn delete_button layui-btn-sm" onclick="javascript:confirm_opt(
                    ()=>{
                        form_func('{{route("admin.telegram.telegrambotadkeyboard.delete")}}',{'rid':'@{{d.rid}}'},get_online_data);
                    });">删除
                </button>
            @endif
        </div>
    </script>

    <script>
        var BotAdData = @json($BotAdData, JSON_UNESCAPED_UNICODE);
        var keyboardData = @json($keyboardData, JSON_UNESCAPED_UNICODE);
        var keyboardType = @json($KeyboardType, JSON_UNESCAPED_UNICODE);
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
        });

        function get_online_data() {
            layui.use('table', function () {
                layui.table.reload('userTable');
            });
        }
    </script>
@endsection
