<!DOCTYPE html>
<html lang="en">
    
@include('layouts.admin._head')
@include('common.tools')
@include('common.modal')
@section('nav-status-home', 'active')

<body class="zhuye">
    <div id="wrapper">
        <nav class="navbar-default navbar-static-side" role="navigation">
            {{--菜单栏--}}
            @include('layouts.admin._navleft')
        </nav>

        <div id="page-wrapper" class="gray-bg dashbard-1">
            @if(!empty($show_license_warning))
            <div class="alert alert-warning" style="margin: 10px 15px;">
                请先激活系统授权后才能使用 &nbsp;
                <a href="{{ route('admin.setting.config.index', ['activate' => 1]) }}" style="color: #007bff; font-weight: 600;">点击此处前往激活</a>
            </div>
            @endif
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    {{--顶部导航栏--}}
                    @include('layouts.admin._navbar')
                </nav>
            </div>

            <div class="layui-tab tab" lay-filter="wenav_tab" id="WeTabTip" lay-allowclose="true">
            <ul class="layui-tab-title" id="tabName" style="display: none !important;">
                <li class="layui-this" lay-id="0">仪表台</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    @include('admin.home.content')
                </div>
            </div>
            </div>
            <div style="padding-bottom: 30px;"></div>
            <div class="footer">
                <div class="float-right">
         
                </div>
                <div>
                 
                </div>
            </div>
        </div>

    </div>

    <!-- Mainly scripts -->
    <script src="{{asset('admin/js/jquery-form.js')}}"></script>
    <script src="{{asset('admin/js/popper.min.js')}}"></script>
    <script src="{{asset('admin/js/bootstrap.js')}}"></script>
    <script src="{{asset('admin/js/plugins/metisMenu/jquery.metisMenu.js')}}"></script>
    <script src="{{asset('admin/js/plugins/slimscroll/jquery.slimscroll.min.js')}}"></script>

    <!-- Custom and plugin javascript -->
    <script src="{{asset('admin/js/inspinia.js')}}"></script>
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

            // 窗口大小改变时防抖调整 iframe 高度
            $(window).on('resize', function () {
                FrameWH();
            });

            // 优化：检查 tab 是否已存在，避免重复创建
            function switchToExistingTab(id) {
                var $tabs = $('.layui-tab-title li', window.parent.document);
                var $tab = $tabs.filter('[lay-id="' + id + '"]');
                if ($tab.length) {
                    var topLayui = parent === self ? layui : top.layui;
                    topLayui.element.tabChange('wenav_tab', id);
                    FrameWH();
                    return true;
                }
                return false;
            }

            // 从左侧菜单 DOM 构建菜单并写入 sessionStorage（不依赖缺失的 admin/menu/http 模块）
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
                        // 只恢复当前选中的 tab，不预加载所有菜单为 tab（避免同时加载大量 iframe）
                        var curMenu = sessionStorage.getItem("curMenu") ? JSON.parse(sessionStorage.getItem("curMenu")) : {};
                        if (curMenu && curMenu.id) {
                            var id = curMenu.id;
                            var found = $('.layui-tab-title li[lay-id="' + id + '"]').length;
                            if (found) {
                                $('.layui-tab-title').find('.layui-this').removeClass('layui-this');
                                $('.layui-tab-title li[lay-id="' + id + '"]').addClass('layui-this');
                                tab.tabChange(id);
                                // 页面加载时恢复菜单高亮
                                var menuId = 'menu' + id;
                                $('.left-nav #side-menu li.menumulu').removeClass('active');
                                $('.left-nav #side-menu li.menumulu#' + menuId).addClass('active');
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
                    // 切换 tab 时同步菜单高亮
                    var menuId = 'menu' + id;
                    $('.left-nav #side-menu li.menumulu').removeClass('active');
                    $('.left-nav #side-menu li.menumulu#' + menuId).addClass('active');
                },
                tabDeleteAll: function (ids) {
                    var topLayui = parent === self ? layui : top.layui;
                    $.each(ids, function (i, item) {
                        topLayui.element.tabDelete("wenav_tab", item);
                    })
                }
            };

            var wframe = {
                openFrame: function (url, title, id) {
                    // 检查 tab 是否已存在，存在则直接切换，避免重复创建
                    if (switchToExistingTab(id)) return;
                    tab.tabAdd(title, url, id);
                    tab.tabChange(id);
                    FrameWH();
                }
            };

            // 暴露到全局
            window.wframe = wframe;
            window.tab = tab;

            // 未授权时点击「配置信息」整页跳转，避免 iframe 被重定向后仍显示首页
            var needFullPageForConfig = @json(!empty($show_license_warning));
            var configMenuId = 30;

            // 菜单点击事件 - 点击时设置高亮
            $('body').on('click', '.left-nav #side-menu li.menumulu', function (event) {
                // 移除所有菜单的 active
                $('.left-nav #side-menu li.menumulu').removeClass('active');
                // 给当前点击的菜单添加 active
                $(this).addClass('active');

                var url = $(this).children('a').attr('_href');
                var title = $(this).find('a').text().trim();
                var idStr = $(this).attr('id') || '';
                var id = parseInt(idStr.replace('menu', ''), 10);
                if (isNaN(id)) return;
                if (needFullPageForConfig && id === configMenuId) {
                    window.top.location.href = url;
                    return;
                }
                // 配置信息在 iframe 内打开时，使用 embed=1 隐藏其自身的菜单/顶部栏，避免双重导航
                if (id === configMenuId && url) {
                    url = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'embed=1';
                }
                wframe.openFrame(url, title, id);
            });

            // Tab 切换时同步菜单高亮
            $(document).on('click', '.layui-tab-title li', function () {
                var layId = $(this).attr('lay-id');
                if (!layId) return;
                // 根据 lay-id 找到对应的菜单项
                var menuId = 'menu' + layId;
                $('.left-nav #side-menu li.menumulu').removeClass('active');
                $('.left-nav #side-menu li.menumulu#' + menuId).addClass('active');
            });

            // Tab加载后刷新
            window.reloadTab = function (which) {
                var len = $('.layui-tab-title').children('li').length;
                var layId = $(which).attr('lay-id');
                var i = 1;
                if ($(which).attr('data-bit')) {
                    return false;
                } else {
                    $(which).attr('data-bit', i);
                    var frame = $('.weIframe[tab-id=' + layId + ']');
                    frame.attr('src', frame.attr('src'));
                }
            }

            $(function () {
                buildMenuFromDom();
                tab.tabInit();
                if (showFooter) {
                var currentY = new Date().getFullYear();
                $('#footer').show();
            } else {
                $('.left-nav, .page-content').css({ 'bottom': 0 });
                    $('#footer').hide();
                }
            });
        });
    </script>
</body>
</html>