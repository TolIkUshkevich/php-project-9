<?php

namespace App;

class PsqlConnection
{
    public function connect(): \PDO
    {
        if (getenv('DATABASE_URL') === false) {
            throw new \Exception();
        } else {
            $databaseUrl = parse_url(getenv('DATABASE_URL'));
            $conStr = sprintf(
                "pgsql:host=%s;dbname=%s;user=%s;password=%s",
                $databaseUrl['host'],
                ltrim($databaseUrl['path'], '/'),
                $databaseUrl['user'],
                $databaseUrl['pass']
            );
            $pdo = new \PDO($conStr);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }
    }
}
