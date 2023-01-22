<?php

namespace Database\Connection;

class Connection
{
    private static $connection;

    public function connect($env): \PDO
    {
        if (empty($env['DATABASE_URL'])) {
            throw new \Exception("Database params is empty.");
        }

        $params = parse_url($env['DATABASE_URL']);
        $conStr = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s',
            $params['host'],
            $params['port'],
            ltrim($params['path'], '/'),
            $params['user'],
            $params['pass']
        );

        $pdo = new \PDO($conStr);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }

    public static function get(): self
    {
        if (null === static::$connection) {
            static::$connection = new static();
        }

        return static::$connection;
    }

    protected function __construct()
    {
    }
}
