<?php

namespace App;

class PsqlConnection
{
    /**
     * Connect to the database and return an instance of \PDO object
     * @return \PDO|null
     */
    public function connect(): \PDO
    {
        if (getenv('DATABASE_URL') === false) {
            return null;
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
