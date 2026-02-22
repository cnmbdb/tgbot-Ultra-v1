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
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                    {{--顶部导航栏--}}
                    @include('layouts.admin._navbar')
                </nav>
            </div>

            <div class="layui-tab tab" lay-filter="wenav_tab" id="WeTabTip" lay-allowclose="true">
            <ul class="layui-tab-title" id="tabName">
                <li lay-id="1" class="layui-this">主页</li>
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
        layui.use(['jquery', 'admin', 'menu', 'http'], function () {
            var $ = layui.jquery,
            admin = layui.admin,
            http = layui.http,
            menu = layui.menu;

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

        layui.define(['jquery', 'form', 'layer', 'element'], function (exports) {
            var $ = layui.jquery,
            form = layui.form,
            layer = layui.layer,
            element = layui.element;

            $('body').on('click', '.left-nav #side-menu li.menumulu', function (event) {
                var url = $(this).children('a').attr('_href');
                var title = $(this).find('a').text();
                var id = parseInt($(this).attr('id').split('menu')[1])
                console.log(url)
                console.log(title)
                console.log(id)
                wframe.openFrame(url, title, id)
            });

            /*
            * 重新计算iframe高度
            */
            var FrameWH = function () {
            var h = $(window).height() - 164;
            $("iframe").css("height", h + "px");
            $("iframe").css("width", "100%");
            }

            /*
            * tab触发事件：增加、删除、切换
            * tabInit 初始化加载菜单，页面初始化后加载一次
            * tabAdd 新增一个tab菜单
            */
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
                // console.log('...tabAdd', title, url, id); //sy-log
                var topLayui = parent === self ? layui : top.layui;
                topLayui.element.tabAdd('wenav_tab', {
                    title: title,
                    content: '<iframe tab-id="' + id + '" frameborder="0" src="' + url + '" scrolling="yes" class="weIframe"></iframe>',
                    id: id
                });
                FrameWH(); //计算框架高度
            },
            tabDelete: function (id) {
                var topLayui = parent === self ? layui : top.layui;
                topLayui.element.tabDelete("wenav_tab", id); //删除
            },
            tabChange: function (id) {
                // console.log('...tabChange', id); //sy-log
                //切换到指定Tab项
                var topLayui = parent === self ? layui : top.layui;
                    topLayui.element.tabChange('wenav_tab', id);
                },
                tabDeleteAll: function (ids) { //删除所有
                    var topLayui = parent === self ? layui : top.layui;
                    $.each(ids, function (i, item) {
                    topLayui.element.tabDelete("wenav_tab", item);
                })
                }
            };
            /**
            * frame操作
            * openFrame 打开frame窗口
            */
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
            }

            /*
            *Tab加载后刷新
            * 判断是刷新后第一次点击时，刷新frame子页面
            * */
            window.reloadTab = function (which) {
                var len = $('.layui-tab-title').children('li').length;
                var layId = $(which).attr('lay-id');
                var i = 1;
                if ($(which).attr('data-bit')) {
                    return false; //判断页面打开后第一次点击，执行刷新
                } else {
                    $(which).attr('data-bit', i);
                    var frame = $('.weIframe[tab-id=' + layId + ']');
                    frame.attr('src', frame.attr('src'));
                    // console.log("reload:" + $(which).attr('data-bit'));
                }
            }
        });
    </script>
</body>
</html>