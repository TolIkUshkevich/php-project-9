<?php

namespace App;

class PsqlConnection
{
    /**
     * Connection
     * @var type
     */
    private static $conn;
    /**
     * Connect to the database and return an instance of \PDO object
     * @return \PDO
     * @throws \Exception
     */
    public function connect(): \PDO
    {
        $databaseUrl = parse_url(getenv('DATABASE_URL'));
        if ($databaseUrl === false) {
            throw new \Exception("Error reading database configuration file");
        }
        $conStr = sprintf(
            "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
            $databaseUrl['host'],
            $databaseUrl['port'],
            ltrim($databaseUrl['path'], '/'),
            $databaseUrl['user'],
            $databaseUrl['pass']
        );
        $pdo = new \PDO($conStr);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
