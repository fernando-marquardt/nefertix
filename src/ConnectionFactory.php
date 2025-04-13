<?php
declare(strict_types=1);

namespace Nefertix;

use PDO;

class ConnectionFactory
{
    public static function fromDSN(string $dsn, ?string $username, ?string $password): Database
    {
        $pdo = new PDO($dsn, $username, $password);

        return new Database($pdo);
    }

    public static function fromConfig(
        string  $driver,
        ?string $host,
        ?int    $port,
        string  $database,
        ?string $username,
        ?string $password,
        ?string $charset = null,
    ): Database
    {
        if ($driver === 'sqlite') {
            return self::fromDSN('sqlite:' . $database, $username, $password);
        }

        $dsn = $driver . ':host=' . $host;

        if ($port) {
            $dsn .= ';port=' . $port;
        }

        $dsn .= ';dbname=' . $database;

        if ($driver === 'mysql' && !$charset) {
            $dsn .= ';charset=utf8mb4';
        }

        return self::fromDSN($dsn, $username, $password);
    }
}
