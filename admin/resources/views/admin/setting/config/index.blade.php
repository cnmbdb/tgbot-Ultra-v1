@extends('layouts.admin.app')
@section('nav-status-setting', 'active')
@section('nav-status-setting-config', 'active')
@section('style')
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
        .config-page-wrap .layui-card-header::before { display: none; }
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
    <link href="{{asset('admin/css/config.css')}}" rel="stylesheet">
@endsection
@section('contents')

    @if(session('license_warning'))
        <div class="alert alert-warning" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px;">{{ session('license_warning') }}</div>
    @endif

    <div class="config-card config-page-wrap">
        <div class="config-tab-content">
            <div class="config-section">
                <form class="layui-form" id="config-form1" action="{{route('admin.setting.config.update')}}" method="POST">
                    <input type="hidden" name="config_type" value="1">
                    @php
                        $jobConfig = $data->firstWhere('config_key', 'job_url');
                        $jobUrl = $jobConfig ? $jobConfig->config_val->url : 'http://tgbot-job:9503';
                        $tonConfig = $data->firstWhere('config_key', 'ton_url');
                        $tonUrl = $tonConfig ? $tonConfig->config_val->url : 'http://host.docker.internal:4444/api/premium';
                        $apiWebConfig = $data->firstWhere('config_key', 'api_web_url');
                        $apiWebUrl = $apiWebConfig ? $apiWebConfig->config_val->url : 'http://host.docker.internal:4444/';
                        $tronscanConfig = $data->firstWhere('config_key', 'tronscan_api_keys');
                        $tronscanKeys = $tronscanConfig && isset($tronscanConfig->config_val->keys) ? $tronscanConfig->config_val->keys : '';
                        $trongridConfig = $data->firstWhere('config_key', 'trongrid_api_keys');
                        $trongridKeys = $trongridConfig && isset($trongridConfig->config_val->keys) ? $trongridConfig->config_val->keys : '';
                    @endphp

                    <!-- 每个配置项一张卡片 -->
                    <div class="layui-card config-item-card">
                        <div class="layui-card-header"><i class="layui-icon layui-icon-util"></i>Job 任务域名 URL</div>
                        <div class="layui-card-body">
                            <input type="text" class="z-input layui-input" name="job_url[url]" autocomplete="off" value="{{ $jobUrl }}" placeholder="https://job.example.com">
                            <p class="tip"><i class="layui-icon layui-icon-tips"></i>{{ $jobConfig ? $jobConfig->comments : '任务域名 URL' }}</p>
                        </div>
                    </div>
                    <div class="layui-card config-item-card">
                        <div class="layui-card-header"><i class="layui-icon layui-icon-rmb"></i>TON 支付接口 URL</div>
                        <div class="layui-card-body">
                            <input type="text" class="z-input layui-input" name="ton_url[url]" autocomplete="off" value="{{ $tonUrl }}" placeholder="https://api.example.com/api/premium">
                            <p class="tip"><i class="layui-icon layui-icon-tips"></i>{{ $tonConfig ? $tonConfig->comments : 'TON 支付接口，不需要开通 TG 会员时可留空' }}</p>
                        </div>
                    </div>
                    <div class="layui-card config-item-card">
                        <div class="layui-card-header"><i class="layui-icon layui-icon-link"></i>API 连接 URL</div>
                        <div class="layui-card-body">
                            <input type="text" class="z-input layui-input" name="api_web_url[url]" autocomplete="off" value="{{ $apiWebUrl }}" placeholder="https://api.example.com">
                            <p class="tip"><i class="layui-icon layui-icon-tips"></i>API 授权系统连接地址</p>
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
            <div class="config-section">
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
@endsection

@section('scripts')
    <script>
        layui.use(['table', 'layer', 'element'], function () {
            var table = layui.table,
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

            // 缩略图预览
            $(".thumb").change(function(){
                var file=this.files[0];
                var url=window.URL.createObjectURL(file);
                $(this).parent().css({
                    'background-image':'url('+url+')',
                    'border':'none'
                });
            });
        });
    </script>
@endsection
