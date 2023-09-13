<?php

namespace szenario\craftspacecontrol\helpers;


use Craft;
use craft\base\Model;
use craft\helpers\App;
use craft\db\Query;

class DatabaseSizeHelper
{
    public static function getDBSize()
    {
            $dbDriver = App::env('CRAFT_DB_DRIVER');
            if ($dbDriver === "mysql") {
                // MYSQL Query
                $query = (new Query())
                    ->select([
                        'ROUND(SUM(data_length + index_length), 2) AS "dbsize"'
                    ])
                    ->from('information_schema.tables');

                $dbSizeVal = $query->one();
                $dbSize = $dbSizeVal['dbsize'] ?? 0;

            }elseif ($dbDriver === "pgsql") {
                // PostgreSQL Query
                $dbName = strtolower(App::parseEnv('CRAFT_DB_DATABASE'));
                $query = (new Query())
                    ->select([
                        "SELECT pg_size_pretty( pg_database_size($dbName) )"
                    ]);
                $dbSize = $query->all();
            }
            else {
                $dbSize = 0;
            }

        return $dbSize;
    }
}