<?php

namespace App\Services;

class MysqlToSqliteConverter
{
    /**
     * Convert a MySQL CREATE TABLE statement to SQLite DDL.
     */
    public function createTableToSqlite(string $mysqlCreate, string $table): string
    {
        if (!preg_match('/\((.*)\)\s*ENGINE=/is', $mysqlCreate, $m)) {
            return 'CREATE TABLE "'.$table.'" ("id" INTEGER PRIMARY KEY AUTOINCREMENT)';
        }

        $body = $m[1];
        $lines = preg_split('/\r\n|\n|\r/', $body) ?: [];
        $colDefs = [];
        $primary = null;

        foreach ($lines as $line) {
            $line = trim($line);
            $line = rtrim($line, ',');
            if ($line === '' || stripos($line, 'KEY ') === 0 || stripos($line, 'UNIQUE KEY') === 0
                || stripos($line, 'CONSTRAINT') === 0 || stripos($line, 'FULLTEXT') === 0
                || stripos($line, 'SPATIAL') === 0) {
                if (preg_match('/^PRIMARY KEY\s*\((.+)\)/i', $line, $pk)) {
                    $primary = trim(str_replace('`', '', $pk[1]));
                }
                continue;
            }

            if (!preg_match('/^`([^`]+)`\s+(.+)$/i', $line, $cm)) {
                continue;
            }

            $name = $cm[1];
            $rest = $this->stripColumnComment($cm[2]);
            $type = $this->mapColumnType($rest);

            $notNull = stripos($rest, 'NOT NULL') !== false ? ' NOT NULL' : '';
            $auto = stripos($rest, 'AUTO_INCREMENT') !== false;
            $defaultSql = '';
            if (preg_match('/\bDEFAULT\s+((?:\'(?:\\\\\'|[^\'])*\'|\"(?:\\\\\"|[^\"])*\"|NULL|CURRENT_TIMESTAMP(?:\(\))?|current_timestamp\(\)|-?\d+(?:\.\d+)?))/i', $rest, $dm)) {
                $default = $dm[1];
                if (strcasecmp($default, 'current_timestamp()') === 0) {
                    $default = 'CURRENT_TIMESTAMP';
                }
                $defaultSql = ' DEFAULT '.$default;
            }

            $def = "\"{$name}\" {$type}{$notNull}{$defaultSql}";
            if ($auto) {
                $def = "\"{$name}\" INTEGER PRIMARY KEY AUTOINCREMENT";
                $primary = $name;
            }
            $colDefs[] = $def;
        }

        if ($primary === null && $this->hasIdColumn($lines)) {
            $primary = 'id';
        }

        if ($primary && !preg_match('/PRIMARY KEY/i', implode(' ', $colDefs))) {
            if ($primary === 'id' && $this->hasIntegerIdColumn($lines)) {
                $colDefs = $this->promoteIdToPrimaryKey($colDefs);
            } else {
                $colDefs[] = 'PRIMARY KEY ("'.$primary.'")';
            }
        }

        return 'CREATE TABLE "'.$table.'" ('.implode(', ', $colDefs).')';
    }

    /**
     * Convert MySQL INSERT statement to SQLite-compatible SQL.
     */
    public function insertToSqlite(string $mysqlInsert): string
    {
        return preg_replace('/`([^`]+)`/', '"$1"', $mysqlInsert);
    }

    protected function stripColumnComment(string $rest): string
    {
        return preg_replace('/\s+COMMENT\s+\'(?:\\\\\'|[^\'])*\'/i', '', $rest) ?? $rest;
    }

    protected function mapColumnType(string $rest): string
    {
        if (preg_match('/\benum\s*\(/i', $rest)) {
            return 'TEXT';
        }
        if (preg_match('/\b(tinyint|smallint|mediumint|int|bigint|integer)\b/i', $rest)) {
            return 'INTEGER';
        }
        if (preg_match('/\b(decimal|numeric|float|double|real)\b/i', $rest)) {
            return 'REAL';
        }
        if (preg_match('/\b(blob|binary|varbinary)\b/i', $rest)) {
            return 'BLOB';
        }

        return 'TEXT';
    }

    /** @param list<string> $lines */
    protected function hasIdColumn(array $lines): bool
    {
        foreach ($lines as $line) {
            $line = trim(rtrim($line, ','));
            if (preg_match('/^`id`\s+/i', $line)) {
                return true;
            }
        }

        return false;
    }

    /** @param list<string> $lines */
    protected function hasIntegerIdColumn(array $lines): bool
    {
        foreach ($lines as $line) {
            $line = trim(rtrim($line, ','));
            if (preg_match('/^`id`\s+(tinyint|smallint|mediumint|int|bigint|integer)\b/i', $line)) {
                return true;
            }
        }

        return false;
    }

    /** @param list<string> $colDefs */
    protected function promoteIdToPrimaryKey(array $colDefs): array
    {
        foreach ($colDefs as $i => $def) {
            if (preg_match('/^"id"\s+(\w+)(.*)$/i', $def, $m)) {
                $colDefs[$i] = '"id" INTEGER PRIMARY KEY'.$m[2];

                return $colDefs;
            }
        }

        return $colDefs;
    }
}
