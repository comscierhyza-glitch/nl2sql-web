<?php

function validateColumns(array &$context): void
{
    $mode = $context["validation"]["mode"];

    /*
    |--------------------------------------------------------------------------
    | Get Resolved Table
    |--------------------------------------------------------------------------
    */

    $table =
        $context["semantic"]["tables"]["resolved"]
        ??
        $context["parsed"]["table"]
        ??
        "";

    /*
    |--------------------------------------------------------------------------
    | Get Semantic Columns
    |--------------------------------------------------------------------------
    */

    $columns = [];

    if (!empty($context["semantic"]["columns"])) {

        foreach ($context["semantic"]["columns"] as $item) {

            if (($item["resolved"] ?? "") == "*") {
                continue;
            }

            $columns[] = $item;
        }

    } elseif (!empty($context["parsed"]["columns"])) {

        foreach ($context["parsed"]["columns"] as $column) {

            if ($column == "*") {
                continue;
            }

            $columns[] = [

                "original" => $column,

                "resolved" => $column,

                "matched_by" => "parser"

            ];

        }

    }

    /*
    |--------------------------------------------------------------------------
    | No Columns
    |--------------------------------------------------------------------------
    */

    if (empty($columns)) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Parser Mode
    |--------------------------------------------------------------------------
    */

    if ($mode == "PARSER") {

        $context["validation"]["warnings"][] =
            "Database schema not imported. Column existence cannot be verified.";

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Schema Mode
    |--------------------------------------------------------------------------
    */

    $schema = $_SESSION["schema"] ?? [];

    if (

        empty($table) ||

        !isset($schema[$table])

    ) {

        return;
    }

    $schemaColumns = array_keys($schema[$table]["columns"]);

    foreach ($columns as $column) {

        $resolved = $column["resolved"];

        /*
        |--------------------------------------------------------------------------
        | Exact Match
        |--------------------------------------------------------------------------
        */

        if (in_array($resolved, $schemaColumns)) {

            continue;

        }

        /*
        |--------------------------------------------------------------------------
        | Similar Match
        |--------------------------------------------------------------------------
        */

        $bestColumn = null;

        $bestScore = 0;

        foreach ($schemaColumns as $schemaColumn) {

            similar_text(

                strtolower($resolved),

                strtolower($schemaColumn),

                $percent

            );

            if ($percent > $bestScore) {

                $bestScore = $percent;

                $bestColumn = $schemaColumn;

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Auto Correct
        |--------------------------------------------------------------------------
        */

        if (

            $bestScore >= 80 &&

            $bestColumn != null

        ) {

            $context["validation"]["autocorrections"][] =

                "{$resolved} → {$bestColumn}";

            continue;

        }

        /*
        |--------------------------------------------------------------------------
        | Unknown Column
        |--------------------------------------------------------------------------
        */

        $context["validation"]["warnings"][] =

            "Unknown column '{$resolved}'.";
    }

}