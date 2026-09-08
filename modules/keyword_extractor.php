<?php

/*
|--------------------------------------------------------------------------
| SQL Commands
|--------------------------------------------------------------------------
*/

$commands = require __DIR__ . '/../rules/commands.php';

/*
|--------------------------------------------------------------------------
| Extract SQL Command
|--------------------------------------------------------------------------
*/

function extractKeyword(array $tokens): string
{
    global $commands;

    $count = count($tokens);

    /*
|--------------------------------------------------------------------------
| Rule-Based Pattern Recognition
|--------------------------------------------------------------------------
*/

    $words = array_map('strtolower', $tokens);

    /*
|----------------------------------------------------------
| EMPTY <table> TABLE
| Example:
| empty student table
| empty employee table
-----------------------------------------------------------
*/

    if (
        count($words) == 3 &&
        $words[0] == "empty" &&
        $words[2] == "table"
    ) {
        return "TRUNCATE TABLE";
    }

    /*
|----------------------------------------------------------
| CLEAR <table> TABLE
-----------------------------------------------------------
*/

    if (
        count($words) == 3 &&
        $words[0] == "clear" &&
        $words[2] == "table"
    ) {
        return "TRUNCATE TABLE";
    }

    /*
|----------------------------------------------------------
| CLEAR ALL RECORDS FROM <table>
-----------------------------------------------------------
*/

    if (
        count($words) >= 5 &&
        $words[0] == "clear" &&
        $words[1] == "all" &&
        $words[2] == "records" &&
        $words[3] == "from"
    ) {
        return "TRUNCATE TABLE";
    }

    /*
|----------------------------------------------------------
| REMOVE ALL ROWS FROM <table>
-----------------------------------------------------------
*/

    if (
        count($words) >= 5 &&
        $words[0] == "remove" &&
        $words[1] == "all" &&
        $words[2] == "rows" &&
        $words[3] == "from"
    ) {
        return "TRUNCATE TABLE";
    }

    /*
|--------------------------------------------------------------------------
| Aggregate Pattern Recognition
|--------------------------------------------------------------------------
|
| show sum salary from employees
| show average age from students
| show max salary from employees
| show min age from students
|
*/

    if (
        count($words) >= 5 &&
        $words[0] == "show"
    ) {

        if (in_array($words[1], ["sum", "average", "avg", "max", "min"])) {
            return "SELECT";
        }
    }

    // 3-word
    for ($i = 0; $i <= $count - 3; $i++) {

        $phrase = implode(" ", array_slice($tokens, $i, 3));

        if (isset($commands[$phrase])) {
            return $commands[$phrase];
        }
    }

    // 2-word
    for ($i = 0; $i <= $count - 2; $i++) {

        $phrase = implode(" ", array_slice($tokens, $i, 2));

        if (isset($commands[$phrase])) {
            return $commands[$phrase];
        }
    }

    // 1-word
    foreach ($tokens as $token) {

        if (isset($commands[$token])) {
            return $commands[$token];
        }
    }

    return "UNKNOWN";
}

/*
|--------------------------------------------------------------------------
| Extract Detected Keywords
|--------------------------------------------------------------------------
|
*/

function extractDetectedKeywords(array $parsed): array
{
    $keywords = [];

    /* Command */

    if (!empty($parsed["command"])) {
        $keywords["Command"] = $parsed["command"];
    }

    /* Table */

    if (!empty($parsed["table"])) {
        $keywords["Table"] = $parsed["table"];
    }

    /* Columns */

    if (!empty($parsed["columns"])) {

        $displayColumns = [];

        foreach ($parsed["columns"] as $column) {

            if (is_array($column)) {

                if (!empty($column["alias"])) {

                    $displayColumns[] =
                        $column["name"] .
                        " AS " .
                        $column["alias"];
                } else {

                    $displayColumns[] = $column["name"];
                }
            } else {

                $displayColumns[] = $column;
            }
        }

        $keywords["Columns"] = implode(", ", $displayColumns);
    }

    /* Aggregate */

    if (!empty($parsed["aggregate"])) {

        if (!empty($parsed["aggregate"]["function"])) {
            $keywords["Aggregate"] = $parsed["aggregate"]["function"];
        }
    }

    /* WHERE */

    if (!empty($parsed["where"])) {

        if (!empty($parsed["where"]["column"])) {
            $keywords["WHERE"] = $parsed["where"]["column"];
        }
    }

    /* JOIN */

    if (!empty($parsed["join"])) {

        if (!empty($parsed["join"]["type"])) {
            $keywords["JOIN"] = $parsed["join"]["type"];
        }

        if (!empty($parsed["join"]["table"])) {
            $keywords["JOIN Table"] = $parsed["join"]["table"];
        }

        if (!empty($parsed["join"]["on"])) {
            $keywords["ON"] = $parsed["join"]["on"];
        }
    }

    /* GROUP BY */

    if (!empty($parsed["groupby"])) {

        if (!empty($parsed["groupby"]["column"])) {
            $keywords["GROUP BY"] = $parsed["groupby"]["column"];
        }
    }

    /* HAVING */

    if (!empty($parsed["having"])) {
        $keywords["HAVING"] = "Detected";
    }

    /* ORDER BY */

    if (!empty($parsed["orderby"])) {

        if (!empty($parsed["orderby"]["column"])) {
            $keywords["ORDER BY"] = $parsed["orderby"]["column"];
        }
    }

    /* LIMIT */

    if (!empty($parsed["limit"])) {

        if (!empty($parsed["limit"]["value"])) {
            $keywords["LIMIT"] = $parsed["limit"]["value"];
        }
    }

    return $keywords;
}
