<?php

declare(strict_types=1);

namespace App\Database\Connectors;

use Hyperf\Database\Connectors\Connector;
use Hyperf\Database\Connectors\ConnectorInterface;
use PDO;

/**
 * PostgreSQL 连接器，供 Hyperf Job 连接 PostgreSQL 数据库
 */
class PostgresConnector extends Connector implements ConnectorInterface
{
    public function connect(array $config): PDO
    {
        $dsn = $this->getDsn($config);
        $options = $this->getOptions($config);
        $connection = $this->createConnection($dsn, $config, $options);

        if (isset($config['charset'])) {
            $connection->prepare("set names '{$config['charset']}'")->execute();
        }

        if (isset($config['timezone'])) {
            $connection->prepare("set time zone '{$config['timezone']}'")->execute();
        }

        return $connection;
    }

    protected function getDsn(array $config): string
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 5432;
        $database = $config['database'] ?? 'db';

        return "pgsql:host={$host};port={$port};dbname={$database}";
    }
}
