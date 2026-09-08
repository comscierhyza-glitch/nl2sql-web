<?php

function generateWarnings(array &$context): void
{
    $parsed = $context["parsed"];

    /*
    |--------------------------------------------------------------------------
    | SELECT *
    |--------------------------------------------------------------------------
    */

    if (
        !empty($parsed["columns"]) &&
        (
            in_array("*", $parsed["columns"]) ||
            in_array("all", $parsed["columns"])
        )
    ) {

        $context["validation"]["warnings"][] =
            "Using SELECT * may return unnecessary columns.";
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE without WHERE
    |--------------------------------------------------------------------------
    */

    if (

        ($parsed["command"] ?? "") == "DELETE" &&

        empty($parsed["where"])

    ) {

        $context["validation"]["warnings"][] =
            "DELETE without WHERE may affect all rows.";

    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE without WHERE
    |--------------------------------------------------------------------------
    */

    if (

        ($parsed["command"] ?? "") == "UPDATE" &&

        empty($parsed["where"])

    ) {

        $context["validation"]["warnings"][] =
            "UPDATE without WHERE may affect all rows.";

    }

    /*
    |--------------------------------------------------------------------------
    | Inferred Table
    |--------------------------------------------------------------------------
    */

    if (

        empty($parsed["table"]) &&

        !empty($context["semantic"]["tables"]["resolved"])

    ) {

        $context["validation"]["warnings"][] =
            "Table was inferred automatically.";

    }

    /*
    |--------------------------------------------------------------------------
    | Inferred JOIN
    |--------------------------------------------------------------------------
    */

    if (!empty($parsed["joins"])) {

        $context["validation"]["warnings"][] =
            "JOIN clause was generated automatically.";

    }

    /*
    |--------------------------------------------------------------------------
    | Parser Mode
    |--------------------------------------------------------------------------
    */

    if (

        $context["validation"]["mode"] == "PARSER"

    ) {

        $context["validation"]["warnings"][] =
            "Schema validation skipped because no database schema is imported.";

    }

}