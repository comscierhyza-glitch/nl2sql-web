<?php

require_once __DIR__ . "/semantic_helpers.php";

function inferTable(array $parsed, array $schema): array
{
    /*
    |--------------------------------------------------------------------------
    | If parser already detected the table,
    | skip semantic inference.
    |--------------------------------------------------------------------------
    */

    if (!empty($parsed["table"])) {
        return $parsed;
    }

    /*
    |--------------------------------------------------------------------------
    | No detected columns
    |--------------------------------------------------------------------------
    */

    if (empty($parsed["columns"])) {
        return $parsed;
    }

    $scores = [];

    foreach ($schema as $table => $tableData) {

        $scores[$table] = 0;

        /*
        |--------------------------------------------------------------------------
        | Get actual column names from schema
        |--------------------------------------------------------------------------
        */

        $columns = array_keys($tableData["columns"]);

        foreach ($parsed["columns"] as $userColumn) {

            /*
            |--------------------------------------------------------------------------
            | Table Name Match
            |--------------------------------------------------------------------------
            */

            if (
                normalizeTableName($table) ==
                normalizeTableName(plural($userColumn))
            ) {
                $scores[$table] += 6;
            }

            if (
                normalizeTableName(singular($table)) ==
                normalizeTableName($userColumn)
            ) {
                $scores[$table] += 6;
            }

            /*
            |--------------------------------------------------------------------------
            | Column Matching
            |--------------------------------------------------------------------------
            */

            foreach ($columns as $schemaColumn) {

                // Exact Match

                if (
                    strtolower($userColumn) ==
                    strtolower($schemaColumn)
                ) {

                    $scores[$table] += 5;
                    continue;
                }

                // Normalized Match

                if (
                    normalizeColumnName($userColumn) ==
                    normalizeColumnName($schemaColumn)
                ) {

                    $scores[$table] += 4;
                    continue;
                }

                // Similar Match

                similar_text(
                    normalizeColumnName($userColumn),
                    normalizeColumnName($schemaColumn),
                    $percent
                );

                if ($percent >= 85) {
                    $scores[$table] += 2;
                }
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Select Best Table
    |--------------------------------------------------------------------------
    */

    arsort($scores);

    $bestTable = array_key_first($scores);

    $highestScore = reset($scores);

    if ($highestScore > 0) {

        $parsed["semantic"]["table"] = $bestTable;

        $parsed["semantic"]["table_score"] = $highestScore;

        $parsed["semantic"]["table_candidates"] = $scores;
    }

    return $parsed;
}
