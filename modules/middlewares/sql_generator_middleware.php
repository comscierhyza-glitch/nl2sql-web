<?php

require_once __DIR__ . "/../sql_generator.php";

function handleSQLGenerator(array &$context): bool
{
    /*
|--------------------------------------------------------------------------
| Validation Check
|--------------------------------------------------------------------------
*/

    if (empty($context["validation"])) {
        return false;
    }

    if (($context["validation"]["status"] ?? "INVALID") === "INVALID") {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Parsed Check
    |--------------------------------------------------------------------------
    */

    if (empty($context["parsed"])) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate SQL
    |--------------------------------------------------------------------------
    */

    $context["sql"] = generateSQL(
        $context["parsed"],
        $context["semantic"] ?? []
    );

    return true;
}
