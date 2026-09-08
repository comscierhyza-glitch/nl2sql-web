<?php

function validateSyntax(array &$context): void
{
    $tokens = array_map("strtolower", $context["tokens"] ?? []);
    $parsed = $context["parsed"] ?? [];
    $command = strtoupper($parsed["command"] ?? "");

    switch ($command) {

        /*
        |--------------------------------------------------------------------------
        | SELECT
        |--------------------------------------------------------------------------
        */
        case "SELECT":

            // Table check
            if (
                empty($parsed["table"]) &&
                empty($context["semantic"]["tables"]["resolved"])
            ) {
                $context["validation"]["warnings"][] =
                    "Table not explicitly specified. Semantic inference will be used.";
            }

            // WHERE check
            if (in_array("where", $tokens)) {
                if (
                    empty($parsed["where"]) ||
                    empty($parsed["where"]["conditions"])
                ) {
                    $context["validation"]["errors"][] =
                        "Unable to understand the WHERE condition.";
                }
            }

            // LIMIT check
            if (in_array("limit", $tokens)) {
                $limit = $parsed["limit"] ?? null;
                if ($limit === null) {
                    $context["validation"]["errors"][] =
                        "LIMIT value is missing.";
                } elseif (!is_numeric($limit)) {
                    $context["validation"]["errors"][] =
                        "LIMIT must be numeric.";
                }
            }

            // ORDER BY check
            if (in_array("order", $tokens) || in_array("sort", $tokens)) {
                if (empty($parsed["orderby"])) {
                    $context["validation"]["warnings"][] =
                        "Unable to determine ORDER BY column.";
                }
            }

            // GROUP BY check
            if (in_array("group", $tokens)) {
                if (empty($parsed["groupby"])) {
                    $context["validation"]["warnings"][] =
                        "GROUP BY column could not be determined.";
                }
            }

            // HAVING check
            if (!empty($parsed["having"])) {
                if (empty($parsed["groupby"])) {
                    $context["validation"]["warnings"][] =
                        "HAVING detected without GROUP BY. The system will attempt to resolve it.";
                }
            }

            break;

        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */
        case "INSERT":
            if (empty($parsed["insert"]) && empty($parsed["table"])) {
                $context["validation"]["warnings"][] =
                    "Ensure target table and values are properly specified for INSERT.";
            }
            break;

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */
        case "UPDATE":
            if (empty($parsed["where"])) {
                $context["validation"]["warnings"][] =
                    "UPDATE without WHERE clause will modify all rows in the table.";
            }
            break;

        /*
        |--------------------------------------------------------------------------
        | DELETE
        |--------------------------------------------------------------------------
        */
        case "DELETE":
            if (empty($parsed["where"])) {
                $context["validation"]["warnings"][] =
                    "DELETE without WHERE clause will remove all records from the table.";
            }
            break;

        /*
        |--------------------------------------------------------------------------
        | CREATE & ALTER (DDL Schema Modifications)
        |--------------------------------------------------------------------------
        */
        case "CREATE":
        case "ALTER":
            $context["validation"]["warnings"][] =
                "Schema modification query detected ({$command}). Verify column data types before execution.";
            break;

        /*
        |--------------------------------------------------------------------------
        | DROP & TRUNCATE (DDL Destructive Operations)
        |--------------------------------------------------------------------------
        */
        case "DROP":
        case "TRUNCATE":
            $context["validation"]["warnings"][] =
                "Destructive DDL query detected ({$command}). Executing this will permanently drop/reset database structure or data.";
            break;
    }
}
