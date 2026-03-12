<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\System\SysConfig;

class CheckLicense
{
    /**
     * Handle an incoming request.
     * 检查系统是否已激活授权，未激活则跳转到首页
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            // 检查是否已激活授权
            if (!$this->isLicensed()) {
                // 获取路由名称
                $routeName = $request->route() ? $request->route()->getName() : '';
                $uri = $request->getRequestUri();

                // 检查是否是配置相关路由（更宽松的匹配）
                $isConfigRoute = strpos($routeName, 'admin.setting.config') !== false
                    || strpos($uri, '/setting/config') !== false
                    || $routeName === 'admin.home';

                // 只有配置相关路由和首页可以访问
                if (!$isConfigRoute) {
                    // 如果是 AJAX 请求，返回错误提示
                    if ($request->ajax()) {
                        return response()->json([
                            'code' => 403,
                            'msg' => '未授权，请先激活授权'
                        ]);
                    }
                    return redirect()->route('admin.home')
                        ->with('show_license_warning', true);
                }
            }
        } catch (\Exception $e) {
            // 中间件出错时，允许访问
            \Log::error('License middleware error: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * 检查系统是否已激活授权
     * 只检查本地配置，不调用外部API
     */
    private function isLicensed()
    {
        $licenseConfig = SysConfig::where('config_key', 'license_activation')->first();

        if (!$licenseConfig) {
            return false;
        }

        $configVal = $licenseConfig->config_val;
        $localLicense = is_string($configVal)
            ? json_decode($configVal, true)
            : (array) $configVal;

        if (!$localLicense || empty($localLicense['auth_code'])) {
            return false;
        }

        return true;
    }
}
