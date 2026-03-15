@php
    $isEmbed = request()->get('embed') == 1;
@endphp
<!DOCTYPE html>
<html lang="en">
@include('layouts.admin._head')
@include('common.tools')
@include('common.modal')
@section('nav-status-setting', 'active')
@section('nav-status-setting-config', 'active')
<body class="zhuye">
<link href="{{asset('admin/css/config.css')}}" rel="stylesheet">
<style>
        .config-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.08); overflow: hidden; }
        .config-tab-content { padding: 24px; background: #fff; }
        .config-section { margin-bottom: 32px; }
        .config-section:last-child { margin-bottom: 0; }
        .config-page-wrap .layui-card {
            margin-bottom: 20px;
            border: 2px solid #00DC82;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,220,130,.08);
            background: #fff;
        }
        .config-page-wrap .layui-card:last-of-type { margin-bottom: 0; }
        .config-item-card .layui-card-body .z-input { margin-bottom: 10px; }
        .config-item-card .layui-card-body .tip { margin-bottom: 0; }
        .config-page-wrap .layui-card-header {
            font-size: 15px; font-weight: 600; color: #111827;
            border-bottom: 1px solid #e5e7eb; padding: 22px 20px; background: #fff;
            margin: 0; line-height: 1; border-radius: 10px 10px 0 0;
            display: flex; align-items: center;
        }
        .config-page-wrap .layui-card-header::before { display: none !important; }
        .config-page-wrap .layui-card-header { background: transparent !important; }
        .config-page-wrap .layui-card-header .layui-icon {
            margin-right: 12px;
            color: #00DC82;
            font-size: 16px;
            vertical-align: middle;
        }
        .config-page-wrap .layui-card-body { padding: 20px; }
        .config-form-row { padding: 20px 0; border-bottom: 1px solid #e5e7eb; }
        .config-form-row:last-of-type { border-bottom: none; }
        .config-form-row .label { display: block; font-weight: 600; color: #374151; font-size: 14px; margin-bottom: 10px; line-height: 1.4; }
        .config-form-row .field { display: block; max-width: 560px; }
        .config-form-row .z-input { width: 100%; padding: 12px 14px; font-size: 14px; border: 1px solid #d1d5db; border-radius: 8px; background: #fff; color: #111; transition: border-color .2s, box-shadow .2s; }
        .config-form-row .z-input:focus { outline: none; border-color: #00DC82; box-shadow: 0 0 0 3px rgba(0,220,130,.15); }
        .config-form-row .z-input::placeholder { color: #9ca3af; }
        .config-form-row .tip { margin: 10px 0 0; font-size: 13px; color: #6b7280; background: #f9fafb; padding: 10px 12px; border-radius: 8px; line-height: 1.5; }
        .config-form-row .tip i { color: #6b7280; margin-right: 6px; }
        .config-form-row .tip code { font-size: 12px; background: #e5e7eb; color: #374151; padding: 1px 6px; border-radius: 4px; }
        .config-actions { padding: 24px 0 0; margin-top: 8px; border-top: 1px solid #e5e7eb; }
        /* 胶囊按钮：绿框 + 绿字，文案居中 */
        .config-card .btn-capsule,
        .activate-next-wrap .btn-capsule {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 50px !important;
            padding: 10px 24px;
            min-height: 38px;
            line-height: 1 !important;
            font-weight: 600;
            font-size: 14px;
            background: #fff !important;
            color: #00DC82 !important;
            border: 2px solid #00DC82 !important;
            box-sizing: border-box;
        }
        .config-card .btn-capsule:hover {
            background: #f0fdf4 !important;
            color: #00a861 !important;
            border-color: #00a861 !important;
        }
        .config-card .btn-capsule-danger,
        .activate-next-wrap .btn-capsule-danger {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            line-height: 1 !important;
            min-height: 38px;
            padding: 10px 24px;
            box-sizing: border-box;
            background: #fff !important;
            color: #dc2626 !important;
            border: 2px solid #dc2626 !important;
        }
        .config-card .btn-capsule-danger:hover {
            background: #fef2f2 !important;
            color: #b91c1c !important;
            border-color: #b91c1c !important;
        }
        /* 刷新余额按钮：浅色背景 + 绿色边框，图标与文字上下居中 */
        .btn-refresh-balance {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px;
            min-height: 32px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 500;
            line-height: 1 !important;
            color: #00DC82 !important;
            background: #ecfdf5 !important;
            border: 1px solid #00DC82 !important;
            border-radius: 8px;
            box-sizing: border-box;
            cursor: pointer;
        }
        .btn-refresh-balance:hover {
            background: #d1fae5 !important;
            color: #059669 !important;
            border-color: #059669 !important;
        }
        .btn-refresh-balance:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .btn-refresh-balance .layui-icon {
            font-size: 14px;
            line-height: 1;
            vertical-align: middle;
        }
        .layui-table.config-table { border: none; }
        .layui-table.config-table td, .layui-table.config-table th { border: none; font-size: 14px; padding: 12px 0; }
        .layui-table.config-table .fu-title { font-weight: 600; color: #374151; width: 160px; }
        .tip{ border-radius: 8px; padding: 10px 12px; background: #f9fafb; font-size: 13px; color: #6b7280; }
        .layui-icon-ok-circle{ color: #16b872 !important; }
        .layui-icon-close-fill{ color: #ff5722 !important; }
        .thumb-box{ width: 150px; height: 150px; border: 1px solid #e5e7eb; border-radius: 8px; background: url('{{asset("admin/img/upload.png")}}') no-repeat center; background-size: 48px 48px; background-color: #f9fafb; }
        .thumb{ width: 150px; height: 150px; opacity: 0; cursor: pointer; border-radius: 8px; }
        .layui-form-switch{ margin-bottom: 10px; }

        /* 授权激活 - 与上方一致的白底绿框卡片 */
        .activate-next-wrap { max-width: 560px; }
        .activate-next-wrap .layui-card {
            margin-bottom: 20px;
            border: 2px solid #00DC82;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,220,130,.08);
        }
        .activate-next-wrap .layui-card:last-of-type { margin-bottom: 0; }
        .activate-next-wrap .activate-status-card { margin-bottom: 28px !important; }
        .activate-next-wrap .config-item-card .layui-card-header {
            font-size: 15px; font-weight: 600; color: #111827;
            border-bottom: 1px solid #e5e7eb; padding: 22px 20px; background: #fff;
            margin: 0; line-height: 1; border-radius: 10px 10px 0 0;
            display: flex; align-items: center;
        }
        .activate-next-wrap .config-item-card .layui-card-header .layui-icon {
            margin-right: 12px; color: #00DC82; font-size: 16px; vertical-align: middle;
        }
        .activate-next-wrap .config-item-card .layui-card-body .z-input { margin-bottom: 10px; }
        .activate-next-wrap .config-item-card .layui-card-body .tip { margin-bottom: 0; }
        .activate-status-card .layui-card-body {
            display: flex; align-items: center; flex-wrap: wrap; gap: 8px 20px;
            font-size: 14px; background: #fff;
        }
        .activate-status-card.activate-ok .layui-card-body {
            background: #f0fdf4;
            color: #065f46;
        }
        .activate-status-card.activate-no .layui-card-body {
            background: #fef2f2;
            color: #991b1b;
        }
        .activate-status-card .status-label { font-weight: 600; font-size: 13px; opacity: .9; }
        .activate-status-card .status-text { font-weight: 600; font-size: 15px; }
        .activate-status-card .status-text .layui-icon { margin-right: 6px; vertical-align: middle; }
        .activate-status-card .status-meta { color: inherit; opacity: .85; font-size: 13px; }
        .activate-next-wrap .config-actions { padding-top: 20px; margin-top: 8px; border-top: 1px solid #e5e7eb; }
</style>
@if(!$isEmbed)
    <div id="wrapper">
        <nav class="navbar-default navbar-static-side" role="navigation">
            {{--菜单栏--}}
            @include('layouts.admin._navleft')
        </nav>

        <div id="page-wrapper" class="gray-bg dashbard-1">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    {{--顶部导航栏--}}
                    @include('layouts.admin._navbar')
                </nav>
            </div>

            <div class="layui-tab tab" lay-filter="wenav_tab" id="WeTabTip" lay-allowclose="true">
                <ul class="layui-tab-title" id="tabName" style="display: none !important;">
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
@endif
                        @if(session('license_warning'))
                            <div class="alert alert-warning" style="margin: 10px 15px;">{{ session('license_warning') }}</div>
                        @endif

                        @if(!$licenseInfo || $licenseInfo['status'] !== 'active')
                            <div class="alert alert-warning" style="margin: 10px 15px;">
                                请先激活系统授权后才能使用完整功能 &nbsp;
                                <a href="{{ route('admin.setting.config.index', ['activate' => 1]) }}" style="color: #007bff; font-weight: 600;">点击此处前往激活</a>
                            </div>
                        @endif

                        <div class="config-page-wrap" style="padding: 24px 20px 40px; max-width: 960px; margin: 0 auto;">
                            <div class="config-card">
                                <div class="config-tab-content">
                                    <div class="config-section">
                                        <form class="layui-form" id="config-form1" action="{{route('admin.setting.config.update')}}" method="POST">
                                            <input type="hidden" name="config_type" value="1">
                                            @php
                                                $jobConfig = $data->firstWhere('config_key', 'job_url');
                                                $jobUrl = $jobConfig ? $jobConfig->config_val->url : 'http://tgbot-job:9503';
                                                $apiWebConfig = $data->firstWhere('config_key', 'api_web_url');
                                                $apiWebUrl = $apiWebConfig ? $apiWebConfig->config_val->url : 'http://host.docker.internal:4444/';
                                                $tronscanConfig = $data->firstWhere('config_key', 'tronscan_api_keys');
                                                $tronscanKeys = $tronscanConfig && isset($tronscanConfig->config_val->keys) ? $tronscanConfig->config_val->keys : '';
                                                $trongridConfig = $data->firstWhere('config_key', 'trongrid_api_keys');
                                                $trongridKeys = $trongridConfig && isset($trongridConfig->config_val->keys) ? $trongridConfig->config_val->keys : '';
                                            @endphp

                                            <!-- 每个配置项一张卡片；TON 支付接口已改为动态使用 API 连接 URL + /api/premium -->
                                            <div class="layui-card config-item-card">
                                                <div class="layui-card-header"><i class="layui-icon layui-icon-util"></i>Job 任务域名 URL</div>
                                                <div class="layui-card-body">
                                                    <input type="text" class="z-input layui-input" name="job_url[url]" autocomplete="off" value="{{ $jobUrl }}" placeholder="https://job.example.com">
                                                    <p class="tip"><i class="layui-icon layui-icon-tips"></i>{{ $jobConfig ? $jobConfig->comments : '任务域名 URL' }}</p>
                                                </div>
                                            </div>
                                            <div class="layui-card config-item-card">
                                                <div class="layui-card-header"><i class="layui-icon layui-icon-key"></i>TRONSCAN API Keys</div>
                                                <div class="layui-card-body">
                                                    <input type="text" class="z-input layui-input" name="tronscan_api_keys[keys]" autocomplete="off" value="{{ $tronscanKeys }}" placeholder="多条 key 用英文逗号分隔">
                                                    <p class="tip"><i class="layui-icon layui-icon-tips"></i>用于访问 <code>https://apilist.tronscanapi.com</code>，多个 key 用英文逗号分隔，系统随机轮询使用。</p>
                                                </div>
                                            </div>
                                            <div class="layui-card config-item-card">
                                                <div class="layui-card-header"><i class="layui-icon layui-icon-password"></i>TRONGRID API Keys</div>
                                                <div class="layui-card-body">
                                                    <input type="text" class="z-input layui-input" name="trongrid_api_keys[keys]" autocomplete="off" value="{{ $trongridKeys }}" placeholder="多条 key 用英文逗号分隔">
                                                    <p class="tip"><i class="layui-icon layui-icon-tips"></i>用于访问 <code>https://api.trongrid.io</code> 等接口，多个 key 逗号分隔，系统自动随机选择以降低限频风险。</p>
                                                    <div class="config-actions">
                                                        <button class="layui-btn btn-capsule" lay-submit lay-filter="formDemo">保存</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- 授权激活 -->
                                    <div class="config-section" id="activate-section">
                                        @php
                                            $licenseStatus = $licenseInfo['status'] ?? 'unactivated';
                                            $isActivated = $licenseStatus === 'active';
                                        @endphp
                                        <div class="activate-next-wrap">
                                            <!-- 状态卡片 -->
                                            <div class="layui-card activate-status-card {{ $isActivated ? 'activate-ok' : 'activate-no' }}">
                                                <div class="layui-card-body">
                                                    <span class="status-label">当前状态</span>
                                                    @if($isActivated)
                                                        <span class="status-text"><i class="layui-icon layui-icon-ok-circle"></i>已激活</span>
                                                        <span class="status-meta">最大机器人数：{{ $licenseInfo['max_bots'] ?? '-' }} · 过期时间：{{ $licenseInfo['expires_at'] ?? '永久' }}</span>
                                                    @else
                                                        <span class="status-text"><i class="layui-icon layui-icon-close-fill"></i>未激活</span>
                                                        @if(!empty($licenseInfo['message']))
                                                            <span class="status-meta">{{ $licenseInfo['message'] }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- 激活表单 - 每项一张卡片 -->
                                            <form class="layui-form" id="config-form2" action="{{route('admin.setting.config.activate')}}" method="POST">
                                                @csrf
                                                <div class="layui-card config-item-card">
                                                    <div class="layui-card-header"><i class="layui-icon layui-icon-link"></i>API 网站地址</div>
                                                    <div class="layui-card-body">
                                                        <input type="text" class="z-input layui-input" id="api_site_url" name="api_site_url" autocomplete="off" value="{{ $apiWebUrl ?? '' }}" placeholder="https://api.example.com">
                                                        <p class="tip"><i class="layui-icon layui-icon-tips"></i>请填写【API 授权系统】(API-web) 的网站地址，不要填本机器人后台地址。</p>
                                                        <!-- API 用户信息：紧跟在 API 网站地址下方 -->
                                                        <div class="config-api-user-wrap" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                                                            <div style="font-weight: 600; color: #374151; margin-bottom: 10px; font-size: 14px;"><i class="layui-icon layui-icon-user" style="color: #00DC82; margin-right: 6px;"></i>API 用户信息</div>
                                                            @if(!empty($licenseInfo['api_user']['username']))
                                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                                                <span style="color: #666;">用户名</span>
                                                                <span style="font-weight: 600; font-size: 15px;">{{ $licenseInfo['api_user']['username'] }}</span>
                                                            </div>
                                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                                                                <span style="color: #666;">余额</span>
                                                                <span style="display: flex; align-items: center; gap: 8px;">
                                                                    <span id="api-balance-display" style="font-weight: 600; font-size: 15px; color: #00DC82;">
                                                                        @if(isset($licenseInfo['api_user']['balance']) && $licenseInfo['api_user']['balance'] !== null)
                                                                            {{ $licenseInfo['api_user']['balance'] }} USDT
                                                                        @else
                                                                            <span class="api-balance-text" style="color: #999; font-size: 13px;">（未获取）</span>
                                                                        @endif
                                                                    </span>
                                                                    <button type="button" id="refresh-api-balance-btn" class="btn-refresh-balance"><span class="layui-icon layui-icon-refresh"></span><span>刷新</span></button>
                                                                </span>
                                                            </div>
                                                            @else
                                                            <p class="tip" style="margin: 0;"><i class="layui-icon layui-icon-tips"></i>填写下方激活码并点击「激活」后，将自动同步绑定该授权码的 API 用户名与余额。</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="layui-card config-item-card">
                                                    <div class="layui-card-header"><i class="layui-icon layui-icon-password"></i>激活码</div>
                                                    <div class="layui-card-body">
                                                        <input type="text" class="z-input layui-input" id="auth_code" name="auth_code" autocomplete="off" value="" placeholder="XXXX-XXXX-XXXX-XXXX">
                                                        <p class="tip"><i class="layui-icon layui-icon-tips"></i>从 API 授权系统获取的激活码。</p>
                                                        <div class="config-actions">
                                                            @if($isActivated)
                                                                <button type="button" class="layui-btn btn-capsule btn-capsule-danger" id="deactivate-btn" onclick="deactivateLicense()">解除授权</button>
                                                            @else
                                                                <button class="layui-btn btn-capsule" lay-submit lay-filter="formActivate">激活</button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
@if(!$isEmbed)
                    </div>
                </div>
            </div>
            <div style="padding-bottom: 30px;"></div>
            <div class="footer">
                <div class="float-right"></div>
                <div></div>
            </div>
        </div>
    </div>
@endif

    <!-- Mainly scripts -->
    <script src="{{asset('admin/js/jquery-form.js')}}"></script>
    <script src="{{asset('admin/js/popper.min.js')}}"></script>
    <script src="{{asset('admin/js/bootstrap.js')}}"></script>
    <script src="{{asset('admin/js/plugins/metisMenu/jquery.metisMenu.js')}}"></script>
    <script src="{{asset('admin/js/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>

    <!-- Custom and plugin javascript -->
    <!--<script src="{{asset('admin/js/inspinia.js')}}"></script>-->
    <script src="{{asset('admin/js/plugins/pace/pace.min.js')}}"></script>

    <script type="text/javascript">
        var showFooter = @json(isset($show_footer) ? (bool)$show_footer : false);
        layui.use(['jquery', 'element', 'form', 'layer'], function () {
            var $ = layui.jquery,
            element = layui.element,
            form = layui.form,
            layer = layui.layer;

            var tabTimer;
            var menu = [];
            var frameWHTimer;

            var FrameWH = function () {
                if (frameWHTimer) clearTimeout(frameWHTimer);
                frameWHTimer = setTimeout(function () {
                    var h = $(window).height() - 164;
                    $("iframe").css("height", h + "px");
                    $("iframe").css("width", "100%");
                }, 50);
            }

            $(window).on('resize', function () {
                FrameWH();
            });

            function switchToExistingTab(id) {
                var $tabs = $('.layui-tab-title', window.parent.document);
                var $tab = $tabs.find('[lay-id="' + id + '"]');
                if ($tab.length) {
                    var topLayui = parent === self ? layui : top.layui;
                    topLayui.element.tabChange('wenav_tab', id);
                    FrameWH();
                    return true;
                }
                return false;
            }

            function buildMenuFromDom() {
                var list = [];
                $('.left-nav #side-menu li.menumulu').each(function () {
                    var $li = $(this);
                    var idStr = $li.attr('id');
                    if (!idStr || idStr.indexOf('menu') !== 0) return;
                    var id = parseInt(idStr.replace('menu', ''), 10);
                    if (isNaN(id)) return;
                    var title = $li.find('a').first().text().trim();
                    var url = $li.children('a').attr('_href');
                    if (url) list.push({ id: id, title: title, url: url });
                });
                sessionStorage.setItem("menu", JSON.stringify(list));
                menu = list;
            }

            var tab = {
                tabInit: function () {
                    if (tabTimer) {
                        clearTimeout(tabTimer);
                    }
                    tabTimer = setTimeout(function () {
                        var storageMenu = sessionStorage.getItem("menu");
                        if (storageMenu) {
                            menu = JSON.parse(storageMenu);
                        }
                        var curMenu = sessionStorage.getItem("curMenu") ? JSON.parse(sessionStorage.getItem("curMenu")) : {};
                        if (curMenu && curMenu.id) {
                            var id = curMenu.id;
                            var found = $('.layui-tab-title li[lay-id="' + id + '"]').length;
                            if (found) {
                                $('.layui-tab-title').find('.layui-this').removeClass('layui-this');
                                $('.layui-tab-title li[lay-id="' + id + '"]').addClass('layui-this');
                                tab.tabChange(id);
                            } else {
                                $(".layui-tab-title li").eq(0).addClass('layui-this');
                                $('.layui-tab-content .layui-tab-item').eq(0).addClass('layui-show');
                            }
                        } else {
                            $(".layui-tab-title li").eq(0).addClass('layui-this');
                            $('.layui-tab-content .layui-tab-item').eq(0).addClass('layui-show');
                        }
                    }, 100);
                },
                tabAdd: function (title, url, id) {
                    var topLayui = parent === self ? layui : top.layui;
                    topLayui.element.tabAdd('wenav_tab', {
                        title: title,
                        content: '<iframe tab-id="' + id + '" frameborder="0" src="' + url + '" scrolling="yes" class="weIframe"></iframe>',
                        id: id
                    });
                    FrameWH();
                },
                tabDelete: function (id) {
                    var topLayui = parent === self ? layui : top.layui;
                    topLayui.element.tabDelete("wenav_tab", id);
                },
                tabChange: function (id) {
                    var topLayui = parent === self ? layui : top.layui;
                    topLayui.element.tabChange('wenav_tab', id);
                }
            };

            var wframe = {
                openFrame: function (url, title, id) {
                    if (switchToExistingTab(id)) return;
                    tab.tabAdd(title, url, id);
                    tab.tabChange(id);
                    FrameWH();
                }
            };

            window.wframe = wframe;
            window.tab = tab;

            $('body').on('click', '.left-nav #side-menu li.menumulu', function (event) {
                var url = $(this).children('a').attr('_href');
                var title = $(this).find('a').text().trim();
                var idStr = $(this).attr('id') || '';
                var id = parseInt(idStr.replace('menu', ''), 10);
                if (isNaN(id)) return;
                wframe.openFrame(url, title, id);
            });

            $(function () {
                buildMenuFromDom();
                tab.tabInit();
            });
        });
    </script>
    <script type="text/javascript">
        layui.use(['jquery', 'table', 'layer', 'element'], function () {
            var $ = layui.jquery,
                table = layui.table,
                element = layui.element,
                form = layui.form;

            // 表单提交
            form.on('submit(formDemo)', function(data){
                layer.load(1);
                $("#config-form"+data.field.config_type).ajaxSubmit({
                    'success':function(res){
                        layer.closeAll('loading');
                        if (res.code == 200) {
                            layer.msg('保存成功', {icon: 6});
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        } else {
                            layer.msg(res.msg, {icon: 5});
                        }
                    },
                    'error':function(res){
                        layer.closeAll('loading');
                        if (res.status == 422) {
                            var error = res.responseJSON.errors;
                            for(val in error) {
                                layer.msg(error[val][0], {icon: 5});
                                break;
                            }
                        } else {
                            layer.msg('修改失败');
                        }
                    }
                })

                return false;
            });

            // 激活表单提交
            form.on('submit(formActivate)', function(data){
                layer.load(1);
                $("#config-form2").ajaxSubmit({
                    'success':function(res){
                        layer.closeAll('loading');
                        if (res.code == 200) {
                            layer.msg('激活成功', {icon: 6});
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        } else {
                            layer.msg(res.msg || '激活失败', {icon: 5});
                        }
                    },
                    'error':function(res){
                        layer.closeAll('loading');
                        if (res.status == 422) {
                            var error = res.responseJSON.errors;
                            for(val in error) {
                                layer.msg(error[val][0], {icon: 5});
                                break;
                            }
                        } else {
                            layer.msg('激活失败');
                        }
                    }
                })

                return false;
            });

            // 解除授权
            window.deactivateLicense = function() {
                layer.confirm('确定要解除授权吗？解除后将无法使用机器人系统。', {
                    btn: ['确定', '取消']
                }, function(index){
                    layer.load(1);
                    $.post("{{route('admin.setting.config.deactivate')}}", {
                        _token: "{{ csrf_token() }}"
                    }, function(res){
                        layer.closeAll('loading');
                        if (res.code == 200) {
                            layer.msg('已解除授权', {icon: 6});
                            setTimeout(() => {
                                location.reload()
                            }, 1000);
                        } else {
                            layer.msg(res.msg || '解除失败', {icon: 5});
                        }
                    }).fail(function(){
                        layer.closeAll('loading');
                        layer.msg('请求失败');
                    });
                });
            };

            // 刷新 API 用户余额（事件委托，兼容 iframe 与动态内容）
            var refreshBalanceUrl = "{{ url()->route('admin.setting.config.api-user-balance') }}";
            $(document).off('click', '#refresh-api-balance-btn').on('click', '#refresh-api-balance-btn', function(){
                var $btn = $(this);
                var $display = $('#api-balance-display');
                if ($btn.prop('disabled')) return;
                $btn.prop('disabled', true).html('<span class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop"></span><span>刷新中</span>');
                $.ajax({
                    url: refreshBalanceUrl,
                    type: 'GET',
                    dataType: 'json'
                }).done(function(res){
                    $btn.prop('disabled', false).html('<span class="layui-icon layui-icon-refresh"></span><span>刷新</span>');
                    if (res && res.code == 200 && res.data) {
                        var bal = res.data.balance;
                        if (bal !== null && bal !== undefined && bal !== '') {
                            $display.text(String(bal) + ' USDT').css('color', '#00DC82');
                        } else {
                            $display.html('<span class="api-balance-text" style="color: #999; font-size: 13px;">（未获取）</span>');
                        }
                        layer.msg('已刷新', {icon: 6});
                    } else {
                        layer.msg((res && res.msg) ? res.msg : '获取失败', {icon: 5});
                        $display.html('<span class="api-balance-text" style="color: #999; font-size: 13px;">（未获取）</span>');
                    }
                }).fail(function(xhr){
                    $btn.prop('disabled', false).html('<span class="layui-icon layui-icon-refresh"></span><span>刷新</span>');
                    var msg = (xhr.responseJSON && xhr.responseJSON.msg) ? xhr.responseJSON.msg : (xhr.statusText || '请求失败');
                    layer.msg(msg, {icon: 5});
                });
            });

            // 缩略图预览
            $(".thumb").change(function(){
                var file=this.files[0];
                var url=window.URL.createObjectURL(file);
                $(this).parent().css({
                    'background-image':'url('+url+')',
                    'border':'none'
                });
            });

            // 未授权时自动滚动到授权激活区域
            @if($showActivateTab)
            setTimeout(function(){
                var activateSection = document.getElementById('activate-section');
                if (activateSection) {
                    activateSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 300);
            @endif
        });
    </script>
</body>
</html>
