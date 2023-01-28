<?php

namespace Database\Connection;

class Connection
{
    /**
     * @var \Database\Connection\Connection|null
     */
    private static ?Connection $connection = null;

    /**
     * @param array $env
     *
     * @return \PDO
     * @throws \Exception
     */
    public function connect(array $env): \PDO
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

    /**
     * @return static
     */
    public static function get(): self
    {
        if (self::$connection === null) {
            self::$connection = new self();
        }

        return self::$connection;
    }

    protected function __construct()
    {
    }
}
