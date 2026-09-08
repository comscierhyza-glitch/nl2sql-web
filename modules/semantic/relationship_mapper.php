<?php

function mapRelationships(array $parsed, array $schema): array
{
    /*
    |--------------------------------------------------------------------------
    | No detected table
    |--------------------------------------------------------------------------
    */

    if (empty($parsed["table"])) {
        return $parsed;
    }

    $table = $parsed["table"];

    if (
        empty($table) &&
        !empty($parsed["semantic"]["table"])
    ) {
        $table = $parsed["semantic"]["table"];
    }

    /*
    |--------------------------------------------------------------------------
    | Table not found
    |--------------------------------------------------------------------------
    */

    if (!isset($schema[$table])) {
        return $parsed;
    }

    /*
    |--------------------------------------------------------------------------
    | No foreign keys
    |--------------------------------------------------------------------------
    */

    if (empty($schema[$table]["foreign_keys"])) {
        return $parsed;
    }

    $parsed["relationships"] = [];

    foreach ($schema[$table]["foreign_keys"] as $foreignKey) {

        $parsed["relationships"][] = [

            "type" => "INNER",

            "table" => $foreignKey["references"],

            "local_column" => $foreignKey["column"],

            "foreign_column" => $foreignKey["reference_column"]

        ];
    }

    /*
|--------------------------------------------------------------------------
| Detect Column Owners
|--------------------------------------------------------------------------
*/

    $parsed["column_tables"] = [];

    if (!empty($parsed["columns"])) {

        foreach ($parsed["columns"] as $column) {

            foreach ($schema as $schemaTable => $tableInfo) {

                if (
                    isset($tableInfo["columns"]) &&
                    array_key_exists($column, $tableInfo["columns"])
                ) {

                    $parsed["column_tables"][$column] = $schemaTable;

                    break;
                }
            }
        }
    }

    /*
|--------------------------------------------------------------------------
| Detect Cross-Table Relationships
|--------------------------------------------------------------------------
*/

    if (!empty($parsed["column_tables"])) {

        $ownerTables = array_unique(
            array_values($parsed["column_tables"])
        );

        /*
    |--------------------------------------------------------------------------
    | More than one table requested
    |--------------------------------------------------------------------------
    */

        if (count($ownerTables) > 1) {

            $baseTable = array_shift($ownerTables);

            foreach ($ownerTables as $relatedTable) {

                /*
            |--------------------------------------------------------------------------
            | Find FK from Base -> Related
            |--------------------------------------------------------------------------
            */

                if (!empty($schema[$baseTable]["foreign_keys"])) {

                    foreach ($schema[$baseTable]["foreign_keys"] as $fk) {

                        if ($fk["references"] == $relatedTable) {

                            $parsed["relationships"][] = [

                                "type" => "INNER",

                                "table" => $relatedTable,

                                "local_column" => $fk["column"],

                                "foreign_column" => $fk["reference_column"]

                            ];
                        }
                    }
                }
            }
        }
    }
    
    return $parsed;
}
