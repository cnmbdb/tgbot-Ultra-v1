<div class="sidebar-collapse left-nav">
    <ul class="nav metismenu" id="side-menu">
        <li class="nav-header">
            <div class="dropdown profile-element">
                <span class="block m-t-xs font-bold" style="color: #111827; font-size: 20px; letter-spacing: -0.025em; margin-bottom: 4px;">TG Ultra</span>
                <span class="block text-muted text-xs block" style="color: #6B7280; font-weight: 500;">Admin Panel</span>
            </div>
            <div class="logo-element" style="color: #111827;">
                TG
            </div>
        </li>
        <li class="@yield('nav-status-home', '')">
            <a href="javascript:;">
                <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span class="nav-label">控制台</span> <span class="fa arrow"></span>
            </a>
            <ul class="nav nav-second-level">
                <li class="@yield('nav-status-home', '')"><a href="{{route('admin.home')}}">仪表盘</a></li>
            </ul>
        </li>
        
        @if( auth('admin')->user()->can('机器人管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-telegram', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span class="nav-label">机器人列表</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('机器人列表') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-telegram-telegrambot', '')" id="menu50"><a _href="{{route('admin.telegram.telegrambot.index')}}">机器人列表</a></li>
                    @endif
                    @if( auth('admin')->user()->can('命令设置') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-telegram-command', '')" id="menu56"><a _href="{{route('admin.telegram.command.index')}}">命令设置</a></li>
                    @endif
                    @if( auth('admin')->user()->can('键盘设置') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-telegram-keyboard', '')" id="menu52"><a _href="{{route('admin.telegram.keyboard.index')}}">键盘设置</a></li>
                    @endif
                    @if( auth('admin')->user()->can('关键字回复') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-telegram-keyreply', '')" id="menu51"><a _href="{{route('admin.telegram.keyreply.index')}}">关键字回复</a></li>
                    @endif
                    @if( auth('admin')->user()->can('关键字键盘') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-telegram-keyreplyboard', '')" id="menu53"><a _href="{{route('admin.telegram.keyreplyboard.index')}}">关键字键盘</a></li>
                    @endif
                    @if( auth('admin')->user()->can('定时广告') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-telegram-telegrambotad', '')" id="menu54"><a _href="{{route('admin.telegram.telegrambotad.index')}}">定时广告</a></li>
                    @endif
                    @if( auth('admin')->user()->can('定时广告键盘') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-telegram-telegrambotadkeyboard', '')" id="menu55"><a _href="{{route('admin.telegram.telegrambotadkeyboard.index')}}">定时广告键盘</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('群组用户') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-groupuser', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span class="nav-label">群组用户</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('群组列表') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-groupuser-group', '')" id="menu70"><a _href="{{route('admin.groupuser.group.index')}}">群组列表</a></li>
                    @endif
                    @if( auth('admin')->user()->can('用户列表') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-groupuser-user', '')" id="menu71"><a _href="{{route('admin.groupuser.user.index')}}">用户列表</a></li>
                    @endif
                    @if( auth('admin')->user()->can('充值订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-groupuser-rechargeorder', '')" id="menu72"><a _href="{{route('admin.groupuser.rechargeorder.index')}}">充值订单</a></li>
                    @endif
                    @if( auth('admin')->user()->can('充值交易') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-groupuser-rechargetrade', '')" id="menu73"><a _href="{{route('admin.groupuser.rechargetrade.index')}}">充值交易</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('闪兑管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-transit', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    <span class="nav-label">闪兑钱包</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('闪兑钱包') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-transit-wallet', '')" id="menu10"><a _href="{{route('admin.transit.wallet.index')}}">闪兑钱包</a></li>
                    @endif
                    @if( auth('admin')->user()->can('闪兑币种') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-transit-walletcoin', '')" id="menu11"><a _href="{{route('admin.transit.walletcoin.index')}}">闪兑币种</a></li>
                    @endif
                    @if( auth('admin')->user()->can('闪兑交易') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-transit-trade', '')" id="menu12"><a _href="{{route('admin.transit.trade.index')}}">闪兑交易</a></li>
                    @endif
                    @if( auth('admin')->user()->can('闪兑黑钱包') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-transit-walletblack', '')" id="menu13"><a _href="{{route('admin.transit.walletblack.index')}}">闪兑黑钱包</a></li>
                    @endif
                    @if( auth('admin')->user()->can('闪兑用户') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-transit-userwallet', '')" id="menu14"><a _href="{{route('admin.transit.userwallet.index')}}">闪兑用户</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('能量管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-energy', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    <span class="nav-label">能量管理</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('能量平台') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-energy-platform', '')" id="menu60"><a _href="{{route('admin.energy.platform.index')}}">能量平台</a></li>
                    @endif
                    @if( auth('admin')->user()->can('机器人能量') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-energy-platformbot', '')" id="menu61"><a _href="{{route('admin.energy.platformbot.index')}}">机器人能量</a></li>
                    @endif
                    @if( auth('admin')->user()->can('能量套餐') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-energy-package', '')" id="menu62"><a _href="{{route('admin.energy.package.index')}}">能量套餐</a></li>
                    @endif
                    @if( auth('admin')->user()->can('能量订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-energy-order', '')" id="menu63"><a _href="{{route('admin.energy.order.index')}}">能量订单</a></li>
                    @endif
                    @if( auth('admin')->user()->can('钱包交易') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-energy-trade', '')" id="menu64"><a _href="{{route('admin.energy.trade.index')}}">钱包交易</a></li>
                    @endif
                    @if( auth('admin')->user()->can('快捷订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-energy-quick', '')" id="menu65"><a _href="{{route('admin.energy.quick.index')}}">快捷订单</a></li>
                    @endif
                    @if( auth('admin')->user()->can('智能托管') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-energy-aitrusteeship', '')" id="menu66"><a _href="{{route('admin.energy.aitrusteeship.index')}}">智能托管</a></li>
                    @endif
                    @if( auth('admin')->user()->can('笔数能量') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-energy-aibishu', '')" id="menu67"><a _href="{{route('admin.energy.aibishu.index')}}">笔数能量</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('会员管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-premium', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12l4 6-10 13L2 9Z"/><path d="M11 3 8 9l4 13 4-13-3-6"/></svg>
                    <span class="nav-label">会员管理</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('会员平台') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-premium-platform', '')" id="menu80"><a _href="{{route('admin.premium.platform.index')}}">会员平台</a></li>
                    @endif
                    @if( auth('admin')->user()->can('会员套餐') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-premium-package', '')" id="menu81"><a _href="{{route('admin.premium.package.index')}}">会员套餐</a></li>
                    @endif
                    @if( auth('admin')->user()->can('会员订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-premium-order', '')" id="menu82"><a _href="{{route('admin.premium.order.index')}}">会员订单</a></li>
                    @endif
                    @if( auth('admin')->user()->can('会员交易') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-premium-trade', '')" id="menu83"><a _href="{{route('admin.premium.trade.index')}}">会员交易</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('监控管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-monitor', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    <span class="nav-label">监控管理</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('机器人监控') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-monitor-bot', '')" id="menu20"><a _href="{{route('admin.monitor.bot.index')}}">机器人监控</a></li>
                    @endif
                    @if( auth('admin')->user()->can('监控钱包') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-monitor-wallet', '')" id="menu21"><a _href="{{route('admin.monitor.wallet.index')}}">监控钱包</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('归集管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-monitor', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                    <span class="nav-label">归集管理</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('归集钱包') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-collection-wallet', '')" id="menu101"><a _href="{{route('admin.collection.wallet.index')}}">归集钱包</a></li>
                    @endif
                    @if( auth('admin')->user()->can('归集记录') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-collection-list', '')" id="menu102"><a _href="{{route('admin.collection.list.index')}}">归集记录</a></li>
                    @endif
                </ul>
            </li>
        @endif
        
        @if( auth('admin')->user()->can('商城管理') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-shop', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    <span class="nav-label">商城管理</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('商品管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-shop-goods', '')" id="menu90"><a _href="{{route('admin.shop.goods.index')}}">商品管理</a></li>
                    @endif
                    @if( auth('admin')->user()->can('卡密管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-shop-cdkey', '')" id="menu91"><a _href="{{route('admin.shop.cdkey.index')}}">卡密管理</a></li>
                    @endif
                    @if( auth('admin')->user()->can('机器人商品') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-shop-bot', '')" id="menu92"><a _href="{{route('admin.shop.bot.index')}}">机器人商品</a></li>
                    @endif
                    @if( auth('admin')->user()->can('商品订单') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-shop-order', '')" id="menu93"><a _href="{{route('admin.shop.order.index')}}">商品订单</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if( auth('admin')->user()->can('系统设置') || auth('admin')->user()->hasrole('超级管理员'))
            <li class="@yield('nav-status-setting', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.1a2 2 0 0 1-1-1.72v-.51a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span class="nav-label">系统设置</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse{{ View::hasSection('nav-status-setting') || View::hasSection('nav-status-setting-config') ? ' in' : '' }}">
                    @if( auth('admin')->user()->can('配置信息') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu{{ View::hasSection('nav-status-setting-config') ? ' active' : '' }}" id="menu30"><a _href="{{route('admin.setting.config.index')}}">配置信息</a></li>
                    @endif
                </ul>
            </li>
        @endif

        @if( auth('admin')->user()->can('系统管理') || auth('admin')->user()->hasrole('超级管理员') )
            <li class="@yield('nav-status-system', '')">
                <a href="javascript:;">
                    <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <span class="nav-label">系统管理</span><span class="fa arrow"></span>
                </a>
                <ul class="nav nav-second-level collapse">
                    @if( auth('admin')->user()->can('管理员管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-system-admin', '')" id="menu40"><a _href="{{route('admin.system.admin.index')}}">管理员管理</a></li>
                    @endif
                    @if( auth('admin')->user()->can('权限管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-system-permission', '')" id="menu41"><a _href="{{route('admin.system.permission.index')}}">权限设置</a></li>
                    @endif
                    @if( auth('admin')->user()->can('角色管理') || auth('admin')->user()->hasrole('超级管理员') )
                        <li class="menumulu @yield('nav-status-system-role', '')" id="menu42"><a _href="{{route('admin.system.role.index')}}">角色管理</a></li>
                    @endif
                </ul>
            </li>
        @endif
    </ul>

</div>
