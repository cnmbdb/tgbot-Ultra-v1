<div class="sidebar-collapse left-nav">
    <ul class="nav metismenu" id="side-menu">
        <li class="nav-header">
            <div class="dropdown profile-element">
                <img alt="image" class="rounded-circle" src="{{asset('admin/img/profile_small.jpg')}}" style="width:50px;height:50px"/>
                <a data-toggle="dropdown" class="dropdown-toggle" href="javascript:;">
                    <span class="block m-t-xs font-bold">{{Auth::guard('admin')->user()->name}}</span>
                    <span class="text-muted text-xs block">{{auth('admin')->user()->getRoleNames()[0] ?? ''}}</span>
                    <a href="https://t.me/botfather" style="font-size:12px;color:#b6b900" target="_blank" class="fa fa-address-book"> 创建机器人</a>
                </a>
            </div>
            <div class="logo-element">
                {{Auth::guard('admin')->user()->name}}
            </div>
        </li>
        <li class="@yield('nav-status-home', '')">
            <a href="javascript:;"><i class="fa fa-home"></i> <span class="nav-label">主页</span> <span class="fa arrow"></span></a>
            <ul class="nav nav-second-level">
                <li class="@yield('nav-status-home', '')"><a href="{{route('admin.home')}}">主页</a></li>
            </ul>
        </li>
        
        @if( auth('admin')->user()->can('机器人管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-telegram', '')">
                <a href="javascript:;"><i class="fa fa-plane"></i> <span class="nav-label">机器人列表</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('机器人列表') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu50"><a _href="{{route('admin.telegram.telegrambot.index')}}">机器人列表</a></li>
                    @endif
                    @if( auth('admin')->user()->can('命令设置') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu56"><a _href="{{route('admin.telegram.command.index')}}">命令设置</a></li>
                    @endif
                    @if( auth('admin')->user()->can('键盘设置') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu52"><a _href="{{route('admin.telegram.keyboard.index')}}">键盘设置</a></li>
                    @endif
                    @if( auth('admin')->user()->can('关键字回复') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu51"><a _href="{{route('admin.telegram.keyreply.index')}}">关键字回复</a></li>
                    @endif
                    @if( auth('admin')->user()->can('关键字键盘') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu53"><a _href="{{route('admin.telegram.keyreplyboard.index')}}">关键字键盘</a></li>
                    @endif
                    @if( auth('admin')->user()->can('定时广告') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu54"><a _href="{{route('admin.telegram.telegrambotad.index')}}">定时广告</a></li>
                    @endif
                    @if( auth('admin')->user()->can('定时广告键盘') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu55"><a _href="{{route('admin.telegram.telegrambotadkeyboard.index')}}">定时广告键盘</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('群组用户') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-groupuser', '')">
                <a href="javascript:;"><i class="fa fa-users"></i> <span class="nav-label">群组用户</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('群组列表') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu70"><a _href="{{route('admin.groupuser.group.index')}}">群组列表</a></li>
                    @endif
                    @if( auth('admin')->user()->can('用户列表') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu71"><a _href="{{route('admin.groupuser.user.index')}}">用户列表</a></li>
                    @endif
                    @if( auth('admin')->user()->can('充值订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu72"><a _href="{{route('admin.groupuser.rechargeorder.index')}}">充值订单</a></li>
                    @endif
                    @if( auth('admin')->user()->can('充值交易') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu73"><a _href="{{route('admin.groupuser.rechargetrade.index')}}">充值交易</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('闪兑管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-transit', '')">
                <a href="javascript:;"><i class="fa fa-jsfiddle"></i> <span class="nav-label">闪兑钱包</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('闪兑钱包') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu10"><a _href="{{route('admin.transit.wallet.index')}}">闪兑钱包</a></li>
                    @endif
                    @if( auth('admin')->user()->can('闪兑币种') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu11"><a _href="{{route('admin.transit.walletcoin.index')}}">闪兑币种</a></li>
                    @endif
                    @if( auth('admin')->user()->can('闪兑交易') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu12"><a _href="{{route('admin.transit.trade.index')}}">闪兑交易</a></li>
                    @endif
                    @if( auth('admin')->user()->can('闪兑黑钱包') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu13"><a _href="{{route('admin.transit.walletblack.index')}}">闪兑黑钱包</a></li>
                    @endif
                    @if( auth('admin')->user()->can('闪兑用户') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu14"><a _href="{{route('admin.transit.userwallet.index')}}">闪兑用户</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('能量管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-energy', '')">
                <a href="javascript:;"><i class="fa fa-flash"></i> <span class="nav-label">能量管理</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('能量平台') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu60"><a _href="{{route('admin.energy.platform.index')}}">能量平台</a></li>
                    @endif
                    @if( auth('admin')->user()->can('机器人能量') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu61"><a _href="{{route('admin.energy.platformbot.index')}}">机器人能量</a></li>
                    @endif
                    @if( auth('admin')->user()->can('能量套餐') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu62"><a _href="{{route('admin.energy.package.index')}}">能量套餐</a></li>
                    @endif
                    @if( auth('admin')->user()->can('能量订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu63"><a _href="{{route('admin.energy.order.index')}}">能量订单</a></li>
                    @endif
                    @if( auth('admin')->user()->can('钱包交易') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu64"><a _href="{{route('admin.energy.trade.index')}}">钱包交易</a></li>
                    @endif
                    @if( auth('admin')->user()->can('快捷订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu65"><a _href="{{route('admin.energy.quick.index')}}">快捷订单</a></li>
                    @endif
                    @if( auth('admin')->user()->can('智能托管') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu66"><a _href="{{route('admin.energy.aitrusteeship.index')}}">智能托管</a></li>
                    @endif
                    @if( auth('admin')->user()->can('笔数能量') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu67"><a _href="{{route('admin.energy.aibishu.index')}}">笔数能量</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('会员管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-premium', '')">
                <a href="javascript:;"><i class="fa fa-diamond"></i> <span class="nav-label">会员管理</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('会员平台') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu80"><a _href="{{route('admin.premium.platform.index')}}">会员平台</a></li>
                    @endif
                    @if( auth('admin')->user()->can('会员套餐') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu81"><a _href="{{route('admin.premium.package.index')}}">会员套餐</a></li>
                    @endif
                    @if( auth('admin')->user()->can('会员订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu82"><a _href="{{route('admin.premium.order.index')}}">会员订单</a></li>
                    @endif
                    @if( auth('admin')->user()->can('会员交易') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu83"><a _href="{{route('admin.premium.trade.index')}}">会员交易</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('监控管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-monitor', '')">
                <a href="javascript:;"><i class="fa fa-drupal"></i> <span class="nav-label">监控管理</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('机器人监控') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu20"><a _href="{{route('admin.monitor.bot.index')}}">机器人监控</a></li>
                    @endif
                    @if( auth('admin')->user()->can('监控钱包') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu21"><a _href="{{route('admin.monitor.wallet.index')}}">监控钱包</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('归集管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-monitor', '')">
                <a href="javascript:;"><i class="fa fa-grav"></i> <span class="nav-label">归集管理</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('归集钱包') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu101"><a _href="{{route('admin.collection.wallet.index')}}">归集钱包</a></li>
                    @endif
                    @if( auth('admin')->user()->can('归集记录') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu102"><a _href="{{route('admin.collection.list.index')}}">归集记录</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('商城管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-shop', '')">
                <a href="javascript:;"><i class="fa fa-shopping-cart"></i> <span class="nav-label">商城管理</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('商品管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu90"><a _href="{{route('admin.shop.goods.index')}}">商品管理</a></li>
                    @endif
                    @if( auth('admin')->user()->can('卡密管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu91"><a _href="{{route('admin.shop.cdkey.index')}}">卡密管理</a></li>
                    @endif
                    @if( auth('admin')->user()->can('机器人商品') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu92"><a _href="{{route('admin.shop.bot.index')}}">机器人商品</a></li>
                    @endif
                    @if( auth('admin')->user()->can('商品订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu93"><a _href="{{route('admin.shop.order.index')}}">商品订单</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if( auth('admin')->user()->can('系统设置') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-setting', '')">
                <a href="javascript:;"><i class="fa fa-windows"></i> <span class="nav-label">系统设置</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('配置信息') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu30"><a _href="{{route('admin.setting.config.index')}}">配置信息</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if( auth('admin')->user()->can('系统管理') || auth('admin')->user()->hasrole('超级管理员') )
            <li class="@yield('nav-status-system', '')">
                <a href="javascript:;"><i class="fa fa-sun-o"></i> <span class="nav-label">系统管理</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('管理员管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu40"><a _href="{{route('admin.system.admin.index')}}">管理员管理</a></li>
                    @endif
                    @if( auth('admin')->user()->can('权限管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu41"><a _href="{{route('admin.system.permission.index')}}">权限设置</a></li>
                    @endif
                    @if( auth('admin')->user()->can('角色管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu" id="menu42"><a _href="{{route('admin.system.role.index')}}">角色管理</a></li>
                    @endif
                </ul>
            </li>
        @endif
    </ul>

</div>