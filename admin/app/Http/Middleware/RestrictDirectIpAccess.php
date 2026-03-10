<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class RestrictDirectIpAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            // 1. 如果环境变量 ALLOW_DIRECT_IP_ACCESS 为 true，则直接放行
            $allowDirectIp = env('ALLOW_DIRECT_IP_ACCESS', true);
            if (filter_var($allowDirectIp, FILTER_VALIDATE_BOOLEAN)) {
                return $next($request);
            }

            // 2. 获取请求的 Host 头
            // 注意：$request->getHost() 获取的是 Host 头的内容（不含端口）
            $host = $request->getHost();
            
            // 3. 获取配置的 APP_URL 中的 Host
            $appUrl = config('app.url');
            $appHost = parse_url($appUrl, PHP_URL_HOST);

            // 4. 允许本地回环地址访问
            if ($host === '127.0.0.1' || $host === 'localhost') {
                return $next($request);
            }

            // 5. 核心逻辑：如果请求 Host 不等于 APP_URL 的 Host，则视为非法访问
            if ($appHost && $host !== $appHost) {
                // Log::warning("Blocked direct IP access from: " . $request->ip() . " Host: " . $host);
                return response('Forbidden: Direct IP access is not allowed.', 403);
            }
        } catch (\Throwable $e) {
            // 如果出现异常，记录日志并放行，防止服务崩溃
            // Log::error("RestrictDirectIpAccess Middleware Error: " . $e->getMessage());
            return $next($request);
        }

        return $next($request);
    }
}
