<?php

require_once __DIR__ . "/../schema_mapper.php";
require_once __DIR__ . "/../column_mapper.php";

function handleSemantic(array &$context): bool
{
    /*
|--------------------------------------------------------------------------
| Load Imported Schema into Context
|--------------------------------------------------------------------------
*/

    if (
        isset($_SESSION["schema"]) &&
        is_array($_SESSION["schema"])
    ) {

        $context["schema"]["tables"] = [];

        $context["schema"]["columns"] = [];

        $context["schema"]["primary_keys"] = [];

        $context["schema"]["foreign_keys"] = [];

        foreach ($_SESSION["schema"] as $table => $tableInfo) {

            $context["schema"]["tables"][] = $table;

            /*
|--------------------------------------------------------------------------
| Columns
|--------------------------------------------------------------------------
*/

            if (
                isset($tableInfo["columns"]) &&
                is_array($tableInfo["columns"])
            ) {

                $context["schema"]["columns"][$table] =
                    array_keys($tableInfo["columns"]);
            }

            /*
|--------------------------------------------------------------------------
| Primary Keys
|--------------------------------------------------------------------------
*/

            $context["schema"]["primary_keys"][$table] =
                $tableInfo["primary_keys"] ?? [];

            /*
|--------------------------------------------------------------------------
| Foreign Keys
|--------------------------------------------------------------------------
*/

            $context["schema"]["foreign_keys"][$table] =
                $tableInfo["foreign_keys"] ?? [];
        }
    }
    /*
|--------------------------------------------------------------------------
| Resolve Table
|--------------------------------------------------------------------------
*/

    $originalTable = $context["parsed"]["table"] ?? "";

    $resolvedTable = mapSchemaWord($originalTable);

    $confidence = 0;
    $source = "";

    /*
|--------------------------------------------------------------------------
| Check if schema exists
|--------------------------------------------------------------------------
*/

    $hasSchema =

        isset($_SESSION["schema"]) &&

        is_array($_SESSION["schema"]) &&

        count($_SESSION["schema"]) > 0;

    /*
|--------------------------------------------------------------------------
| Resolution
|--------------------------------------------------------------------------
*/

    if (!$hasSchema) {

        $resolvedTable = $originalTable;

        if ($originalTable != "") {

            $confidence = 100;

            $source = "parser";
        }
    } else {

        if (
            strtolower($originalTable) === strtolower($resolvedTable)
        ) {

            $confidence = 100;

            $source = "schema";
        } elseif (!empty($resolvedTable)) {

            $confidence = 80;

            $source = "schema";
        }
    }

    /*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

    $context["semantic"]["tables"] = [

        "original"   => $originalTable,

        "resolved"   => $resolvedTable,

        "exists"     => !empty($resolvedTable),

        "confidence" => $confidence,

        "source"     => $source

    ];

    /*
    |--------------------------------------------------------------------------
    | Warning
    |--------------------------------------------------------------------------
    */


    if (

        $hasSchema &&

        $confidence == 0 &&

        $originalTable != ""

    ) {

        $context["semantic"]["warnings"][] =

            "Unable to resolve table '{$originalTable}' from imported schema.";
    }

    /*
|--------------------------------------------------------------------------
| Resolve Columns
|--------------------------------------------------------------------------
*/

    $originalColumns = $context["parsed"]["columns"] ?? [];

    $semanticColumns = [];

    foreach ($originalColumns as $column) {

        if ($column === "*") {

            $semanticColumns[] = [

                "original"   => "*",
                "resolved"   => "*",
                "confidence" => 100,
                "matched_by" => "wildcard"

            ];

            continue;
        }

        if (!$hasSchema) {

            $semanticColumns[] = [

                "original"   => $column,
                "resolved"   => $column,
                "confidence" => 100,
                "matched_by" => "parser"

            ];

            continue;
        }

        $result = mapColumnWord($column);

        if ($result !== null) {

            $semanticColumns[] = [

                "original"   => $column,
                "resolved"   => $result["column"],
                "confidence" => $result["confidence"],
                "matched_by" => $result["matched_by"]

            ];
        } else {

            $semanticColumns[] = [

                "original"   => $column,
                "resolved"   => $column,
                "confidence" => 0,
                "matched_by" => "unresolved"

            ];

            $context["semantic"]["warnings"][] =
                "Unable to resolve column '{$column}'.";
        }
    }

    $context["semantic"]["columns"] = $semanticColumns;

    /*
|--------------------------------------------------------------------------
| Infer Table From Resolved Columns
|--------------------------------------------------------------------------
|
| If parser cannot determine the table but a schema is loaded,
| infer the most likely table by checking which table contains
| the resolved columns.
|
*/

    if (

        $hasSchema &&

        empty($context["parsed"]["table"]) &&

        !empty($semanticColumns)

    ) {

        $tableScores = [];

        /*
|--------------------------------------------------------------------------
| Table Name Bonus
|--------------------------------------------------------------------------
|
| If the parser already detected a table name,
| give that table a higher initial score.
|
*/

        $originalTable = strtolower($context["parsed"]["table"] ?? "");

        if (!empty($originalTable)) {

            foreach ($_SESSION["schema"] as $table => $tableInfo) {

                if (strtolower($table) === $originalTable) {

                    if (!isset($tableScores[$table])) {
                        $tableScores[$table] = 0;
                    }

                    $tableScores[$table] += 6;
                }
            }
        }

        foreach ($semanticColumns as $column) {

            if (
                $column["resolved"] == "*" ||
                $column["matched_by"] == "unresolved"
            ) {
                continue;
            }

            foreach ($_SESSION["schema"] as $table => $tableInfo) {

                if (!isset($tableScores[$table])) {
                    $tableScores[$table] = 0;
                }

                /*
        |--------------------------------------------------------------------------
        | Exact Column Match
        |--------------------------------------------------------------------------
        */

                if (isset($tableInfo["columns"][$column["resolved"]])) {

                    $tableScores[$table] += 5;
                }

                /*
        |--------------------------------------------------------------------------
        | Similar Column Match
        |--------------------------------------------------------------------------
        */

                foreach ($tableInfo["columns"] as $schemaColumn => $meta) {

                    similar_text(
                        strtolower($column["resolved"]),
                        strtolower($schemaColumn),
                        $percent
                    );

                    if ($percent >= 85) {

                        $tableScores[$table] += 2;

                        break;
                    }
                }
            }
        }

        if (!empty($tableScores)) {

            arsort($tableScores);

            $bestTable = array_key_first($tableScores);

            $bestScore = current($tableScores);

            /*
|--------------------------------------------------------------------------
| Detect Ambiguous Tables
|--------------------------------------------------------------------------
*/

            $topTables = [];

            foreach ($tableScores as $table => $score) {

                if ($score == $bestScore) {

                    $topTables[] = $table;
                }
            }

            $isAmbiguous = count($topTables) > 1;

            if ($isAmbiguous) {

                $context["semantic"]["warnings"][] =
                    "Multiple possible tables detected: " .
                    implode(", ", $topTables);
            }

            $confidence = round(

                ($bestScore / count($semanticColumns)) * 100

            );

            $confidenceLevel = "LOW";

            if ($confidence >= 90) {

                $confidenceLevel = "HIGH";
            } elseif ($confidence >= 70) {

                $confidenceLevel = "MEDIUM";
            }

            $context["semantic"]["tables"] = [

                "original"           => null,

                "resolved"           => $bestTable,

                "exists"             => true,

                "confidence"         => $confidence,

                "confidence_level"   => $confidenceLevel,

                "source"             => "inferred",

                "ambiguous"          => $isAmbiguous,

                "candidates"         => $topTables

            ];

            // Apply immediately to parsed context
            $context["parsed"]["table"] = $bestTable;
        }
    }

    /*
|--------------------------------------------------------------------------
| Automatic JOIN Inference
|--------------------------------------------------------------------------
*/

    if (
        $hasSchema &&
        !empty($context["parsed"]["table"])
    ) {

        $baseTable = $context["parsed"]["table"];

        $joins = [];

        foreach ($context["semantic"]["columns"] as $column) {

            $resolvedColumn = $column["resolved"] ?? "";

            if ($resolvedColumn == "*" || empty($resolvedColumn)) {
                continue;
            }

            foreach ($_SESSION["schema"] as $table => $tableInfo) {

                // Skip base table
                if ($table == $baseTable) {
                    continue;
                }

                // Column not found in this table
                if (!isset($tableInfo["columns"][$resolvedColumn])) {
                    continue;
                }

                // Already joined
                if (isset($joins[$table])) {
                    continue;
                }

                // Check foreign keys
                if (
                    empty($_SESSION["schema"][$baseTable]["foreign_keys"])
                ) {
                    continue;
                }

                foreach ($_SESSION["schema"][$baseTable]["foreign_keys"] as $fk) {

                    if ($fk["references"] != $table) {
                        continue;
                    }

                    $joins[$table] = [

                        "type" => "INNER JOIN",

                        "table" => $table,

                        "on" =>
                        "{$baseTable}.{$fk["column"]} = {$table}.{$fk["reference_column"]}"

                    ];

                    break;
                }
            }
        }

        $context["parsed"]["joins"] = array_values($joins);
    }

    /*
|--------------------------------------------------------------------------
| Resolve WHERE Columns
|--------------------------------------------------------------------------
*/

    if (!empty($context["parsed"]["where"]["conditions"])) {

        foreach ($context["parsed"]["where"]["conditions"] as &$condition) {

            if (empty($condition["column"])) {
                continue;
            }

            $result = mapColumnWord($condition["column"]);

            if ($result !== null) {

                $condition["column"] = $result["column"];
            }
        }

        unset($condition);
    }

    /*
|--------------------------------------------------------------------------
| Resolve ORDER BY Column
|--------------------------------------------------------------------------
*/

    if (!empty($context["parsed"]["orderby"]["column"])) {

        $result = mapColumnWord(
            $context["parsed"]["orderby"]["column"]
        );

        if ($result !== null) {

            $context["parsed"]["orderby"]["column"] =
                $result["column"];
        }
    }

    /*
|--------------------------------------------------------------------------
| Resolve GROUP BY Column
|--------------------------------------------------------------------------
*/

    if (!empty($context["parsed"]["groupby"])) {

        $result = mapColumnWord(
            $context["parsed"]["groupby"]
        );

        if ($result !== null) {

            $context["parsed"]["groupby"] =
                $result["column"];
        }
    }

    /*
|--------------------------------------------------------------------------
| Resolve Aggregate Column
|--------------------------------------------------------------------------
*/

    if (

        !empty($context["parsed"]["aggregate"]["column"])

    ) {

        $result = mapColumnWord(

            $context["parsed"]["aggregate"]["column"]

        );

        if ($result !== null) {

            $context["parsed"]["aggregate"]["column"] =

                $result["column"];
        }
    }

    /*
|--------------------------------------------------------------------------
| Apply Semantic Resolution to Parsed Context
|--------------------------------------------------------------------------
|
| Replace parser output with resolved schema-aware values.
| This keeps the existing SQL Generator compatible without
| modifying all SQL generation functions.
|
*/

    if (!empty($context["semantic"]["tables"]["resolved"])) {

        $context["parsed"]["table"] =
            $context["semantic"]["tables"]["resolved"];
    }

    if (!empty($context["semantic"]["columns"])) {

        $resolvedColumns = [];

        foreach ($context["semantic"]["columns"] as $column) {

            /*
        |--------------------------------------------------------------
        | Ignore unresolved connector words
        |--------------------------------------------------------------
        */

            if (
                ($column["matched_by"] ?? "") === "unresolved"
            ) {
                continue;
            }

            $resolvedColumns[] = $column["resolved"];
        }

        if (!empty($resolvedColumns)) {

            $context["parsed"]["columns"] = $resolvedColumns;
        }
    }


    return true;
}
