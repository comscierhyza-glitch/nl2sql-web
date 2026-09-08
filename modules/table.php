<?php

function detectTable($tokens, $command)
{
    $count = count($tokens);

    $knownTables = require __DIR__ . "/../rules/tables.php";

    /*
    |--------------------------------------------------------------------------
    | CREATE / ALTER / DROP / TRUNCATE TABLE
    |--------------------------------------------------------------------------
    */

    if (
        in_array(
            $command,
            [
                "CREATE TABLE",
                "DROP TABLE",
                "ALTER TABLE",
                "TRUNCATE TABLE"
            ]
        )
    ) {

        for ($i = 0; $i < $count - 1; $i++) {

            if (strtolower($tokens[$i]) == "table") {

                return strtolower($tokens[$i + 1]);
            }
        }
    }

    /*
|--------------------------------------------------------------------------
| SELECT
|--------------------------------------------------------------------------
*/

    if ($command == "SELECT") {

        /*
    ----------------------------------------
    FROM has highest priority
    ----------------------------------------
    */

        for ($i = 0; $i < $count - 1; $i++) {

            if (strtolower($tokens[$i]) == "from") {
                return strtolower($tokens[$i + 1]);
            }
        }

        /*
    ----------------------------------------
    show all employees
    ----------------------------------------
    */

        if (
            isset($tokens[1]) &&
            strtolower($tokens[1]) == "all" &&
            isset($tokens[2])
        ) {
            return strtolower($tokens[2]);
        }

        /*
----------------------------------------
Known Tables
----------------------------------------
*/

        foreach ($tokens as $index => $token) {

            $word = strtolower($token);

            /*
    ----------------------------------------
    Skip ORDER BY keyword
    ----------------------------------------
    */

            if (
                $word == "order" &&
                isset($tokens[$index + 1]) &&
                strtolower($tokens[$index + 1]) == "by"
            ) {
                continue;
            }

            /*
    ----------------------------------------
    Skip GROUP BY keyword
    ----------------------------------------
    */

            if (
                $word == "group" &&
                isset($tokens[$index + 1]) &&
                strtolower($tokens[$index + 1]) == "by"
            ) {
                continue;
            }

            if (in_array($word, $knownTables)) {

                return rtrim($word, "s");
            }
        }

        /*
----------------------------------------
Temporary Default
----------------------------------------
*/

        return "employee";
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    if ($command == "INSERT") {

        foreach ($tokens as $token) {

            $token = strtolower($token);

            if (
                !in_array(
                    $token,
                    [
                        "add",
                        "insert",
                        "save",
                        "record"
                    ]
                )
            ) {

                return $token;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    if ($command == "UPDATE") {

        foreach ($tokens as $token) {

            $token = strtolower($token);

            if (
                !in_array(
                    $token,
                    [
                        "update",
                        "modify",
                        "edit",
                        "change"
                    ]
                )
            ) {

                return $token;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    if ($command == "DELETE") {

        foreach ($tokens as $token) {

            $token = strtolower($token);

            if (
                !in_array(
                    $token,
                    [
                        "delete",
                        "remove",
                        "erase"
                    ]
                )
            ) {

                return $token;
            }
        }
    }

    return null;
}
