<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDOException;

class DatabaseConnectionCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 优化：只在必要时检查连接（避免静态资源等请求的额外开销）
        // 跳过静态资源、健康检查等请求
        if ($this->shouldSkipCheck($request)) {
            return $next($request);
        }

        // 检查数据库连接状态（使用缓存避免频繁检查）
        $lastCheckKey = 'db_connection_last_check';
        $lastCheck = cache()->get($lastCheckKey, 0);
        $checkInterval = 30; // 30秒检查一次

        if (time() - $lastCheck > $checkInterval) {
            try {
                $connection = DB::connection();
                $pdo = $connection->getPdo();
                
                // 执行简单查询测试连接
                $connection->select('SELECT 1');
                
                // 更新检查时间
                cache()->put($lastCheckKey, time(), 60);
            } catch (PDOException $e) {
                Log::error('数据库连接检查失败，尝试重连', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'url' => $request->fullUrl(),
                ]);

                // 尝试重新连接
                if ($this->reconnectDatabase()) {
                    Log::info('数据库重连成功，继续处理请求');
                    cache()->put($lastCheckKey, time(), 60);
                } else {
                    Log::error('数据库重连失败，返回错误响应');
                    
                    // 如果是 API 请求，返回 JSON 错误
                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'error' => '数据库连接失败，请稍后重试',
                            'code' => 'DB_CONNECTION_ERROR',
                        ], 503);
                    }
                    
                    // 否则返回错误页面
                    abort(503, '数据库连接失败，请稍后重试');
                }
            }
        }

        return $next($request);
    }

    /**
     * 判断是否应该跳过连接检查
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldSkipCheck(Request $request)
    {
        $path = $request->path();
        
        // 跳过静态资源
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
            return true;
        }
        
        // 跳过健康检查
        if ($path === 'health' || $path === 'ping') {
            return true;
        }
        
        return false;
    }

    /**
     * 重新连接数据库
     *
     * @return bool
     */
    protected function reconnectDatabase()
    {
        $maxRetries = (int) config('database.connections.pgsql.max_retries', 3);
        $retryAfter = (int) config('database.connections.pgsql.retry_after', 5);

        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                // 断开当前连接
                DB::disconnect();
                
                // 等待后重连（第一次不等待）
                if ($i > 0) {
                    sleep(min($retryAfter, 5)); // 最多等待5秒
                }
                
                // 重新连接
                DB::reconnect();
                
                // 测试连接
                DB::connection()->getPdo();
                DB::connection()->select('SELECT 1');
                
                Log::info('数据库重连成功', [
                    'attempt' => $i + 1,
                ]);
                
                return true;
            } catch (PDOException $e) {
                Log::warning('数据库重连失败', [
                    'attempt' => $i + 1,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::error('数据库重连失败，已达到最大重试次数', [
            'max_retries' => $maxRetries,
        ]);

        return false;
    }
}
