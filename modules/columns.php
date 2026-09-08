<?php

function detectColumnGroups(array $tokens, array $columnGroups): array
{
    $columns = [];

    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {

        for ($length = 3; $length >= 1; $length--) {

            if ($i + $length > $count) {
                continue;
            }

            $phrase = strtolower(

                implode(
                    "_",
                    array_slice($tokens, $i, $length)
                )

            );

            if (isset($columnGroups[$phrase])) {

                $columns = array_merge(

                    $columns,

                    $columnGroups[$phrase]

                );

                $i += ($length - 1);

                break;
            }
        }
    }

    return array_values(
        array_unique($columns)
    );
}

function detectColumns(
    $tokens,
    $command,
    $table,
    $where,
    $aggregate,
    $orderby,
    $groupby,
    $join,
    $normalizedTokens = null
) {

    require_once __DIR__ . "/column_helper.php";
    require_once __DIR__ . "/column_mapper.php";

    global $operators;

    $columnGroups = require __DIR__ . "/../rules/column_groups.php";

    if ($command != "SELECT") {
        return [];
    }

    if (empty($table)) {
        return [];
    }

    $columns = [];

    /*
|--------------------------------------------------------------------------
| Use normalized tokens when available
|--------------------------------------------------------------------------
*/

    if (
        is_array($normalizedTokens) &&
        !empty($normalizedTokens)
    ) {
        $tokens = $normalizedTokens;
    }

    /*
|--------------------------------------------------------------------------
| Detect Multi-word Column Phrases
|--------------------------------------------------------------------------
*/

    $processedTokens = [];

    for ($i = 0; $i < count($tokens); $i++) {

        $processedTokens[] = detectColumnPhrase(
            $tokens,
            $i
        );
    }

    $tokens = $processedTokens;

    $count = count($tokens);

    /*
    ----------------------------------------
    FIND FROM
    ----------------------------------------
    */

    $fromIndex = -1;

    for ($i = 0; $i < $count; $i++) {

        if (strtolower($tokens[$i]) == "from") {

            $fromIndex = $i;

            break;
        }
    }

    $columns = detectColumnGroups(
        $tokens,
        $columnGroups
    );

    $reservedWords = [

        "show",
        "display",
        "as",
        "list",
        "fetch",
        "find",
        "search",
        "get",

        "from",
        "where",
        "group",
        "order",
        "having",
        "limit",
        "join",

        "by",

        "distinct",

        "all",

        "and",
        "or"

    ];

    /*
    ----------------------------------------
    SHOW ALL students
    ----------------------------------------
    */

    if (
        isset($tokens[1]) &&
        strtolower($tokens[1]) == "all"
    ) {
        return ["*"];
    }

    /*
    ----------------------------------------
    SHOW students ...
    ----------------------------------------
    */

    if ($fromIndex == -1) {

        if ($count == 2) {
            return ["*"];
        }

        if (
            isset($tokens[2]) &&
            strtolower($tokens[2]) == "where"
        ) {
            return ["*"];
        }

        for ($i = 1; $i < $count; $i++) {

            $word = strtolower($tokens[$i]);

            // Skip aggregate words
            if (
                in_array(
                    $word,
                    [
                        "count",
                        "sum",
                        "avg",
                        "average",
                        "min",
                        "minimum",
                        "max",
                        "maximum"
                    ]
                )
            ) {
                continue;
            }

            // Skip alias keyword
            if ($word == "as") {
                continue;
            }

            // Skip alias value
            if (
                $i > 0 &&
                strtolower($tokens[$i - 1]) == "as"
            ) {
                continue;
            }

            /*
|--------------------------------------------------------------------------
| Skip connector words
|--------------------------------------------------------------------------
*/

            if (
                in_array(
                    $word,
                    [
                        "and",
                        "or"
                    ]
                )
            ) {
                continue;
            }

            /*
            ----------------------------------------
            RESERVED SQL WORDS
            ----------------------------------------
            */

            if ($word == "distinct") {
                continue;
            }

            if (
                in_array($word, [

                    "where",

                    "order",
                    "sort",
                    "by",

                    "group",
                    "having",

                    "join",

                    "limit",

                    "ascending",
                    "descending",
                    "asc",
                    "desc"

                ])
            ) {
                break;
            }

            /*
            ----------------------------------------
            SQL OPERATORS
            ----------------------------------------
            */

            if (isset($operators[$word])) {
                break;
            }

            /*
            ----------------------------------------
            SYMBOL OPERATORS
            ----------------------------------------
            */

            if (
                in_array($word, [
                    "=",
                    ">",
                    "<",
                    ">=",
                    "<=",
                    "<>",
                    "!="
                ])
            ) {
                break;
            }

            /*
            ----------------------------------------
            NUMERIC VALUE
            ----------------------------------------
            */

            if (is_numeric($word)) {
                break;
            }

            /*
            ----------------------------------------
            WHERE COLUMN
            ----------------------------------------
            */

            if (
                !empty($where["conditions"])
            ) {

                foreach ($where["conditions"] as $condition) {

                    if (
                        strtolower($condition["column"]) == $word
                    ) {
                        break 2;
                    }
                }
            }

            // Skip detected table name when no FROM clause exists
            if ($table && strtolower($word) === strtolower($table)) {
                continue;
            }


            $mapped = mapColumnWord($word);


            if ($mapped !== null) {

                $columns[] = $mapped["column"];
            } else {

                $columns[] = $word;
            }
        }

        if (empty($columns)) {

            $columns[] = "*";
        }
    }

    /*
----------------------------------------
COLUMNS BEFORE FROM
----------------------------------------
*/

    if ($fromIndex != -1) {

        for ($i = 1; $i < $fromIndex; $i++) {

            $word = strtolower($tokens[$i]);

            // Skip aggregate keyword
            // Skip aggregate keyword
            if (
                $aggregate != null &&
                $i == $aggregate["index"]
            ) {
                continue;
            }

            /*
|--------------------------------------------------------------------------
| Skip aggregate words
|--------------------------------------------------------------------------
*/

            if (
                in_array(
                    $word,
                    [
                        "count",
                        "sum",
                        "avg",
                        "average",
                        "min",
                        "minimum",
                        "max",
                        "maximum"
                    ]
                )
            ) {
                continue;
            }

            /*
|--------------------------------------------------------------------------
| Skip alias name
|--------------------------------------------------------------------------
*/

            if (
                $i > 0 &&
                strtolower($tokens[$i - 1]) == "as"
            ) {
                continue;
            }

            if (in_array($word, $reservedWords)) {
                continue;
            }

            if (isset($operators[$word])) {
                continue;
            }

            if (is_numeric($word)) {
                continue;
            }

            if ($word == ",") {
                continue;
            }

            if ($word == "*") {
                $columns[] = "*";
                continue;
            }

            $mapped = mapColumnWord($word);

            if ($mapped !== null) {

                $columns[] = $mapped["column"];
            } else {

                $columns[] = $word;
            }
        }
    }

    /*
----------------------------------------
FINAL RETURN
----------------------------------------
*/

    $columns = array_values(
        array_unique($columns)
    );

    if (empty($columns)) {
        return ["*"];
    }

    return $columns;
}
