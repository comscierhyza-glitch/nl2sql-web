<?php

function parseSQLSchema($sql)
{
    $schema = [];

    /*
|--------------------------------------------------------------------------
| Match All CREATE TABLE Statements
|--------------------------------------------------------------------------
*/

    preg_match_all(
        '/CREATE\s+TABLE\s+`?(\w+)`?\s*\((.*?)\)\s*ENGINE=/is',
        $sql,
        $matches,
        PREG_SET_ORDER
    );

    foreach ($matches as $tableMatch) {

        $tableName = $tableMatch[1];
        $tableBody = $tableMatch[2];

        $schema[$tableName] = [

            "columns" => [],

            "primary_keys" => [],

            "foreign_keys" => []

        ];

        $lines = preg_split('/\r\n|\r|\n/', $tableBody);

        foreach ($lines as $line) {

            $line = trim($line);
            $line = rtrim($line, ",");

            if ($line == "") {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | PRIMARY KEY
            |--------------------------------------------------------------------------
            */

            if (preg_match('/PRIMARY\s+KEY\s*\((.*?)\)/i', $line, $pkMatch)) {

                $primaryKeys = explode(",", $pkMatch[1]);

                foreach ($primaryKeys as $primaryKey) {

                    $primaryKey = trim($primaryKey);
                    $primaryKey = trim($primaryKey, "` ");

                    $schema[$tableName]["primary_keys"][] = $primaryKey;
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | FOREIGN KEY
            |--------------------------------------------------------------------------
            */

            if (
                preg_match(
                    '/FOREIGN\s+KEY\s*\(`?(\w+)`?\)\s+REFERENCES\s+`?(\w+)`?\s*\(`?(\w+)`?\)/i',
                    $line,
                    $fkMatch
                )
            ) {

                $schema[$tableName]["foreign_keys"][] = [

                    "column" => $fkMatch[1],

                    "references" => $fkMatch[2],

                    "reference_column" => $fkMatch[3]

                ];

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Ignore Other Constraints
            |--------------------------------------------------------------------------
            */

            if (
                stripos($line, "UNIQUE KEY") === 0 ||
                stripos($line, "KEY ") === 0 ||
                stripos($line, "CONSTRAINT") === 0
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Extract Column Name + Data Type
            |--------------------------------------------------------------------------
            */

            if (
                preg_match(
                    '/^`?(\w+)`?\s+([a-zA-Z]+(?:\([^)]+\))?)/',
                    $line,
                    $columnMatch
                )
            ) {

                $columnName = $columnMatch[1];
                $columnType = strtoupper($columnMatch[2]);

                $schema[$tableName]["columns"][$columnName] = [

                    "type" => $columnType

                ];
            }
        }
    }

    return $schema;
}
