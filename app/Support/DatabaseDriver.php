<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DatabaseDriver
{
    public static function name(?string $connection = null): string
    {
        return DB::connection($connection)->getDriverName();
    }

    public static function isSqlite(?string $connection = null): bool
    {
        return self::name($connection) === 'sqlite';
    }

    public static function isMysql(?string $connection = null): bool
    {
        return in_array(self::name($connection), ['mysql', 'mariadb'], true);
    }

    /**
     * SQL fragment to read a JSON object string field (LIKE-compatible).
     */
    public static function jsonStringExtract(string $column, string $path): string
    {
        if (self::isSqlite()) {
            return "json_extract({$column}, '$.{$path}')";
        }

        return "JSON_UNQUOTE(JSON_EXTRACT({$column}, '$.{$path}'))";
    }

    /**
     * SQL fragment comparing a JSON boolean-ish field to true.
     */
    public static function jsonTruthy(string $column, string $path): string
    {
        if (self::isSqlite()) {
            return "json_extract({$column}, '$.{$path}') = 1";
        }

        return "JSON_EXTRACT({$column}, '$.{$path}') = true";
    }
}
