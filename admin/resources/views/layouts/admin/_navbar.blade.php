<div class="navbar-header">
</div>
@php
    $adminUser = auth('admin')->user();
    $adminName = $adminUser ? $adminUser->name : '用户';
    $avatarSeeds = ['PixelA', 'PixelB', 'PixelC', 'PixelD', 'PixelE'];
    $avatarIndex = abs(crc32($adminName)) % count($avatarSeeds);
    $avatarUrl = 'https://api.dicebear.com/9.x/pixel-art/svg?seed=' . $avatarSeeds[$avatarIndex];
@endphp
<ul class="nav navbar-top-links navbar-right">
    <li style="padding: 20px; display: flex; align-items: center;">
        <span id="jobstatus">
            <span class="status-pill">
                <span class="status-icon-wrapper">
                    <span class="status-icon"></span>
                    <span class="status-pulse"></span>
                </span>
                <span id="job-text">10秒后检测</span>
            </span>
        </span>
    </li>
    <li class="topbar-actions">
        <a class="nav-pill-btn" onclick="clearJobCache()">
            <svg class="nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
            <span>清理JOB缓存</span>
        </a>
    </li>
    <li class="topbar-actions">
        <span class="nav-pill-btn" onclick="openChangePassword()">
            <svg class="nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>修改密码</span>
        </span>
    </li>
    <li class="topbar-actions">
        <button type="button" id="themeSwitch" class="theme-switch" aria-label="切换日夜模式">
            <span class="switch-icon switch-icon-light"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg></span>
            <span class="switch-icon switch-icon-dark"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg></span>
            <span class="switch-dot"></span>
        </button>
    </li>
    <li class="topbar-actions">
        <a href="javascript:;" class="topbar-account-link" title="{{ $adminName }}">
            <img src="{{ $avatarUrl }}" alt="{{ $adminName }}" class="topbar-avatar">
            <span class="topbar-account-name">{{ $adminName }}</span>
        </a>
    </li>
    <li>
        <a href="javascript:;" class="nav-pill-btn logout-btn" title="退出登录" onclick="logoutAdmin(event)">
            <svg class="nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            <span>退出</span>
        </a>
        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </li>
</ul>

<style>
.topbar-actions {
    padding: 14px 8px;
}
body.theme-dark .nav-icon,
body.theme-dark .switch-icon {
    color: #d1d5db;
}
.theme-switch {
    position: relative;
    width: 68px;
    height: 32px;
    border-radius: 999px;
    border: 1px solid #d1d5db;
    background: #e5e7eb;
    cursor: pointer;
    outline: none;
    transition: all .2s ease;
}
.theme-switch .switch-icon {
    position: absolute;
    top: 7px;
    color: #6b7280;
    font-size: 12px;
}
.theme-switch .switch-icon-light {
    left: 10px;
}
.theme-switch .switch-icon-dark {
    right: 10px;
}
.theme-switch .switch-dot {
    position: absolute;
    left: 3px;
    top: 3px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
    transition: transform .2s ease;
}
.topbar-icon-link {
    font-size: 19px;
    color: #676a6c !important;
    display: inline-flex !important;
    align-items: center;
}
.topbar-account-link {
    color: #676a6c !important;
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
}
.topbar-avatar {
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
}
.topbar-account-name {
    font-size: 14px;
    font-weight: 600;
}
body.theme-dark .theme-switch {
    background: #2563eb;
    border-color: #2563eb;
}
body.theme-dark .theme-switch .switch-dot {
    transform: translateX(36px);
}
body.theme-dark .theme-switch .switch-icon {
    color: rgba(255, 255, 255, .85);
}
body.theme-dark {
    background: #0b1220 !important;
    color: #d1d5db;
}
body.theme-dark #page-wrapper,
body.theme-dark .gray-bg {
    background: #0f172a !important;
}
body.theme-dark .navbar-static-top,
body.theme-dark .border-bottom {
    background: #0b1220 !important;
    border-color: #1f2937 !important;
}
body.theme-dark .layui-tab,
body.theme-dark .layui-tab-title,
body.theme-dark .layui-tab-content {
    background: #111827 !important;
    color: #d1d5db;
    border-color: #1f2937 !important;
}
body.theme-dark .layui-tab-title li {
    color: #9ca3af;
}
body.theme-dark .layui-tab-title .layui-this {
    color: #ffffff;
    background: #1f2937;
}
body.theme-dark .navbar-top-links > li > a,
body.theme-dark .navbar-top-links > li > span,
body.theme-dark .topbar-icon-link,
body.theme-dark .topbar-account-link {
    color: #d1d5db !important;
}
body.theme-dark .topbar-avatar {
    background: transparent;
}
body.theme-dark .footer {
    background: #0b1220 !important;
    border-top: 1px solid #1f2937 !important;
    color: #9ca3af !important;
}
body.theme-dark .nav-header {
    background: #0b1220 !important;
    background-image: none !important;
}
body.theme-dark .nav-header .logo-element {
    color: #ffffff;
    font-weight: bold;
}
</style>

<script>
    var check_job = setInterval('checkjob()', 10000); //10秒检查一次
    function checkjob(){
		$.ajax({
			type: 'get',
			url:  '{{route("admin.setting.config.checkjob")}}',
			async : false,
			success:function(data){
			    $('#job-text').text(data.msg);
                $('#jobstatus .status-pill').removeClass('error');
			},
			error:function(data){
			    $('#job-text').text(data.msg || "检测失败");
                $('#jobstatus .status-pill').addClass('error');
			}
		})
	}

    function clearJobCache() {
        confirm_opt(function () {
            form_func('{{route("admin.setting.config.clearjobcache")}}');
        });
    }

    function openChangePassword() {
        tools_add('修改密码', {
            oldpassword: ['原密码', 'password', '', '原密码'],
            xinpassword: ['新密码', 'password', '', '新密码'],
            qrpassword: ['确认密码', 'password', '', '确认密码']
        }, '{{route("admin.system.admin.change_password")}}');
    }

    function logoutAdmin(event) {
        event.preventDefault();
        document.getElementById('logout-form').submit();
    }

    (function () {
        var storageKey = 'admin_theme_mode';
        var themeSwitch = document.getElementById('themeSwitch');
        if (!themeSwitch) {
            return;
        }
        var savedMode = localStorage.getItem(storageKey);
        if (savedMode === 'dark') {
            document.body.classList.add('theme-dark');
        }
        themeSwitch.addEventListener('click', function () {
            document.body.classList.toggle('theme-dark');
            localStorage.setItem(storageKey, document.body.classList.contains('theme-dark') ? 'dark' : 'light');
        });
    })();

    </script>
