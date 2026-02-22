<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector as BasePostgresConnector;

class PostgresConnector extends BasePostgresConnector
{
    /**
     * Create a DSN string from a configuration.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        // 调用父类方法获取基础 DSN
        $dsn = parent::getDsn($config);

        // 添加 keepalive 参数（如果配置了）
        if (isset($config['keepalive']) && $config['keepalive']) {
            $dsn .= ';keepalives=1';
            
            // 添加 keepalive 相关参数
            if (isset($config['keepalive_idle'])) {
                $dsn .= ';keepalives_idle=' . (int) $config['keepalive_idle'];
            }
            
            if (isset($config['keepalive_interval'])) {
                $dsn .= ';keepalives_interval=' . (int) $config['keepalive_interval'];
            }
            
            if (isset($config['keepalive_count'])) {
                $dsn .= ';keepalives_count=' . (int) $config['keepalive_count'];
            }
        }

        // 添加连接超时参数
        if (isset($config['connect_timeout'])) {
            $dsn .= ';connect_timeout=' . (int) $config['connect_timeout'];
        }

        return $dsn;
    }
}
