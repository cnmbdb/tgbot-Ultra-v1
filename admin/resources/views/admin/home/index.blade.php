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
        layui.use(['jquery', 'admin', 'menu', 'http', 'element', 'form', 'layer'], function () {
            var $ = layui.jquery,
            admin = layui.admin,
            http = layui.http,
            menu = layui.menu,
            element = layui.element,
            form = layui.form,
            layer = layui.layer;

            var tabTimer;

            var FrameWH = function () {
                var h = $(window).height() - 164;
                $("iframe").css("height", h + "px");
                $("iframe").css("width", "100%");
            }

            var tab = {
                tabInit: function () {
                    if (tabTimer) {
                        clearTimeout(tabTimer);
                    }
                    tabTimer = setTimeout(function () {
                        var storageMenu = sessionStorage.getItem("menu");
                        if (!storageMenu) {
                            return;
                        }
                        menu = JSON.parse(storageMenu);
                        for (var i = 0; i < menu.length; i++) {
                            tab.tabAdd(menu[i].title, menu[i].url, menu[i].id);
                        }
                        var curMenu = sessionStorage.getItem("curMenu") ? JSON.parse(sessionStorage.getItem("curMenu")) : {};
                        if (curMenu && curMenu.id) {
                            var id = curMenu.id;
                            $('.layui-tab-title').find('layui-this').removeClass('layui-class');
                            $('.layui-tab-title li[lay-id="' + id + '"]').addClass('layui-this');
                            tab.tabChange(id);
                        } else {
                            $(".layui-tab-title li").eq(0).addClass('layui-this');
                            $('.layui-tab-content iframe').eq(0).parent().addClass('layui-show');
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
                    var parentFrame = $('.weIframe', window.parent.document);
                    var frameList = parentFrame && parentFrame.length ? parentFrame : $('.weIframe');

                    for (var i = 0; i < frameList.length; i++) {
                        if (frameList.eq(i).attr('tab-id') == id) {
                            tab.tabChange(id);
                            event.stopPropagation();
                            return;
                        }
                    };

                    tab.tabAdd(title, url, id);
                    tab.tabChange(id);
                }
            };

            // 暴露到全局
            window.wframe = wframe;
            window.tab = tab;

            // 菜单点击事件
            $('body').on('click', '.left-nav #side-menu li.menumulu', function (event) {
                var url = $(this).children('a').attr('_href');
                var title = $(this).find('a').text();
                var id = parseInt($(this).attr('id').split('menu')[1])
                console.log(url)
                console.log(title)
                console.log(id)
                wframe.openFrame(url, title, id)
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

            http.getMenu();
            admin.tab.tabInit();
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