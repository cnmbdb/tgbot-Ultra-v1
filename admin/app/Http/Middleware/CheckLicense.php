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
        // 暂时禁用授权检查，让所有页面都能访问，以便调试
        // TODO: 之后需要恢复授权检查逻辑
        return $next($request);

        try {
            // 检查是否已激活授权
            $licensed = $this->isLicensed();
            \Log::info('CheckLicense: isLicensed = ' . ($licensed ? 'true' : 'false'));

            if (!$licensed) {
                // 获取路由名称（iframe 内请求时 route 可能尚未解析，用 URI 兜底）
                $routeName = $request->route() ? $request->route()->getName() : '';
                $uri = $request->getRequestUri();
                $path = $request->path();

                \Log::info('CheckLicense: 未授权，检查路由', [
                    'routeName' => $routeName,
                    'uri' => $uri,
                    'path' => $path
                ]);

                // 检查是否是系统设置相关路由（允许所有设置页面，含 iframe 内打开）
                $isConfigRoute = strpos($routeName, 'admin.setting') !== false
                    || strpos($uri, '/setting') !== false
                    || strpos($path, 'setting') !== false
                    || $routeName === 'admin.home';

                \Log::info('CheckLicense: 是否允许访问', ['isConfigRoute' => $isConfigRoute]);

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
