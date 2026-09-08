<?php

function validateTable(array &$context): void
{
    $mode = $context["validation"]["mode"];

    /*
    |--------------------------------------------------------------------------
    | Get Table
    |--------------------------------------------------------------------------
    */

    $table = "";

    if (!empty($context["semantic"]["tables"]["resolved"])) {

        $table = $context["semantic"]["tables"]["resolved"];

    } elseif (!empty($context["parsed"]["table"])) {

        $table = $context["parsed"]["table"];

    }

    /*
    |--------------------------------------------------------------------------
    | No Table Found
    |--------------------------------------------------------------------------
    */

    if (empty($table)) {

        $context["validation"]["warnings"][] =
            "No table specified. The system will attempt to infer the table.";

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Parser Mode (No Imported Schema)
    |--------------------------------------------------------------------------
    */

    if ($mode == "PARSER") {

        $context["validation"]["warnings"][] =
            "Database schema not imported. Table existence cannot be verified.";

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Schema Mode
    |--------------------------------------------------------------------------
    */

    $schema = $_SESSION["schema"] ?? [];

    if (!isset($schema[$table])) {

        $context["validation"]["status"] = "INVALID";

        $context["validation"]["errors"][] =
            "Table '{$table}' does not exist in the imported schema.";

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Auto Correction Notice
    |--------------------------------------------------------------------------
    */

    if (

        empty($context["parsed"]["table"]) &&

        !empty($context["semantic"]["tables"]["resolved"])

    ) {

        $context["validation"]["autocorrections"][] =
            "Table automatically inferred as '{$table}'.";
    }

}