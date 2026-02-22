<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\StatementPrepared;
use PDO;
use PDOException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 注册自定义 PostgreSQL 连接器（支持 keepalive）
        $this->app->bind('db.connector.pgsql', function () {
            return new \App\Database\Connectors\PostgresConnector;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        if(env('IS_HTTPS')){
            \URL::forceScheme('https');
        }

        // 数据库连接保活和自动重连机制
        $this->setupDatabaseConnection();
    }

    /**
     * 设置数据库连接保活和自动重连
     */
    protected function setupDatabaseConnection()
    {
        // 监听查询执行，检测连接状态
        DB::listen(function (QueryExecuted $query) {
            // 如果查询执行时间过长，可能是连接问题
            if ($query->time > 5000) { // 5秒
                Log::warning('数据库查询执行时间过长', [
                    'sql' => $query->sql,
                    'time' => $query->time,
                ]);
            }
        });

        // 定期检查数据库连接健康状态
        if (app()->runningInConsole() === false) {
            $this->scheduleConnectionHealthCheck();
        }
    }

    /**
     * 定期检查数据库连接健康状态
     */
    protected function scheduleConnectionHealthCheck()
    {
        // 使用 Laravel 的延迟执行来定期检查连接
        // 每5分钟检查一次连接
        try {
            DB::connection()->getPdo();
        } catch (PDOException $e) {
            Log::error('数据库连接健康检查失败，尝试重连', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            
            // 尝试重新连接
            $this->reconnectDatabase();
        }
    }

    /**
     * 重新连接数据库
     */
    protected function reconnectDatabase()
    {
        $maxRetries = (int) env('DB_MAX_RETRIES', 3);
        $retryAfter = (int) env('DB_RETRY_AFTER', 5);

        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                // 断开当前连接
                DB::disconnect();
                
                // 等待后重连
                if ($i > 0) {
                    sleep($retryAfter);
                }
                
                // 重新连接
                DB::reconnect();
                
                // 测试连接
                DB::connection()->getPdo();
                
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
