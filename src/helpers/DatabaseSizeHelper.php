<?php

namespace szenario\craftspacecontrol\helpers;


use Craft;
use craft\helpers\App;
use craft\db\Query;

class DatabaseSizeHelper
{
    public static function getDBSize()
    {
        $dbDriver = App::env('CRAFT_DB_DRIVER');
        try {
            if ($dbDriver === "mysql") {
                // MYSQL Query
                $dbName = App::env('CRAFT_DB_DATABASE');
                $query = (new Query())
                    ->select([
                        'ROUND(SUM(data_length + index_length), 0) AS "dbsize"'
                    ])
                    ->from('information_schema.tables')
                    ->where(['table_schema' => $dbName]);
                $dbSizeVal = $query->one();
                $dbSize = $dbSizeVal['dbsize'] ?? 0;

            } elseif ($dbDriver === "pgsql") {
                // PostgreSQL Query
                $dbName = strtolower(App::env('CRAFT_DB_DATABASE'));
                $query = (new Query())
                    ->select([
                        "pg_database_size('{$dbName}') as dbsize"
                    ]);

                $dbSizeVal = $query->one();
                $dbSize = $dbSizeVal['dbsize'] ?? 0;
            } else {
                $dbSize = 0;
            }
        } catch (\Exception $e) {
            $dbSize = 0;
        }

        return $dbSize;
    }
}