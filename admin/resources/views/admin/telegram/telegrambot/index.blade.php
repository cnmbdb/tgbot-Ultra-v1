@extends('layouts.admin.app')
@section('nav-status-telegram', 'active')
@section('nav-status-telegram-telegrambot', 'active')

@section('style')
<style>
    .ibox-title .com-search-btns { margin-left: 0; }
</style>
@endsection

@section('contents')

    <div class="row">
        <div class="col-lg-12">
            <div class="wrapper wrapper-content animated fadeInUp">
                <fieldset class="layui-elem-field">
                    <div class="ibox-title">
                        <form class="layui-form" id="user_form">
                            搜索机器人名称：
                            <div class="layui-inline">
                                <input class="layui-input layui-input-inline" lay-verify="" name="bot_username" value="" autocomplete="off">
                            </div>
                            <!--<div class="layui-btn-group">-->
                            <!--    <button class="layui-btn layui-btn-sm" lay-submit lay-filter="go" id="search">搜索</button>-->
                            <!--    <button class="layui-btn layui-btn-primary layui-btn-sm" type="reset" lay-submit lay-filter="reset">重置</button>-->
                            <!--</div>-->
                            @include('common.com_search')
                        </form>
                    </div>
                </fieldset>

                @if( auth('admin')->user()->can('添加机器人') || auth('admin')->user()->hasrole('超级管理员') )
                    <button class="layui-btn layui-btn-sm btn-add-bot" style="margin-top:6px;margin-left:10px;" onclick="javascript:tools_add('添加机器人',
                                {
                                'bot_token':['机器人token','text',''],
                                'bot_admin_username':['机器人管理员','text','','@开头的用户名'],
                                'comments':['备注','text','']
                                },
                                '{{route("admin.telegram.telegrambot.add")}}',get_online_data)">添加机器人
                    </button>
                @endif
                
                <div class="layui-card bot-tips-card">
                    <div class="layui-card-header">
                        <i class="fa fa-lightbulb-o"></i> 使用说明
                    </div>
                    <div class="layui-card-body">
                        <ul class="bot-tips-list">
                            <li class="bot-tip-item bot-tip-warning">
                                <span class="bot-tip-num">1</span>
                                <span class="bot-tip-text">添加或者修改机器人 token 后，请执行<strong>更新操作</strong>、<strong>Webhook 操作</strong>。</span>
                            </li>
                            <li class="bot-tip-item bot-tip-warning">
                                <span class="bot-tip-num">2</span>
                                <span class="bot-tip-text">申请机器人请用 BotFather。若需在群组使用，请打开设置：bot settings → group privacy 改为 turn off（关闭），allow groups 改为 turn groups on（开启）。</span>
                            </li>
                            <li class="bot-tip-item bot-tip-warning">
                                <span class="bot-tip-num">3</span>
                                <span class="bot-tip-text">若群组开启了审核入群，机器人可自动审核入群，但需先<strong>赋予机器人管理权限</strong>。</span>
                            </li>
                            <li class="bot-tip-item bot-tip-info">
                                <span class="bot-tip-num">4</span>
                                <span class="bot-tip-text">如需充值功能，请配置<strong>充值钱包地址</strong>和<strong>开始拉取交易时间</strong>；钱包地址不能与闪兑、能量、会员共用。</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="bot-list-wrap">
                    <div id="bot-card-loading" class="bot-card-loading layui-hide">
                        <i class="fa fa-spinner fa-spin"></i> 加载中…
                    </div>
                    <div id="bot-card-empty" class="bot-card-empty layui-hide">
                        暂无机器人，请点击「添加机器人」。
                    </div>
                    <div id="bot-card-grid" class="bot-card-grid"></div>
                    <div id="bot-card-pagination" class="bot-card-pagination"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        window.BOT_LIST_ROUTES = {
            getData: '{{ route("admin.telegram.telegrambot.get_data") }}',
            update: '{{ route("admin.telegram.telegrambot.update") }}',
            gengxin: '{{ route("admin.telegram.telegrambot.gengxin") }}',
            regwebhook: '{{ route("admin.telegram.telegrambot.regwebhook") }}',
            clone_config: '{{ route("admin.telegram.telegrambot.clone_config") }}',
            delete: '{{ route("admin.telegram.telegrambot.delete") }}',
            recharge: '{{ route("admin.telegram.telegrambot.recharge") }}'
        };
        window.BOT_LIST_PERMS = {
            canEdit: {{ (auth('admin')->user()->can('修改机器人') || auth('admin')->user()->hasrole('超级管理员')) ? 'true' : 'false' }},
            canUpdate: {{ (auth('admin')->user()->can('更新机器人') || auth('admin')->user()->hasrole('超级管理员')) ? 'true' : 'false' }},
            canWebhook: {{ (auth('admin')->user()->can('注册Webhook') || auth('admin')->user()->hasrole('超级管理员')) ? 'true' : 'false' }},
            isSuper: {{ auth('admin')->user()->hasrole('超级管理员') ? 'true' : 'false' }},
            canDelete: {{ (auth('admin')->user()->can('删除机器人') || auth('admin')->user()->hasrole('超级管理员')) ? 'true' : 'false' }},
            canRecharge: {{ auth('admin')->user()->hasrole('超级管理员') ? 'true' : 'false' }},
            canClone: {{ auth('admin')->user()->hasrole('超级管理员') ? 'true' : 'false' }}
        };
    </script>
    <script>
        layui.use(['layer'], function () {
            var layer = layui.layer;
            var limit = 12;
            var currPage = 1;
            var totalCount = 0;

            function showLoading(show) {
                $('#bot-card-loading').toggleClass('layui-hide', !show);
            }
            function renderCards(data, total) {
                totalCount = total;
                var grid = $('#bot-card-grid');
                grid.empty();
                if (!data || data.length === 0) {
                    $('#bot-card-empty').removeClass('layui-hide');
                    $('#bot-card-pagination').empty();
                    return;
                }
                $('#bot-card-empty').addClass('layui-hide');
                var perms = window.BOT_LIST_PERMS;
                var routes = window.BOT_LIST_ROUTES;
                $.each(data, function (i, d) {
                    d.rid = d.rid || d.id;
                    d.bot_token = d.bot_token || '';
                    d.bot_admin_username = d.bot_admin_username || '';
                    d.bot_firstname = d.bot_firstname || '';
                    d.bot_username = d.bot_username || '';
                    d.comments = d.comments || '';
                    d.create_time = d.create_time || '';
                    d.update_time = d.update_time || '';
                    d.recharge_wallet_addr = d.recharge_wallet_addr || '';
                    d.get_tx_time = d.get_tx_time || '';

                    var btns = [];
                    if (perms.canEdit) {
                        btns.push('<button type="button" class="layui-btn layui-btn-sm btn-toolbar-edit bot-card-btn" data-action="edit" data-rid="'+ d.rid +'" data-bot-token="'+ escapeHtml(d.bot_token) +'" data-bot-admin="'+ escapeHtml(d.bot_admin_username) +'" data-comments="'+ escapeHtml(d.comments) +'">修改</button>');
                    }
                    if (perms.canUpdate) {
                        btns.push('<button type="button" class="layui-btn layui-btn-sm btn-toolbar-update bot-card-btn" data-action="gengxin" data-rid="'+ d.rid +'">更新</button>');
                    }
                    if (perms.canWebhook) {
                        btns.push('<button type="button" class="layui-btn layui-btn-sm btn-toolbar-webhook bot-card-btn" data-action="regwebhook" data-rid="'+ d.rid +'" data-bot-username="'+ escapeHtml(d.bot_username) +'" data-bot-token="'+ escapeHtml(d.bot_token) +'">Webhook</button>');
                    }
                    if (perms.canClone) {
                        btns.push('<button type="button" class="layui-btn layui-btn-sm btn-toolbar-clone bot-card-btn" data-action="clone_config" data-rid="'+ d.rid +'">复制配置</button>');
                    }
                    if (perms.canDelete) {
                        btns.push('<button type="button" class="layui-btn layui-btn-sm btn-toolbar-delete bot-card-btn" data-action="delete" data-rid="'+ d.rid +'">删除</button>');
                    }
                    if (perms.canRecharge) {
                        btns.push('<button type="button" class="layui-btn layui-btn-sm btn-toolbar-recharge bot-card-btn" data-action="recharge" data-rid="'+ d.rid +'" data-bot-token="'+ escapeHtml(d.bot_token) +'" data-recharge-addr="'+ escapeHtml(d.recharge_wallet_addr) +'" data-get-tx-time="'+ escapeHtml(d.get_tx_time) +'">充值</button>');
                    }

                    var cardHtml = '<div class="layui-card bot-card" data-rid="'+ d.rid +'">' +
                        '<div class="layui-card-header">' +
                        '<span class="bot-card-title">'+ (d.bot_username ? '@'+ escapeHtml(d.bot_username) : 'ID '+ d.rid) +'</span>' +
                        (d.comments ? '<span class="bot-card-desc">'+ escapeHtml(d.comments) +'</span>' : '') +
                        '</div>' +
                        '<div class="layui-card-body">' +
                        '<dl class="bot-card-dl">' +
                        '<dt>ID</dt><dd>'+ d.rid +'</dd>' +
                        '<dt>机器人token</dt><dd class="bot-card-token">'+ escapeHtml(d.bot_token) +'</dd>' +
                        '<dt>机器人管理员</dt><dd>'+ escapeHtml(d.bot_admin_username) +'</dd>' +
                        '<dt>机器人显示名称</dt><dd>'+ escapeHtml(d.bot_firstname) +'</dd>' +
                        '<dt>机器人名称</dt><dd>'+ escapeHtml(d.bot_username) +'</dd>' +
                        '<dt>备注</dt><dd>'+ escapeHtml(d.comments) +'</dd>' +
                        '<dt>创建时间</dt><dd>'+ escapeHtml(d.create_time) +'</dd>' +
                        '<dt>修改时间</dt><dd>'+ escapeHtml(d.update_time) +'</dd>' +
                        '</dl>' +
                        '</div>' +
                        '<div class="layui-card-footer bot-card-footer bot-toolbar">' + btns.join('') + '</div>' +
                        '</div>';
                    grid.append(cardHtml);
                });
                renderPagination(total);
            }
            function escapeHtml(s) {
                if (s == null) return '';
                return String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }
            function renderPagination(total) {
                var pages = Math.ceil(total / limit) || 1;
                var wrap = $('#bot-card-pagination');
                wrap.empty();
                if (pages <= 1) return;
                var html = '<div class="layui-box layui-laypage layui-laypage-default">';
                if (currPage > 1) {
                    html += '<a href="javascript:;" class="layui-laypage-prev" data-page="'+ (currPage-1) +'">上一页</a>';
                }
                html += '<span class="layui-laypage-curr"><em class="layui-laypage-em"></em><em>'+ currPage +'</em></span> / '+ pages +' 页，共 '+ total +' 条';
                if (currPage < pages) {
                    html += '<a href="javascript:;" class="layui-laypage-next" data-page="'+ (currPage+1) +'">下一页</a>';
                }
                html += '</div>';
                wrap.html(html);
                wrap.find('a[data-page]').on('click', function () {
                    currPage = parseInt($(this).data('page'), 10);
                    loadData();
                });
            }
            function loadData() {
                var bot_username = ($('input[name="bot_username"]').val() || '').trim();
                showLoading(true);
                $.get(window.BOT_LIST_ROUTES.getData, {
                    page: currPage,
                    limit: limit,
                    bot_username: bot_username
                }).done(function (res) {
                    if (res && res.code === '0' && res.data) {
                        renderCards(res.data, res.count != null ? res.count : res.data.length);
                    } else {
                        renderCards([], 0);
                    }
                }).fail(function () {
                    renderCards([], 0);
                }).always(function () {
                    showLoading(false);
                });
            }
            // 搜索与重置
            $('form#user_form').on('submit', function (e) {
                e.preventDefault();
                currPage = 1;
                loadData();
                return false;
            });
            $(document).on('click', '#search', function (e) {
                e.preventDefault();
                currPage = 1;
                loadData();
            });
            $(document).on('click', '.btn-reset', function () {
                setTimeout(function () { currPage = 1; loadData(); }, 0);
            });
            // 卡片内按钮事件委托
            $(document).on('click', '.bot-card-btn', function () {
                var btn = $(this);
                var action = btn.data('action');
                var rid = btn.data('rid');
                var routes = window.BOT_LIST_ROUTES;
                if (action === 'edit') {
                    tools_add('修改机器人', {
                        'rid': ['','hidden', rid],
                        'bot_token': ['机器人token','text', btn.data('bot-token')],
                        'bot_admin_username': ['机器人管理员','text', btn.data('bot-admin')],
                        'comments': ['备注','text', btn.data('comments')]
                    }, routes.update, get_online_data);
                } else if (action === 'gengxin') {
                    confirm_opt(function(){ form_func(routes.gengxin, {rid: rid}, get_online_data); });
                } else if (action === 'regwebhook') {
                    tools_add('注册Webhook', {
                        'rid': ['','hidden', rid],
                        'bot_username': ['机器人名称','span', btn.data('bot-username')],
                        'bot_token': ['机器人token','span', btn.data('bot-token')]
                    }, routes.regwebhook, get_online_data);
                } else if (action === 'clone_config') {
                    confirm_opt(function(){ form_func(routes.clone_config, {rid: rid, from_rid: 1}, get_online_data); });
                } else if (action === 'delete') {
                    confirm_opt(function(){ form_func(routes.delete, {rid: rid}, get_online_data); });
                } else if (action === 'recharge') {
                    tools_add('修改机器人充值', {
                        'rid': ['','hidden', rid],
                        'bot_token': ['机器人token','span', btn.data('bot-token')],
                        'recharge_wallet_addr': ['充值钱包地址','text', btn.data('recharge-addr')],
                        'get_tx_time': ['开始拉取交易时间','datetime', btn.data('get-tx-time')]
                    }, routes.recharge, get_online_data);
                }
            });
            window.get_online_data = function () {
                loadData();
            };
            loadData();
        });
    </script>
@endsection
